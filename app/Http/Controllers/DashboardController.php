<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Dudi;
use App\Models\Magang;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
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
                    'studentName' => $magang->siswa->nama_lengkap,
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
            'recentLogbooks' => $recentLogbooks
        ]);
    }
}