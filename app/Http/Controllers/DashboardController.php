<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Dudi;
use App\Models\Magang;
use App\Models\Logbook;
use App\Models\Guru;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user && $user->role === 'guru') {
            return $this->guruDashboard();
        }
        
        return $this->adminDashboard();
    }

    private function adminDashboard()
    {
        // Get statistics
        $totalStudents = Siswa::count();
        $dudiPartners = Dudi::count();
        $activeInterns = Magang::where('status', 'aktif')->count();
        $todayLogbooks = Logbook::whereDate('tanggal', Carbon::today())->count();

        // Get recent internships (latest 3)
        $recentInternships = Magang::with(['siswa', 'dudi'])
            ->where('status', 'aktif')
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($magang) {
                return [
                    'id' => $magang->id,
                    'studentName' => $magang->siswa->nama,
                    'companyName' => $magang->dudi->nama_perusahaan,
                    'startDate' => $magang->tanggal_mulai->format('d/m/Y'),
                    'endDate' => $magang->tanggal_selesai->format('d/m/Y'),
                    'status' => ucfirst($magang->status)
                ];
            });

        // Get active DUDI with student count (latest 3)
        $activeDudi = Dudi::withCount(['magang as student_count' => function ($query) {
                $query->where('status', 'aktif');
            }])
            ->having('student_count', '>', 0)
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($dudi) {
                return [
                    'id' => $dudi->id,
                    'name' => $dudi->nama_perusahaan,
                    'address' => $dudi->alamat,
                    'phone' => $dudi->telepon,
                    'studentCount' => $dudi->student_count
                ];
            });

        // Get recent logbooks (latest 3)
        $recentLogbooks = Logbook::with(['magang.siswa'])
            ->latest('tanggal')
            ->take(3)
            ->get()
            ->map(function ($logbook) {
                $status = match($logbook->status_verifikasi) {
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak',
                    'pending' => 'Pending',
                    default => 'Pending'
                };

                return [
                    'id' => $logbook->id,
                    'task' => $logbook->kegiatan ?? 'Tidak ada kegiatan',
                    'date' => $logbook->tanggal->format('d/m/Y'),
                    'obstacle' => $logbook->kendala ?? 'Tidak ada kendala',
                    'status' => $status
                ];
            });

        $stats = [
            'totalStudents' => $totalStudents,
            'dudiPartners' => $dudiPartners,
            'activeInterns' => $activeInterns,
            'todayLogbooks' => $todayLogbooks
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentInternships' => $recentInternships,
            'activeDudi' => $activeDudi,
            'recentLogbooks' => $recentLogbooks,
        ]);
    }

    private function guruDashboard()
    {
        $user = Auth::user();
        
        // Get the guru record for the authenticated user
         $guru = Guru::where('user_id', $user->id)->first();
        
        if (!$guru) {
            // If no guru record found, return empty dashboard
            return Inertia::render('Dashboard', [
                'stats' => [
                    'totalStudents' => 0,
                    'dudiPartners' => 0,
                    'activeInterns' => 0,
                    'todayLogbooks' => 0
                ],
                'recentInternships' => [],
                'activeDudi' => [],
                'recentLogbooks' => [],
            ]);
        }

        // Get statistics for guru - only for students under their supervision
        $supervisedStudentIds = Magang::where('guru_id', $guru->id)->pluck('siswa_id');
        
        $totalStudents = $supervisedStudentIds->count();
        $dudiPartners = Dudi::whereHas('magang', function($query) use ($guru) {
            $query->where('guru_id', $guru->id);
        })->count();
        $activeInterns = Magang::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->count();
        $todayLogbooks = Logbook::whereHas('magang', function($query) use ($guru) {
            $query->where('guru_id', $guru->id);
        })->whereDate('tanggal', Carbon::today())->count();

        // Get recent internships for guru's supervised students
        $recentInternships = Magang::with(['siswa', 'dudi'])
            ->where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->latest()
            ->take(2)
            ->get()
            ->map(function ($magang) {
                return [
                    'id' => $magang->id,
                    'studentName' => $magang->siswa->nama,
                    'companyName' => $magang->dudi->nama_perusahaan,
                    'startDate' => $magang->tanggal_mulai->format('d/m/Y'),
                    'endDate' => $magang->tanggal_selesai->format('d/m/Y'),
                    'status' => 'Aktif'
                ];
            });

        // Get active DUDI for guru's supervised students
        $activeDudi = Dudi::withCount(['magang as student_count' => function ($query) use ($guru) {
                $query->where('guru_id', $guru->id)->where('status', 'aktif');
            }])
            ->whereHas('magang', function($query) use ($guru) {
                $query->where('guru_id', $guru->id);
            })
            ->having('student_count', '>', 0)
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($dudi) {
                return [
                    'id' => $dudi->id,
                    'name' => $dudi->nama_perusahaan,
                    'address' => $dudi->alamat,
                    'phone' => $dudi->telepon,
                    'studentCount' => $dudi->student_count . ' siswa'
                ];
            });

        // Get recent logbooks for guru's supervised students
        $recentLogbooks = Logbook::with(['magang.siswa'])
            ->whereHas('magang', function($query) use ($guru) {
                $query->where('guru_id', $guru->id);
            })
            ->latest('tanggal')
            ->take(3)
            ->get()
            ->map(function ($logbook) {
                $status = match($logbook->status_verifikasi) {
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak', 
                    'pending' => 'Pending',
                    default => 'Pending'
                };

                return [
                    'id' => $logbook->id,
                    'task' => $logbook->kegiatan ?? 'Mempelajari sistem database dan melakukan backup data harian',
                    'date' => $logbook->tanggal->format('d/m/Y'),
                    'obstacle' => $logbook->kendala ?? 'Aplikasi tidak bisa diakses karena kendala',
                    'status' => $status
                ];
            });

        $stats = [
            'totalStudents' => $totalStudents,
            'dudiPartners' => $dudiPartners,
            'activeInterns' => $activeInterns,
            'todayLogbooks' => $todayLogbooks
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentInternships' => $recentInternships,
            'activeDudi' => $activeDudi,
            'recentLogbooks' => $recentLogbooks,
        ]);
    }
}