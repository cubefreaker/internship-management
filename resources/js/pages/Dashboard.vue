<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Calendar, Users, Building2, GraduationCap, BookOpen, CheckCircle, Clock, XCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);

// Get current date
const currentDate = computed(() => {
    const now = new Date();
    return now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

// Mock data for demonstration
const stats = {
    totalStudents: 150,
    dudiPartners: 45,
    activeInterns: 120,
    todayLogbooks: 85
};

const recentInternships = [
    {
        id: 1,
        studentName: 'Ahmad Rizki',
        companyName: 'PT. Teknologi Nusantara',
        startDate: '15/1/2024',
        endDate: '15/4/2024',
        status: 'Aktif'
    },
    {
        id: 2,
        studentName: 'Siti Nurhaliza',
        companyName: 'CV. Digital Kreativa',
        startDate: '20/1/2024',
        endDate: '20/4/2024',
        status: 'Aktif'
    },
    {
        id: 3,
        studentName: 'Budi Santoso',
        companyName: 'PT. Inovasi Mandiri',
        startDate: '25/1/2024',
        endDate: '25/4/2024',
        status: 'Aktif'
    }
];

const activeDudi = [
    {
        id: 1,
        name: 'PT. Teknologi Nusantara',
        address: 'Jl. HR Muhammad No. 123, Surabaya',
        phone: '031-5551234',
        studentCount: 8
    },
    {
        id: 2,
        name: 'CV. Digital Kreativa',
        address: 'Jl. Raya Darmo No. 456, Surabaya',
        phone: '031-5555678',
        studentCount: 5
    },
    {
        id: 3,
        name: 'PT. Inovasi Mandiri',
        address: 'Jl. Diponegoro No. 789, Surabaya',
        phone: '031-5559012',
        studentCount: 12
    }
];

const recentLogbooks = [
    {
        id: 1,
        task: 'Mempelajari sistem database dan melakukan backup data harian',
        date: '21/7/2024',
        obstacle: 'Tidak ada kendala berarti',
        status: 'Disetujui'
    },
    {
        id: 2,
        task: 'Membuat design mockup untuk website perusahaan',
        date: '20/7/2024',
        obstacle: 'Software design masih belum familiar',
        status: 'Pending'
    },
    {
        id: 3,
        task: 'Mengikuti training keamanan sistem informasi',
        date: '19/7/2024',
        obstacle: 'Materi cukup kompleks untuk dipahami',
        status: 'Ditolak'
    }
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'Aktif':
        case 'Disetujui':
            return 'bg-green-100 text-green-800';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'Ditolak':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">SMK Negeri 1 Surabaya</h1>
                        <p class="text-lg text-gray-600">Sistem Manajemen Magang Siswa</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <Calendar class="h-4 w-4" />
                                <span>{{ currentDate }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <Users class="h-4 w-4 text-blue-600" />
                            </div>
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ user.name }}</div>
                                <div class="text-gray-500">{{ user.role || 'Admin' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Dashboard</h2>
                    <p class="text-gray-600">Selamat datang di sistem pelaporan magang siswa SMK Negeri 1 Surabaya</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Siswa Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Siswa</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.totalStudents }}</p>
                            <p class="text-sm text-gray-500">Seluruh siswa terdaftar</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <Users class="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <!-- DUDI Partner Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">DUDI Partner</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.dudiPartners }}</p>
                            <p class="text-sm text-gray-500">Perusahaan mitra</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <Building2 class="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <!-- Siswa Magang Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Siswa Magang</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.activeInterns }}</p>
                            <p class="text-sm text-gray-500">Sedang aktif magang</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                            <GraduationCap class="h-6 w-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <!-- Logbook Hari Ini Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Logbook Hari Ini</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.todayLogbooks }}</p>
                            <p class="text-sm text-gray-500">Laporan masuk hari ini</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center">
                            <BookOpen class="h-6 w-6 text-orange-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Panels -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Magang Terbaru Panel -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-gray-600" />
                        <h3 class="text-lg font-semibold text-gray-900">Magang Terbaru</h3>
                    </div>
                    <div class="space-y-4">
                        <div v-for="internship in recentInternships" :key="internship.id" 
                             class="flex items-center justify-between rounded-lg border border-gray-100 p-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <GraduationCap class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ internship.studentName }}</p>
                                    <p class="text-sm text-gray-600">{{ internship.companyName }}</p>
                                    <p class="text-xs text-gray-500">{{ internship.startDate }} - {{ internship.endDate }}</p>
                                </div>
                            </div>
                            <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(internship.status)]">
                                {{ internship.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- DUDI Aktif Panel -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-gray-600" />
                        <h3 class="text-lg font-semibold text-gray-900">DUDI Aktif</h3>
                    </div>
                    <div class="space-y-4">
                        <div v-for="dudi in activeDudi" :key="dudi.id" 
                             class="rounded-lg border border-gray-100 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ dudi.name }}</h4>
                                    <p class="text-sm text-gray-600">{{ dudi.address }}</p>
                                    <p class="text-sm text-gray-500">{{ dudi.phone }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium">
                                    {{ dudi.studentCount }} siswa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logbook Terbaru Panel -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <BookOpen class="h-5 w-5 text-gray-600" />
                    <h3 class="text-lg font-semibold text-gray-900">Logbook Terbaru</h3>
                </div>
                <div class="space-y-4">
                    <div v-for="logbook in recentLogbooks" :key="logbook.id" 
                         class="rounded-lg border border-gray-100 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ logbook.task }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ logbook.date }}</p>
                                <p class="text-sm text-gray-500 mt-1">Kendala: {{ logbook.obstacle }}</p>
                            </div>
                            <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(logbook.status)]">
                                {{ logbook.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
