<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\Magang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;


class LogbookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Logbook::query()->with(['magang', 'magang.siswa', 'magang.dudi']);

        // Fokus versi siswa/guru: batasi scope sesuai role
        $activeMagang = null;
        $isGuru = ($user && $user->role === 'guru');
        if ($user && $user->role === 'siswa') {
            // Cari magang aktif siswa
            $activeMagang = Magang::where('siswa_id', $user->id)
                ->where('status', 'berlangsung')
                ->latest()
                ->first();

            if ($activeMagang) {
                $query->where('magang_id', $activeMagang->id);
            } else {
                // Jika tidak ada magang aktif, tampilkan kosong
                $query->whereRaw('1 = 0');
            }
        } elseif ($isGuru) {
            // Guru: hanya melihat jurnal siswa bimbingannya
            $query->whereHas('magang', function ($q) use ($user) {
                $q->where('guru_id', $user->id);
            });
        }

        // Filter status verifikasi
        if ($request->filled('status') && in_array($request->get('status'), ['pending', 'disetujui', 'ditolak'])) {
            $query->where('status_verifikasi', $request->get('status'));
        }

        // Filter bulan & tahun berdasarkan tanggal
        if ($request->filled('month')) {
            $query->whereMonth('tanggal', (int) $request->get('month'));
        }
        if ($request->filled('year')) {
            $query->whereYear('tanggal', (int) $request->get('year'));
        }
        // Filter tanggal spesifik
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->get('date'));
        }

        // Pagination
        $perPage = (int) $request->get('per_page', 10);
        $logbooks = $query->orderBy('tanggal', 'desc')->paginate($perPage);

        // Reminder: belum menulis jurnal hari ini (khusus siswa)
        $reminderMissingToday = false;
        if ($activeMagang) {
            $todayExists = Logbook::where('magang_id', $activeMagang->id)
                ->whereDate('tanggal', now()->toDateString())
                ->exists();
            $reminderMissingToday = !$todayExists;
        }

        // Statistik untuk guru (total, pending, disetujui, ditolak)
        $stats = null;
        if ($isGuru) {
            $base = Logbook::query()->whereHas('magang', function ($q) use ($user) {
                $q->where('guru_id', $user->id);
            });
        } else {
            $base = Logbook::query()->whereHas('magang', function ($q) use ($user) {
                $q->where('siswa_id', $user->id);
            });
        }

        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status_verifikasi', 'pending')->count(),
            'disetujui' => (clone $base)->where('status_verifikasi', 'disetujui')->count(),
            'ditolak' => (clone $base)->where('status_verifikasi', 'ditolak')->count(),
        ];
        
        return Inertia::render('Logbook/Index', [
            'logbooks' => $logbooks,
            'filters' => $request->only(['status', 'month', 'year', 'date', 'per_page']),
            'reminderMissingToday' => $reminderMissingToday,
            'canCreate' => (bool) $activeMagang,
            'activeMagang' => $activeMagang,
            'stats' => $stats,
            'canVerify' => $isGuru,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            return back()->with('error', 'Hanya siswa yang dapat menambah jurnal.');
        }

        $activeMagang = Magang::where('siswa_id', $user->id)->where('status', 'berlangsung')->latest()->first();
        if (!$activeMagang) {
            return back()->with('error', 'Anda belum memiliki magang aktif.');
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'kegiatan' => 'required|string',
            'kendala' => 'nullable|string',
            'file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'kegiatan.required' => 'Deskripsi kegiatan wajib diisi.',
            'file.mimes' => 'Tipe file tidak didukung. Gunakan PDF, DOC, DOCX, JPG, atau PNG.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('logbook/'.$activeMagang->id, 'public');
        }

        Logbook::create([
            'magang_id' => $activeMagang->id,
            'tanggal' => $request->tanggal,
            'kegiatan' => $request->kegiatan,
            'kendala' => $request->kendala,
            'file' => $path,
            'status_verifikasi' => 'pending',
        ]);

        return redirect()->route('logbook.index')->with('success', 'Jurnal harian berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $logbook = Logbook::with(['magang', 'magang.siswa', 'magang.dudi'])->findOrFail($id);
        return response()->json($logbook);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not used: handled by Inertia modal on the index page
        return redirect()->route('logbook.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();

        $logbook = Logbook::findOrFail($id);

        // Jika siswa: izinkan edit konten jurnal
        if ($user && $user->role === 'siswa') {
            // Pastikan milik siswa saat ini
            $magangIds = Magang::where('siswa_id', $user->id)->pluck('id')->toArray();
            if (!in_array($logbook->magang_id, $magangIds)) {
                return back()->with('error', 'Anda tidak memiliki akses ke jurnal ini.');
            }

            $validator = Validator::make($request->all(), [
                'tanggal' => 'required|date',
                'kegiatan' => 'required|string',
                'kendala' => 'nullable|string',
                'file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $data = [
                'tanggal' => $request->tanggal,
                'kegiatan' => $request->kegiatan,
                'kendala' => $request->kendala,
            ];

            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('logbook/'.$logbook->magang_id, 'public');
                $data['file'] = $path;
            }

            $logbook->update($data);

            return redirect()->route('logbook.index')->with('success', 'Jurnal harian berhasil diperbarui.');
        }

        // Jika guru: izinkan mengubah status verifikasi dan catatan guru
        if ($user && $user->role === 'guru') {
            $hasAccess = Magang::where('id', $logbook->magang_id)->where('guru_id', $user->id)->exists();
            if (!$hasAccess) {
                return back()->with('error', 'Anda tidak memiliki akses untuk memverifikasi jurnal ini.');
            }

            $validator = Validator::make($request->all(), [
                'status_verifikasi' => 'required|in:pending,disetujui,ditolak',
                'catatan_guru' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $logbook->update([
                'status_verifikasi' => $request->status_verifikasi,
                'catatan_guru' => $request->catatan_guru,
            ]);

            return redirect()->route('logbook.index')->with('success', 'Status jurnal berhasil diperbarui.');
        }

        return back()->with('error', 'Akses tidak diizinkan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            return back()->with('error', 'Hanya siswa yang dapat menghapus jurnal.');
        }

        $logbook = Logbook::findOrFail($id);

        $magangIds = Magang::where('siswa_id', $user->id)->pluck('id')->toArray();
        if (!in_array($logbook->magang_id, $magangIds)) {
            return back()->with('error', 'Anda tidak memiliki akses ke jurnal ini.');
        }

        $logbook->delete();

        return redirect()->route('logbook.index')->with('success', 'Jurnal harian berhasil dihapus.');
    }

    /**
     * Verify logbook by guru (approve/reject and add notes).
     */
    public function verify(Request $request, string $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'guru') {
            return back()->with('error', 'Hanya guru yang dapat memverifikasi jurnal.');
        }

        $logbook = Logbook::findOrFail($id);
        $hasAccess = Magang::where('id', $logbook->magang_id)->where('guru_id', $user->id)->exists();
        if (!$hasAccess) {
            return back()->with('error', 'Anda tidak memiliki akses untuk memverifikasi jurnal ini.');
        }

        $validator = Validator::make($request->all(), [
            'status_verifikasi' => 'required|in:pending,disetujui,ditolak',
            'catatan_guru' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $logbook->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan_guru' => $request->catatan_guru,
        ]);

        return redirect()->route('logbook.index')->with('success', 'Verifikasi jurnal berhasil disimpan.');
    }
}
