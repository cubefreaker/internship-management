<?php

namespace App\Http\Controllers;

use App\Models\Dudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class DudiController extends Controller
{
    public function index(Request $request)
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

    public function store(Request $request)
    {
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
            'user_id' => auth()->id(),
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
        $dudi->delete();

        return redirect()->route('dudi.index')->with('success', 'DUDI berhasil dihapus.');
    }

    public function restore($id)
    {
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
}
