<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Building2, GraduationCap, Clock, CheckCircle, AlertCircle, Filter, Search, Pencil, Trash2, Plus } from 'lucide-vue-next';

type Stats = { total: number; aktif: number; selesai: number; pending: number };
type Filters = { search?: string; status?: string; per_page?: number };
type User = { id?: number; name?: string; role?: string };

const props = defineProps<{ magang: any; stats: Stats; filters: Filters; siswaOptions?: Array<{ id: number; nama: string }>; guruOptions?: Array<{ id: number; nama: string }>; dudiOptions?: Array<{ id: number; nama: string }>; currentGuruId?: number|null }>();
const page = usePage();
const user = computed<User>(() => ((page.props as any).auth?.user) || ({} as User));

// Filters and search
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const perPage = ref(props.filters?.per_page || 10);

const handleFilter = () => {
  router.get('/magang', { search: search.value, status: statusFilter.value, per_page: perPage.value }, { preserveState: true });
};

// Add/Edit modal state
const showAddModal = ref(false);
const showEditModal = ref(false);
const editItem = ref<any | null>(null);

// Add form
const addForm = ref<{ siswa_id: string; guru_id: string; dudi_id: string; tanggal_mulai: string; tanggal_selesai: string }>({ siswa_id: '', guru_id: props.currentGuruId ? String(props.currentGuruId) : '', dudi_id: '', tanggal_mulai: '', tanggal_selesai: '' });
const addErrors = ref<Record<string, string>>({});

const openAddModal = () => {
  showAddModal.value = true;
  addErrors.value = {};
};

const submitAdd = () => {
  const payload: Record<string, any> = { ...addForm.value };
  // remove empty guru_id for guru role (already set server-side)
  if (user.value.role === 'guru') {
    delete payload.guru_id;
  }
  router.post('/magang', payload, {
    onSuccess: () => {
      showAddModal.value = false;
      addForm.value = { siswa_id: '', guru_id: props.currentGuruId ? String(props.currentGuruId) : '', dudi_id: '', tanggal_mulai: '', tanggal_selesai: '' };
      addErrors.value = {};
    },
    onError: (errors: Record<string, string>) => {
      addErrors.value = errors || {};
    }
  });
};

// Edit form
const editForm = ref<{ tanggal_mulai: string; tanggal_selesai: string; status: string; nilai_akhir: string }>({ tanggal_mulai: '', tanggal_selesai: '', status: '', nilai_akhir: '' });

// Normalize various date formats to YYYY-MM-DD for input type="date"
const formatDateInput = (val: any): string => {
  if (!val) return '';
  if (typeof val === 'string') {
    const match = val.match(/^\d{4}-\d{2}-\d{2}/);
    if (match) return match[0];
  }
  const d = new Date(val);
  if (isNaN(d.getTime())) return '';
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
};

const openEditModal = (row: any) => {
  editItem.value = row;
  editForm.value = {
    tanggal_mulai: formatDateInput(row.tanggal_mulai ?? ''),
    tanggal_selesai: formatDateInput(row.tanggal_selesai ?? ''),
    status: row.status ?? '',
    nilai_akhir: row.nilai_akhir != null ? String(row.nilai_akhir) : '',
  };
  showEditModal.value = true;
};

const submitEdit = () => {
  if (!editItem.value) return;
  router.put(`/magang/${editItem.value.id}`, editForm.value, { onSuccess: () => { showEditModal.value = false; editItem.value = null; } });
};

const destroyRow = (row: any) => {
  if (!confirm('Apakah Anda yakin ingin menghapus data magang ini? Aksi ini tidak bisa dibatalkan.')) return;
  router.delete(`/magang/${row.id}`);
};

const statusBadge = (status: string) => {
  switch (status) {
    case 'berlangsung': return 'bg-green-100 text-green-700';
    case 'selesai': return 'bg-blue-100 text-blue-700';
    case 'pending': return 'bg-yellow-100 text-yellow-700';
    case 'ditolak': return 'bg-red-100 text-red-700';
    default: return 'bg-gray-100 text-gray-700';
  }
};

const canFillGrade = computed(() => (editForm.value.status === 'selesai'));
</script>

<template>
  <Head title="Manajemen Siswa Magang" />
  <AppLayout :breadcrumbs="[{ title: 'Magang', href: '/magang' }, { title: 'Data siswa magang', href: '/magang' }]">
    <div class="px-4 py-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Siswa Magang</h1>
        <p class="text-sm text-gray-600">Kelola data siswa yang sedang, sudah, atau akan magang.</p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-4 mb-6">
        <Card>
          <CardHeader class="pb-2 flex items-center justify-between">
            <CardTitle class="text-sm text-gray-600">Total Siswa</CardTitle>
            <GraduationCap class="h-4 w-4 text-gray-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
            <p class="text-xs text-gray-500">Siswa magang terdaftar</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2 flex items-center justify-between">
            <CardTitle class="text-sm text-gray-600">Aktif</CardTitle>
            <CheckCircle class="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-gray-900">{{ stats.aktif }}</div>
            <p class="text-xs text-gray-500">Sedang magang</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2 flex items-center justify-between">
            <CardTitle class="text-sm text-gray-600">Selesai</CardTitle>
            <Clock class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-gray-900">{{ stats.selesai }}</div>
            <p class="text-xs text-gray-500">Magang selesai</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="pb-2 flex items-center justify-between">
            <CardTitle class="text-sm text-gray-600">Pending</CardTitle>
            <AlertCircle class="h-4 w-4 text-yellow-600" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-gray-900">{{ stats.pending }}</div>
            <p class="text-xs text-gray-500">Menunggu penempatan</p>
          </CardContent>
        </Card>
      </div>

      <!-- Toolbar -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2 w-full max-w-xl">
          <div class="relative w-full">
            <Input v-model="search" placeholder="Cari siswa, guru, atau DUDI..." class="pl-9" />
            <Search class="h-4 w-4 absolute left-3 top-3 text-gray-500" />
          </div>
          <select
            v-model="statusFilter"
            class="flex h-10 w-44 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="berlangsung">Aktif</option>
            <option value="selesai">Selesai</option>
            <option value="ditolak">Ditolak</option>
          </select>
          <Button variant="outline" @click="handleFilter"><Filter class="h-4 w-4 mr-2" /> Tampilkan Filter</Button>
        </div>
        <Button class="bg-blue-600 hover:bg-blue-700" @click="openAddModal"><Plus class="h-4 w-4 mr-2" /> Tambah</Button>
      </div>

      <!-- Table -->
      <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="divide-y">
          <div class="grid grid-cols-12 px-4 py-3 text-sm text-gray-600 bg-gray-50">
            <div class="col-span-3">Siswa</div>
            <div class="col-span-3">Guru Pembimbing</div>
            <div class="col-span-3">DUDI</div>
            <div class="col-span-2">Periode</div>
            <div class="col-span-1 text-right">Aksi</div>
          </div>

          <div v-for="row in magang.data" :key="row.id" class="grid grid-cols-12 px-4 py-4 text-sm">
            <div class="col-span-3">
              <div class="font-medium text-gray-900">{{ row.siswa?.name }}</div>
              <div class="text-xs text-gray-500">{{ row.siswa?.kelas }} • {{ row.siswa?.jurusan }}</div>
            </div>
            <div class="col-span-3">
              <div class="font-medium text-gray-900">{{ row.guru?.name || '-' }}</div>
            </div>
            <div class="col-span-3">
              <div class="font-medium text-gray-900">{{ row.dudi?.nama_perusahaan }}</div>
              <Badge :class="statusBadge(row.status)" class="mt-1">{{ row.status === 'berlangsung' ? 'Aktif' : row.status }}</Badge>
            </div>
            <div class="col-span-2 text-gray-700">
              <div>{{ row.tanggal_mulai }} - {{ row.tanggal_selesai }}</div>
              <div v-if="row.nilai_akhir" class="text-xs text-gray-500 mt-1">Nilai: {{ row.nilai_akhir }}</div>
            </div>
            <div class="col-span-1 flex items-center justify-end gap-2">
              <Button variant="outline" size="icon" @click="openEditModal(row)"><Pencil class="h-4 w-4" /></Button>
              <Button variant="destructive" size="icon" @click="destroyRow(row)"><Trash2 class="h-4 w-4" /></Button>
            </div>
          </div>
        </div>

        <!-- Pagination simple -->
        <div class="flex items-center justify-between px-4 py-3 border-t text-sm text-gray-600">
          <div>Menampilkan {{ magang.from }} sampai {{ magang.to }} dari {{ magang.total }} entri</div>
        </div>
      </div>

      <!-- Add Modal -->
      <Dialog v-model:open="showAddModal">
        <DialogContent class="sm:max-w-xl">
          <DialogHeader>
            <DialogTitle>Tambah Data Siswa Magang</DialogTitle>
          </DialogHeader>
          <div class="space-y-6">
            <div>
              <p class="font-semibold mb-3">Siswa & Pembimbing</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm mb-3">Siswa</p>
                    <SearchableSelect v-model="addForm.siswa_id" :options="props.siswaOptions || []" label-key="nama" value-field="id" placeholder="Cari atau pilih siswa" :class="addErrors.siswa_id ? 'border-red-500' : ''" />
                    <p v-if="addErrors.siswa_id" class="text-xs text-red-600 mt-1">{{ addErrors.siswa_id }}</p>
                </div>
                <div>
                    <p class="text-sm mb-3">Guru Pembimbing</p>
                    <template v-if="user.role === 'guru'">
                      <SearchableSelect v-model="addForm.guru_id" :disabled="true" :options="[{ id: props.currentGuruId, nama: user.name ?? '' }]" label-key="nama" value-field="id" placeholder="Guru Pembimbing" :class="addErrors.guru_id ? 'border-red-500' : ''" />
                      <p v-if="addErrors.guru_id" class="text-xs text-red-600 mt-1">{{ addErrors.guru_id }}</p>
                    </template>
                    <template v-else>
                      <SearchableSelect v-model="addForm.guru_id" :options="props.guruOptions || []" label-key="nama" value-field="id" placeholder="Cari atau pilih guru" :class="addErrors.guru_id ? 'border-red-500' : ''" />
                      <p v-if="addErrors.guru_id" class="text-xs text-red-600 mt-1">{{ addErrors.guru_id }}</p>
                    </template>
                </div>
              </div>
            </div>
            <div>
              <p class="font-semibold mb-3">Tempat Magang</p>
              <SearchableSelect v-model="addForm.dudi_id" :options="props.dudiOptions || []" label-key="nama" value-field="id" placeholder="Cari atau pilih DUDI" :class="addErrors.dudi_id ? 'border-red-500' : ''" />
              <p v-if="addErrors.dudi_id" class="text-xs text-red-600 mt-1">{{ addErrors.dudi_id }}</p>
            </div>
            <div>
              <p class="font-semibold mb-3">Periode & Status</p>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm mb-3">Tanggal Mulai</p>
                    <Input v-model="addForm.tanggal_mulai" type="date" :aria-invalid="!!addErrors.tanggal_mulai" />
                    <p v-if="addErrors.tanggal_mulai" class="text-xs text-red-600 mt-1">{{ addErrors.tanggal_mulai }}</p>
                </div>
                <div>
                    <p class="text-sm mb-3">Tanggal Selesai</p>
                    <Input v-model="addForm.tanggal_selesai" type="date" :aria-invalid="!!addErrors.tanggal_selesai" />
                    <p v-if="addErrors.tanggal_selesai" class="text-xs text-red-600 mt-1">{{ addErrors.tanggal_selesai }}</p>
                </div>
                <div>
                    <p class="text-sm mb-3">Status</p>
                    <Input :value="'Aktif'" disabled />
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">Saat simpan, status otomatis Aktif (berlangsung).</p>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" @click="showAddModal = false">Batal</Button>
            <Button class="bg-blue-600" @click="submitAdd">Simpan</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Edit Modal -->
      <Dialog v-model:open="showEditModal">
        <DialogContent class="sm:max-w-xl">
          <DialogHeader>
            <DialogTitle>Edit Data Siswa Magang</DialogTitle>
          </DialogHeader>
          <div class="space-y-6">
            <div>
              <p class="font-semibold mb-3">Periode & Status</p>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm mb-3">Tanggal Mulai</p>
                    <Input v-model="editForm.tanggal_mulai" type="date" />
                </div>
                <div>
                    <p class="text-sm mb-3">Tanggal Selesai</p>
                    <Input v-model="editForm.tanggal_selesai" type="date" />
                </div>
                <div>
                    <p class="text-sm mb-3">Status</p>
                    <select
                  v-model="editForm.status"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="pending">Pending</option>
                        <option value="berlangsung">Aktif</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
              </div>
            </div>
            <div>
              <p class="font-semibold mb-3">Penilaian</p>
              <Input :disabled="!canFillGrade" v-model="editForm.nilai_akhir" placeholder="Nilai Akhir" />
              <p class="text-xs text-gray-500 mt-2">Nilai hanya dapat diisi setelah status magang selesai</p>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" @click="showEditModal = false">Batal</Button>
            <Button class="bg-blue-600" @click="submitEdit">Update</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>