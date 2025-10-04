<?php

namespace App\Http\Controllers;

use App\Models\Magang;
use App\Models\Siswa;
use App\Models\Dudi;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class MagangController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Magang::with(['siswa', 'guru', 'dudi']);

        // Guru hanya melihat siswa bimbingannya
        if ($user && $user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if ($guru) {
                $query->where('guru_id', $guru->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Pencarian berdasarkan nama siswa, guru, dan DUDI
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($qs) use ($search) {
                    $qs->where('nama', 'like', "%{$search}%");
                })
                ->orWhereHas('guru', function ($qg) use ($search) {
                    $qg->where('nama', 'like', "%{$search}%");
                })
                ->orWhereHas('dudi', function ($qd) use ($search) {
                    $qd->where('nama_perusahaan', 'like', "%{$search}%");
                });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $perPage = $request->get('per_page', 10);
        $magang = $query->latest()->paginate($perPage);

        // Statistik ringkas seperti pada mockup
        $stats = [
            'total' => $user->role === 'guru' ? Magang::where('guru_id', $guru->id)->count() : Magang::count(),
            'aktif' => $user->role === 'guru' ? Magang::where('guru_id', $guru->id)->where('status', 'berlangsung')->count() : Magang::where('status', 'berlangsung')->count(),
            'selesai' => $user->role === 'guru' ? Magang::where('guru_id', $guru->id)->where('status', 'selesai')->count() : Magang::where('status', 'selesai')->count(),
            'pending' => $user->role === 'guru' ? Magang::where('guru_id', $guru->id)->where('status', 'pending')->count() : Magang::where('status', 'pending')->count(),
        ];

        // Options for selects in Add modal
        $siswaOptions = Siswa::select('id', 'nama')->orderBy('nama')->get();
        $dudiOptions = Dudi::select('id', 'nama_perusahaan')->orderBy('nama_perusahaan')->get();
        $guruOptionsQuery = Guru::select('id', 'nama', 'user_id')->orderBy('nama');
        // If guru logged in, limit options to themselves
        if ($user && $user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if ($guru) {
                $guruOptionsQuery->where('id', $guru->id);
            } else {
                $guruOptionsQuery->whereRaw('1 = 0');
            }
        }
        $guruOptions = $guruOptionsQuery->get();

        return Inertia::render('Magang/Index', [
            'magang' => $magang,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'per_page']),
            'siswaOptions' => $siswaOptions,
            'guruOptions' => $guruOptions->map(fn($g) => ['id' => $g->id, 'nama' => $g->nama]),
            'dudiOptions' => $dudiOptions->map(fn($d) => ['id' => $d->id, 'nama' => $d->nama_perusahaan]),
            'currentGuruId' => ($user && $user->role === 'guru') ? optional(Guru::where('user_id', $user->id)->first())->id : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'siswa_id' => 'required|exists:siswa,id',
            'dudi_id' => 'required|exists:dudi,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ];

        // Admin must select guru explicitly
        if ($user && $user->role === 'admin') {
            $rules['guru_id'] = 'required|exists:guru,id';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Guru pembimbing: jika admin, gunakan pilihan; jika guru, otomatis dari akun login
        $guruId = null;
        if ($user && $user->role === 'guru') {
            $guruModel = Guru::where('user_id', $user->id)->first();
            if (!$guruModel) {
                return back()->withErrors(['guru_id' => 'Akun guru belum terhubung dengan data guru. Hubungi admin.'])->withInput();
            }
            $guruId = $guruModel->id;
        } else {
            $guruId = $request->get('guru_id');
        }

        $data = [
            'siswa_id' => $request->siswa_id,
            'dudi_id' => $request->dudi_id,
            'guru_id' => $guruId,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            // Saat simpan dari guru, status menjadi aktif (berlangsung)
            'status' => 'berlangsung',
        ];

        Magang::create($data);

        return redirect()->route('magang.index')->with('success', 'Data magang berhasil ditambahkan');
    }

    public function update(Request $request, Magang $magang)
    {
        $user = Auth::user();

        $rules = [
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'nullable|in:pending,diterima,ditolak,berlangsung,selesai,dibatalkan',
            'nilai_akhir' => 'nullable|numeric|min:0|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Guru hanya bisa mengelola siswanya
        if ($user && $user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $magang->guru_id !== $guru->id) {
                abort(403);
            }
        }

        $update = $request->only(['tanggal_mulai', 'tanggal_selesai', 'status', 'nilai_akhir']);

        // Aturan: nilai akhir hanya bisa diisi jika status selesai
        if (isset($update['nilai_akhir']) && ($update['status'] ?? $magang->status) !== 'selesai') {
            // hapus nilai jika belum selesai
            unset($update['nilai_akhir']);
        }

        // Jika siswa mendaftar mandiri (pending), guru bisa mengubah ke berlangsung
        if (($magang->status === 'pending') && ($update['status'] ?? null) === 'berlangsung') {
            $update['status'] = 'berlangsung';
        }

        $magang->update($update);

        return redirect()->route('magang.index')->with('success', 'Data magang berhasil diperbarui');
    }

    public function destroy(Magang $magang)
    {
        $user = Auth::user();
        if ($user && $user->role === 'guru') {
            $guru = Guru::where('user_id', $user->id)->first();
            if (!$guru || $magang->guru_id !== $guru->id) {
                abort(403);
            }
        }
        $magang->delete();
        return redirect()->route('magang.index')->with('success', 'Data magang berhasil dihapus');
    }
}