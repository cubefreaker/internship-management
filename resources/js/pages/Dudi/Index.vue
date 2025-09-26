<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Building2, Plus, Search, Edit, Trash2, RotateCcw, Eye, Users, CheckCircle, XCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import DudiForm from '@/components/DudiForm.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'DUDI',
        href: '/dudi',
    },
];

const page = usePage();
const props = defineProps<{
    dudi: any;
    stats: {
        total_dudi: number;
        dudi_aktif: number;
        dudi_tidak_aktif: number;
        total_siswa_magang: number;
    };
    filters: {
        search?: string;
        status?: string;
        per_page?: number;
    };
}>();

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 5);
const showAddModal = ref(false);
const showDeleteModal = ref(false);
const showRestoreModal = ref(false);
const selectedDudi = ref<any>(null);
const showSiswaModal = ref(false);
const siswaMagang = ref<any[]>([]);

// Watch for search changes and debounce
let searchTimeout: NodeJS.Timeout;
watch(search, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/dudi', { search: newValue, per_page: perPage.value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

// Watch for per page changes
watch(perPage, (newValue) => {
    router.get('/dudi', { search: search.value, per_page: newValue }, {
        preserveState: true,
        replace: true,
    });
});

const handleAddDudi = () => {
    showAddModal.value = true;
};

const handleEditDudi = (dudi: any) => {
    selectedDudi.value = dudi;
    showAddModal.value = true;
};

const handleDeleteDudi = (dudi: any) => {
    selectedDudi.value = dudi;
    showDeleteModal.value = true;
};

const handleRestoreDudi = (dudi: any) => {
    selectedDudi.value = dudi;
    showRestoreModal.value = true;
};

const confirmDelete = () => {
    if (selectedDudi.value) {
        router.delete(`/dudi/${selectedDudi.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedDudi.value = null;
            },
        });
    }
};

const confirmRestore = () => {
    if (selectedDudi.value) {
        router.post(`/dudi/${selectedDudi.value.id}/restore`, {}, {
            onSuccess: () => {
                showRestoreModal.value = false;
                selectedDudi.value = null;
            },
        });
    }
};

const handleViewSiswaMagang = async (dudi: any) => {
    try {
        const response = await fetch(`/dudi/${dudi.id}/siswa-magang`);
        const data = await response.json();
        siswaMagang.value = data.siswa_magang;
        selectedDudi.value = dudi;
        showSiswaModal.value = true;
    } catch (error) {
        console.error('Error fetching siswa magang:', error);
    }
};

const getStatusBadge = (status: string) => {
    return status === 'aktif' 
        ? 'bg-green-100 text-green-800 border-green-200' 
        : 'bg-red-100 text-red-800 border-red-200';
};

const getStatusIcon = (status: string) => {
    return status === 'aktif' ? CheckCircle : XCircle;
};
</script>

<template>
    <Head title="Manajemen DUDI" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Manajemen DUDI</h1>
                        <p class="text-lg text-gray-600">Kelola data perusahaan mitra magang</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total DUDI Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Total DUDI</CardTitle>
                        <Building2 class="h-4 w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.total_dudi }}</div>
                        <p class="text-xs text-gray-500">Perusahaan mitra</p>
                    </CardContent>
                </Card>

                <!-- DUDI Aktif Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">DUDI Aktif</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.dudi_aktif }}</div>
                        <p class="text-xs text-gray-500">Perusahaan aktif</p>
                    </CardContent>
                </Card>

                <!-- DUDI Tidak Aktif Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">DUDI Tidak Aktif</CardTitle>
                        <XCircle class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.dudi_tidak_aktif }}</div>
                        <p class="text-xs text-gray-500">Perusahaan tidak aktif</p>
                    </CardContent>
                </Card>

                <!-- Total Siswa Magang Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Total Siswa Magang</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-gray-900">{{ stats.total_siswa_magang }}</div>
                        <p class="text-xs text-gray-500">Siswa magang aktif</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Daftar DUDI Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Daftar DUDI</CardTitle>
                        <Button @click="handleAddDudi" class="bg-blue-600 hover:bg-blue-700">
                            <Plus class="h-4 w-4 mr-2" />
                            Tambah DUDI
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Search and Filter Controls -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                v-model="search"
                                placeholder="Cari perusahaan, alamat, penanggung jawab..."
                                class="pl-10"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Tampilkan:</span>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-20">
                                        {{ perPage }} entri
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem @click="perPage = 5">5 entri</DropdownMenuItem>
                                    <DropdownMenuItem @click="perPage = 10">10 entri</DropdownMenuItem>
                                    <DropdownMenuItem @click="perPage = 25">25 entri</DropdownMenuItem>
                                    <DropdownMenuItem @click="perPage = 50">50 entri</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- DUDI Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Perusahaan</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Kontak</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Penanggung Jawab</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Siswa Magang</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="dudi in dudi.data" :key="dudi.id" class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ dudi.nama_perusahaan }}</div>
                                            <div class="text-sm text-gray-500">{{ dudi.alamat }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div>
                                            <div class="text-sm text-gray-900">{{ dudi.email }}</div>
                                            <div class="text-sm text-gray-500">{{ dudi.telepon }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm text-gray-900">{{ dudi.penanggung_jawab }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Badge :class="getStatusBadge(dudi.status)">
                                            {{ dudi.status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="handleViewSiswaMagang(dudi)"
                                            class="h-8 w-8 rounded-full bg-yellow-100 text-yellow-800 hover:bg-yellow-200 border-yellow-200"
                                        >
                                            {{ dudi.total_siswa_magang || 0 }}
                                        </Button>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="handleEditDudi(dudi)"
                                                class="h-8 w-8 p-0"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                v-if="!dudi.deleted_at"
                                                variant="ghost"
                                                size="sm"
                                                @click="handleDeleteDudi(dudi)"
                                                class="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                v-else
                                                variant="ghost"
                                                size="sm"
                                                @click="handleRestoreDudi(dudi)"
                                                class="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                            >
                                                <RotateCcw class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between mt-6">
                        <div class="text-sm text-gray-500">
                            Menampilkan {{ dudi.from || 0 }} sampai {{ dudi.to || 0 }} dari {{ dudi.total }} entri
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!dudi.prev_page_url"
                                @click="router.get(dudi.prev_page_url)"
                            >
                                «
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!dudi.prev_page_url"
                                @click="router.get(dudi.prev_page_url)"
                            >
                                <
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!dudi.next_page_url"
                                @click="router.get(dudi.next_page_url)"
                            >
                                >
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!dudi.next_page_url"
                                @click="router.get(dudi.next_page_url)"
                            >
                                »
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Add/Edit DUDI Modal -->
        <DudiForm
            v-model:open="showAddModal"
            :dudi="selectedDudi"
            @success="() => { showAddModal = false; selectedDudi = null; }"
        />

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="showDeleteModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus DUDI "{{ selectedDudi?.nama_perusahaan }}"? 
                        Data akan dihapus secara permanen.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false">Batal</Button>
                    <Button variant="destructive" @click="confirmDelete">Hapus</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Restore Confirmation Modal -->
        <Dialog v-model:open="showRestoreModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Konfirmasi Pulihkan</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin memulihkan DUDI "{{ selectedDudi?.nama_perusahaan }}"? 
                        Data akan dikembalikan ke daftar aktif.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showRestoreModal = false">Batal</Button>
                    <Button @click="confirmRestore" class="bg-green-600 hover:bg-green-700">Pulihkan</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Siswa Magang Modal -->
        <Dialog v-model:open="showSiswaModal">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Siswa Magang - {{ selectedDudi?.nama_perusahaan }}</DialogTitle>
                    <DialogDescription>
                        Daftar siswa yang sedang magang di perusahaan ini.
                    </DialogDescription>
                </DialogHeader>
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="siswaMagang.length === 0" class="text-center py-8 text-gray-500">
                        Belum ada siswa yang magang di perusahaan ini.
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="magang in siswaMagang"
                            :key="magang.id"
                            class="flex items-center justify-between p-4 border rounded-lg"
                        >
                            <div>
                                <div class="font-medium">{{ magang.siswa?.name || 'Nama tidak tersedia' }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ magang.tanggal_mulai }} - {{ magang.tanggal_selesai }}
                                </div>
                            </div>
                            <Badge :class="getStatusBadge(magang.status)">
                                {{ magang.status }}
                            </Badge>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showSiswaModal = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
