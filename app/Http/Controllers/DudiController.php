<?php

namespace App\Http\Controllers;

use App\Models\Dudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DudiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user && $user->role === 'guru') {
            return $this->guruIndex($request);
        }
        
        // Jika role adalah siswa, arahkan ke halaman khusus siswa
        if ($user && $user->role === 'siswa') {
            return $this->siswaIndex($request);
        }
        
        return $this->adminIndex($request);
    }

    private function adminIndex(Request $request)
    {
        $query = Dudi::with(['user', 'magang']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Pagination
        $perPage = $request->get('per_page', 5);
        $dudi = $query->paginate($perPage);

        // Get statistics
        $stats = [
            'total_dudi' => Dudi::count(),
            'dudi_aktif' => Dudi::where('status', 'aktif')->count(),
            'dudi_tidak_aktif' => Dudi::where('status', 'tidak_aktif')->count(),
            'total_siswa_magang' => Dudi::with('magang')->get()->sum('total_siswa_magang'),
        ];

        return Inertia::render('Dudi/Index', [
            'dudi' => $dudi,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'per_page']),
        ]);
    }

    private function siswaIndex(Request $request)
    {
        $siswa = Auth::user();
        
        // Query DUDI aktif yang tersedia untuk siswa
        $query = Dudi::with(['user'])
            ->where('status', 'aktif');

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 6);
        $dudi = $query->paginate($perPage);

        // Statistik sederhana untuk siswa
        $stats = [
            'total_dudi' => Dudi::where('status', 'aktif')->count(),
            'dudi_aktif' => Dudi::where('status', 'aktif')->count(),
            'dudi_tidak_aktif' => Dudi::where('status', 'tidak_aktif')->count(),
        ];

        // Ambil daftar pendaftaran magang siswa (maks 3) untuk kontrol UI
        $pendaftaran = [];
        if ($siswa) {
            $pendaftaran = \App\Models\Magang::where('siswa_id', $siswa->id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get(['id', 'dudi_id', 'status']);
        }

        return Inertia::render('Dudi/index_siswa', [
            'dudi' => $dudi,
            'stats' => $stats,
            'filters' => $request->only(['search', 'per_page']),
            'pendaftaran' => $pendaftaran,
        ]);
    }

    private function guruIndex(Request $request)
    {
        $user = Auth::user();
        $guru = $user->role === 'guru' ? $user : null;
        
        if (!$guru) {
            return Inertia::render('Dudi/Index', [
                'dudi' => collect(),
                'stats' => [
                    'total_dudi' => 0,
                    'dudi_aktif' => 0,
                    'dudi_tidak_aktif' => 0,
                    'total_siswa_magang' => 0,
                    'rata_rata_siswa' => 0,
                ],
                'filters' => $request->only(['search', 'status', 'per_page']),
            ]);
        }

        // Query DUDI yang terhubung dengan siswa bimbingan guru
        $query = Dudi::with(['user', 'magang' => function($q) use ($guru) {
            $q->where('guru_id', $guru->id);
        }])
        ->whereHas('magang', function($q) use ($guru) {
            $q->where('guru_id', $guru->id);
        });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Pagination
        $perPage = $request->get('per_page', 5);
        $dudi = $query->paginate($perPage);

        // Transform data untuk menambahkan jumlah siswa bimbingan per DUDI
        $dudi->getCollection()->transform(function ($item) use ($guru) {
            $item->siswa_bimbingan_count = $item->magang()
                ->where('guru_id', $guru->id)
                ->where('status', 'aktif')
                ->count();
            return $item;
        });

        // Get statistics untuk guru
        $totalDudi = Dudi::whereHas('magang', function($q) use ($guru) {
            $q->where('guru_id', $guru->id);
        })->count();

        $dudiAktif = Dudi::where('status', 'aktif')
            ->whereHas('magang', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->count();

        $totalSiswaMagang = \App\Models\Magang::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->count();

        $rataRataSiswa = $totalDudi > 0 ? round($totalSiswaMagang / $totalDudi, 1) : 0;

        $stats = [
            'total_dudi' => $totalDudi,
            'dudi_aktif' => $dudiAktif,
            'dudi_tidak_aktif' => $totalDudi - $dudiAktif,
            'total_siswa_magang' => $totalSiswaMagang,
            'rata_rata_siswa' => $rataRataSiswa,
        ];

        return Inertia::render('Dudi/Index', [
            'dudi' => $dudi,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        // Restrict access for guru role
        $user = Auth::user();
        if ($user && $user->role === 'guru') {
            return redirect()->route('dudi.index')->with('error', 'Anda tidak memiliki akses untuk menambah data DUDI.');
        }

        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Dudi::where('nama_perusahaan', $value)
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    if ($exists) {
                        $fail('Nama perusahaan sudah terdaftar.');
                    }
                },
            ],
            'alamat' => 'required|string',
            'telepon' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'penanggung_jawab' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'telepon.required' => 'Telepon wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'penanggung_jawab.required' => 'Penanggung jawab wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Dudi::create([
            'user_id' => Auth::id(),
            'nama_perusahaan' => $request->nama_perusahaan,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'penanggung_jawab' => $request->penanggung_jawab,
            'status' => $request->status,
        ]);

        return redirect()->route('dudi.index')->with('success', 'DUDI berhasil ditambahkan.');
    }

    public function show(Dudi $dudi)
    {
        $dudi->load(['user', 'magang.siswa']);
        
        return Inertia::render('Dudi/Show', [
            'dudi' => $dudi,
        ]);
    }

    public function update(Request $request, Dudi $dudi)
    {
        // Restrict access for guru role
        $user = Auth::user();
        if ($user && $user->role === 'guru') {
            return redirect()->route('dudi.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data DUDI.');
        }

        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($dudi) {
                    $exists = Dudi::where('nama_perusahaan', $value)
                        ->where('id', '!=', $dudi->id)
                        ->whereNull('deleted_at')
                        ->exists();
                    
                    if ($exists) {
                        $fail('Nama perusahaan sudah terdaftar.');
                    }
                },
            ],
            'alamat' => 'required|string',
            'telepon' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'penanggung_jawab' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif',
        ], [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'telepon.required' => 'Telepon wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'penanggung_jawab.required' => 'Penanggung jawab wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $dudi->update($request->all());

        return redirect()->route('dudi.index')->with('success', 'DUDI berhasil diperbarui.');
    }

    public function destroy(Dudi $dudi)
    {
        // Restrict access for guru role
        $user = Auth::user();
        if ($user && $user->role === 'guru') {
            return redirect()->route('dudi.index')->with('error', 'Anda tidak memiliki akses untuk menghapus data DUDI.');
        }

        $dudi->delete();

        return redirect()->route('dudi.index')->with('success', 'DUDI berhasil dihapus.');
    }

    public function restore($id)
    {
        // Restrict access for guru role
        $user = Auth::user();
        if ($user && $user->role === 'guru') {
            return redirect()->route('dudi.index')->with('error', 'Anda tidak memiliki akses untuk memulihkan data DUDI.');
        }

        $dudi = Dudi::withTrashed()->findOrFail($id);
        $dudi->restore();

        return redirect()->route('dudi.index')->with('success', 'DUDI berhasil dipulihkan.');
    }

    public function getSiswaMagang(Dudi $dudi)
    {
        $siswaMagang = $dudi->magang()->with('siswa')->get();
        
        return response()->json([
            'dudi' => $dudi,
            'siswa_magang' => $siswaMagang,
        ]);
    }

    public function apply(Request $request, Dudi $dudi)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'siswa') {
            return redirect()->route('dudi.index')->with('error', 'Hanya siswa yang dapat mendaftar magang.');
        }

        // Batasi maksimal 3 pendaftaran
        $count = \App\Models\Magang::where('siswa_id', $user->id)->count();
        if ($count >= 3) {
            return redirect()->route('dudi.index')->with('error', 'Maksimal pendaftaran magang adalah 3 DUDI.');
        }

        // Cegah duplikasi pendaftaran ke DUDI yang sama saat masih pending/berlangsung
        $existing = \App\Models\Magang::where('siswa_id', $user->id)
            ->where('dudi_id', $dudi->id)
            ->whereIn('status', ['pending', 'berlangsung'])
            ->first();
        if ($existing) {
            return redirect()->route('dudi.index')->with('error', 'Anda sudah mendaftar atau sedang magang di DUDI ini.');
        }

        \App\Models\Magang::create([
            'siswa_id' => $user->id,
            'dudi_id' => $dudi->id,
            'status' => 'pending',
        ]);

        return redirect()->route('dudi.index')->with('success', 'Pendaftaran magang berhasil diajukan, menunggu verivikasi dari pihak guru.');
    }
}
