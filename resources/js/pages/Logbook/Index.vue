<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { AlertCircle, BookOpen, CalendarDays, CheckCircle, Image as ImageIcon, Info, Pencil, Plus, Search, Trash2, Upload, XCircle } from 'lucide-vue-next';

type LogbookItem = {
  id: number;
  tanggal: string;
  kegiatan: string;
  kendala?: string|null;
  file?: string|null;
  status_verifikasi: 'pending'|'disetujui'|'ditolak';
  magang?: any;
};

const page = usePage();
const props = defineProps<{ logbooks: any; filters: { status?: string; month?: string; year?: string; date?: string; per_page?: number }; reminderMissingToday: boolean; canCreate: boolean; activeMagang?: any; stats?: { total: number; pending: number; disetujui: number; ditolak: number } | null; canVerify?: boolean }>();
const user = computed(() => page.props.auth?.user || {});

// Filters & pagination
const statusFilter = ref(props.filters?.status || '');
const monthFilter = ref(props.filters?.month || '');
const yearFilter = ref(props.filters?.year || String(new Date().getFullYear()));
const dateFilter = ref(props.filters?.date || '');
const perPage = ref(props.filters?.per_page || 10);

const handleFilter = () => {
  router.get('/logbook', { status: statusFilter.value, month: monthFilter.value, year: yearFilter.value, date: dateFilter.value, per_page: perPage.value }, { preserveState: true, replace: true });
};

watch([statusFilter, monthFilter, yearFilter, dateFilter, perPage], () => {
  handleFilter();
});

// Add/Edit/Delete modal state
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showDetailModal = ref(false);
const selectedItem = ref<LogbookItem | null>(null);

// Add form
const addForm = ref<{ tanggal: string; kegiatan: string; kendala: string; file: File | null }>({ tanggal: new Date().toISOString().slice(0,10), kegiatan: '', kendala: '', file: null });
const addErrors = ref<Record<string, string>>({});
const isSavingAdd = ref(false);
const acceptDocs = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
const addFileInputRef = ref<HTMLInputElement | null>(null);
const browseAddFile = () => { addFileInputRef.value?.click(); };

const openAddModal = () => { showAddModal.value = true; addErrors.value = {}; };
const closeAddModal = () => { showAddModal.value = false; };

const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0] || null;
  // Validate file type & size (max 5MB). Allowed: PDF, DOC, DOCX, JPG, PNG
  const allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/jpeg',
    'image/png',
  ];
  const maxSize = 5 * 1024 * 1024; // 5MB
  addErrors.value.file = '';
  if (file) {
    const isValidType = allowedTypes.includes(file.type);
    if (!isValidType) {
      addErrors.value.file = 'Tipe file tidak didukung. Gunakan PDF, DOC, DOCX, JPG, atau PNG.';
      addForm.value.file = null;
      input.value = '';
      return;
    }
    if (file.size > maxSize) {
      addErrors.value.file = 'Ukuran file maksimal 5MB.';
      addForm.value.file = null;
      input.value = '';
      return;
    }
  }
  addForm.value.file = file;
};

const submitAdd = () => {
  isSavingAdd.value = true;
  addErrors.value = {};
  const formData = new FormData();
  formData.append('tanggal', addForm.value.tanggal);
  formData.append('kegiatan', addForm.value.kegiatan);
  if (addForm.value.kendala) formData.append('kendala', addForm.value.kendala);
  if (addForm.value.file) formData.append('file', addForm.value.file);

  router.post('/logbook', formData, {
    onSuccess: () => { isSavingAdd.value = false; showAddModal.value = false; addForm.value = { tanggal: new Date().toISOString().slice(0,10), kegiatan: '', kendala: '', file: null }; },
    onError: (errors: Record<string, string>) => { addErrors.value = errors || {}; isSavingAdd.value = false; },
  });
};

// Edit form
const editForm = ref<{ tanggal: string; kegiatan: string; kendala: string; file: File | null }>({ tanggal: '', kegiatan: '', kendala: '', file: null });
const editErrors = ref<Record<string, string>>({});
const isSavingEdit = ref(false);
const editFileInputRef = ref<HTMLInputElement | null>(null);
const browseEditFile = () => { editFileInputRef.value?.click(); };

const handleEditFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0] || null;
  // Validate file type & size (max 5MB). Allowed: PDF, DOC, DOCX, JPG, PNG
  const allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/jpeg',
    'image/png',
  ];
  const maxSize = 5 * 1024 * 1024; // 5MB
  editErrors.value.file = '';
  if (file) {
    const isValidType = allowedTypes.includes(file.type);
    if (!isValidType) {
      editErrors.value.file = 'Tipe file tidak didukung. Gunakan PDF, DOC, DOCX, JPG, atau PNG.';
      editForm.value.file = null;
      input.value = '';
      return;
    }
    if (file.size > maxSize) {
      editErrors.value.file = 'Ukuran file maksimal 5MB.';
      editForm.value.file = null;
      input.value = '';
      return;
    }
  }
  editForm.value.file = file;
};

const openEditModal = (item: LogbookItem) => {
  selectedItem.value = item;
  editErrors.value = {};
  editForm.value = { tanggal: item.tanggal?.slice(0,10) || '', kegiatan: item.kegiatan || '', kendala: item.kendala || '', file: null };
  showEditModal.value = true;
};
const closeEditModal = () => { showEditModal.value = false; };

const submitEdit = () => {
  if (!selectedItem.value) return;
  isSavingEdit.value = true;
  editErrors.value = {};
  const formData = new FormData();
  formData.append('_method', 'PUT');
  formData.append('tanggal', editForm.value.tanggal);
  formData.append('kegiatan', editForm.value.kegiatan);
  if (editForm.value.kendala) formData.append('kendala', editForm.value.kendala);
  if (editForm.value.file) formData.append('file', editForm.value.file);
  router.post(`/logbook/${selectedItem.value.id}`, formData, {
    onSuccess: () => { isSavingEdit.value = false; showEditModal.value = false; selectedItem.value = null; },
    onError: (errors: Record<string, string>) => { editErrors.value = errors || {}; isSavingEdit.value = false; },
  });
};

// Delete
const openDeleteModal = (item: LogbookItem) => { selectedItem.value = item; showDeleteModal.value = true; };
const closeDeleteModal = () => { showDeleteModal.value = false; };
const confirmDelete = () => {
  if (!selectedItem.value) return;
  router.delete(`/logbook/${selectedItem.value.id}`, {
    onSuccess: () => { showDeleteModal.value = false; selectedItem.value = null; },
  });
};

// Detail
const openDetailModal = (item: LogbookItem) => { selectedItem.value = item; showDetailModal.value = true; };
const closeDetailModal = () => { showDetailModal.value = false; };

// Helpers
const statusBadgeClass = (status: string) => {
  switch (status) {
    case 'disetujui': return 'bg-green-100 text-green-700';
    case 'ditolak': return 'bg-red-100 text-red-700';
    default: return 'bg-yellow-100 text-yellow-700';
  }
};

// Guru verification form
const verifyForm = ref<{ status_verifikasi: 'pending'|'disetujui'|'ditolak'; catatan_guru: string }>({ status_verifikasi: 'pending', catatan_guru: '' });
const isSavingVerify = ref(false);
const openVerifyFromDetail = (item: LogbookItem) => {
  selectedItem.value = item;
  verifyForm.value = { status_verifikasi: item.status_verifikasi, catatan_guru: (item as any).catatan_guru || '' } as any;
};
const submitVerify = () => {
  if (!selectedItem.value) return;
  isSavingVerify.value = true;
  const formData = new FormData();
  formData.append('status_verifikasi', verifyForm.value.status_verifikasi);
  formData.append('catatan_guru', verifyForm.value.catatan_guru || '');
  router.post(`/logbook/${selectedItem.value.id}/verify`, formData, {
    onSuccess: () => { isSavingVerify.value = false; showDetailModal.value = false; selectedItem.value = null; },
    onFinish: () => { isSavingVerify.value = false; },
  });
};

</script>

<template>
  <AppLayout>
    <Head title="Jurnal Harian" />

    <div class="p-4">
      <!-- Header & Reminder -->
      <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <BookOpen class="h-5 w-5 text-gray-600" />
          <h2 class="text-lg font-semibold text-gray-900">Jurnal Harian</h2>
        </div>
        <Button v-if="props.canCreate && user.role === 'siswa'" class="bg-blue-600 hover:bg-blue-700" @click="openAddModal">
          <Plus class="h-4 w-4 mr-2" /> Tambah Jurnal
        </Button>
      </div>

      <!-- Statcards untuk Guru -->
      <div v-if="props.stats" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardHeader><CardTitle>Total Logbook</CardTitle></CardHeader>
          <CardContent><p class="text-3xl font-bold">{{ props.stats.total }}</p></CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>Belum Diverifikasi</CardTitle></CardHeader>
          <CardContent><p class="text-3xl font-bold text-yellow-600">{{ props.stats.pending }}</p></CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>Disetujui</CardTitle></CardHeader>
          <CardContent><p class="text-3xl font-bold text-green-600">{{ props.stats.disetujui }}</p></CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle>Ditolak</CardTitle></CardHeader>
          <CardContent><p class="text-3xl font-bold text-red-600">{{ props.stats.ditolak }}</p></CardContent>
        </Card>
      </div>

      <div v-if="props.reminderMissingToday" class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-yellow-800 flex items-center gap-2">
        <AlertCircle class="h-4 w-4" />
        <span>Belum ada jurnal untuk hari ini. Yuk, tambahkan sekarang!</span>
      </div>

      <!-- Filter Bar -->
      <Card class="mb-4">
        <CardContent>
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-2">
              <select v-model="statusFilter" class="flex h-10 w-48 rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="disetujui">Disetujui</option>
                <option value="ditolak">Ditolak</option>
              </select>
              <select v-model="monthFilter" class="flex h-10 w-36 rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">Semua Bulan</option>
                <option v-for="m in 12" :key="m" :value="String(m)">Bulan {{ m }}</option>
              </select>
              <select v-model="yearFilter" class="flex h-10 w-28 rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option v-for="y in [new Date().getFullYear()-1, new Date().getFullYear(), new Date().getFullYear()+1]" :key="y" :value="String(y)">{{ y }}</option>
              </select>
              <Input v-model="dateFilter" type="date" class="flex h-10 w-40" />
            </div>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-600">Tampilkan:</span>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="outline" class="w-24">{{ perPage }} entri</Button>
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
        </CardContent>
      </Card>

      <!-- List -->
      <Card>
        <CardHeader>
          <CardTitle>Daftar Jurnal Harian</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="props.logbooks?.data?.length" class="space-y-3">
            <div v-for="item in props.logbooks.data" :key="item.id" class="rounded-lg border border-gray-100 p-4">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                      <span class="text-xs font-medium text-green-600">LB</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ new Date(item.tanggal).toLocaleDateString() }}</p>
                      <p class="text-sm text-gray-600">{{ item.kegiatan }}</p>
                      <p v-if="user.role === 'guru' && item.magang?.siswa" class="text-xs text-gray-500 mt-1">Siswa: {{ item.magang.siswa.nama }}</p>
                    </div>
                  </div>
                  <div v-if="item.kendala" class="mb-2">
                    <span class="text-xs font-medium text-gray-700">Kendala:</span>
                    <span class="text-xs text-gray-600"> {{ item.kendala }}</span>
                  </div>
                  <div v-if="user.role === 'guru' && item.catatan_guru" class="mb-2">
                    <span class="text-xs font-medium text-gray-700">Catatan Guru:</span>
                    <span class="text-xs text-gray-600"> {{ item.catatan_guru }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', statusBadgeClass(item.status_verifikasi)]">{{ item.status_verifikasi }}</span>
                    <Button variant="outline" size="sm" @click="openDetailModal(item)"><Info class="h-4 w-4 mr-1" /> Detail</Button>
                    <Button v-if="user.role === 'siswa'" variant="outline" size="sm" @click="openEditModal(item)"><Pencil class="h-4 w-4 mr-1" /> Edit</Button>
                    <Button v-if="user.role === 'siswa'" variant="destructive" size="sm" @click="openDeleteModal(item)"><Trash2 class="h-4 w-4 mr-1" /> Hapus</Button>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <ImageIcon v-if="item.file" class="h-5 w-5 text-gray-500" />
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-10 text-gray-500">Belum ada jurnal.</div>

          <!-- Pagination -->
          <div v-if="props.logbooks?.links" class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">Menampilkan {{ props.logbooks.from }}–{{ props.logbooks.to }} dari {{ props.logbooks.total }} entri</div>
            <div class="flex items-center gap-1">
              <template v-for="link in props.logbooks.links" :key="link.url + link.label">
                <Link v-if="link.url" :href="link.url" preserve-state class="px-3 py-1 rounded-md border text-sm" :class="{ 'bg-gray-100 font-medium': link.active }" v-html="link.label" />
                <span v-else class="px-3 py-1 rounded-md border text-sm text-gray-400" v-html="link.label" />
              </template>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Add Modal -->
      <Dialog v-model:open="showAddModal">
        <DialogContent class="sm:max-w-xl">
          <DialogHeader>
            <DialogTitle>Tambah Jurnal Harian</DialogTitle>
            <DialogDescription>Isi kegiatan magang harian dan unggah dokumentasi bila ada.</DialogDescription>
          </DialogHeader>
          <form @submit.prevent="submitAdd" class="space-y-4">
            <div class="flex justify-between gap-4 mb-2">
              <div class="w-full">
                <p class="text-sm">Tanggal</p>
                <Input v-model="addForm.tanggal" type="date" :aria-invalid="!!addErrors.tanggal" />
                <p v-if="addErrors.tanggal" class="text-xs text-red-600 mt-1">{{ addErrors.tanggal }}</p>
              </div>
              <div class="w-full">
                <p class="text-sm">Status</p>
                <Input value="pending" disabled />
              </div>
            </div>
            <div>
              <p class="text-sm mb-2">Deskripsi Kegiatan</p>
              <textarea v-model="addForm.kegiatan" class="w-full rounded-md border px-3 py-2 text-sm" rows="4" placeholder="Ceritakan kegiatan hari ini"></textarea>
              <p v-if="addErrors.kegiatan" class="text-xs text-red-600 mt-1">{{ addErrors.kegiatan }}</p>
            </div>
            <div>
              <p class="text-sm mb-2">Kendala (opsional)</p>
              <textarea v-model="addForm.kendala" class="w-full rounded-md border px-3 py-2 text-sm" rows="3" placeholder="Tuliskan kendala jika ada"></textarea>
            </div>
            <div>
              <p class="text-sm font-medium">Dokumentasi Pendukung</p>
              <p class="text-xs text-gray-600 mb-2">Upload File (Opsional)</p>
              <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
                <Upload class="mx-auto h-8 w-8 text-gray-400" />
                <p class="mt-2 text-sm font-medium text-gray-700">Pilih file dokumentasi</p>
                <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG (Max 5MB)</p>
                <div class="mt-3">
                  <Button type="button" class="bg-blue-600 hover:bg-blue-700" @click="browseAddFile">Browse File</Button>
                </div>
                <input ref="addFileInputRef" type="file" :accept="acceptDocs" @change="handleFileChange" class="hidden" />
                <p v-if="addForm.file" class="mt-2 text-xs text-gray-600">Terpilih: {{ addForm.file?.name }}</p>
              </div>
              <p v-if="addErrors.file" class="text-xs text-red-600 mt-1">{{ addErrors.file }}</p>
              <p class="text-xs text-gray-500 mt-2">Jenis file yang dapat diupload: Screenshot hasil kerja, dokumentasi code, foto kegiatan</p>
            </div>
            <DialogFooter>
              <Button type="submit" :disabled="isSavingAdd" class="bg-blue-600 hover:bg-blue-700">
                <span v-if="isSavingAdd">Menyimpan...</span>
                <span v-else>Simpan</span>
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <!-- Edit Modal -->
      <Dialog v-model:open="showEditModal">
        <DialogContent class="sm:max-w-xl">
          <DialogHeader>
            <DialogTitle>Edit Jurnal Harian</DialogTitle>
          </DialogHeader>
          <form @submit.prevent="submitEdit" class="space-y-4">
            <div>
              <p class="text-sm mb-2">Tanggal</p>
              <Input v-model="editForm.tanggal" type="date" :aria-invalid="!!editErrors.tanggal" />
              <p v-if="editErrors.tanggal" class="text-xs text-red-600 mt-1">{{ editErrors.tanggal }}</p>
            </div>
            <div>
              <p class="text-sm mb-2">Status</p>
              <Input :value="selectedItem?.status_verifikasi" disabled />
            </div>
            <div>
              <p class="text-sm mb-2">Deskripsi Kegiatan</p>
              <textarea v-model="editForm.kegiatan" class="w-full rounded-md border px-3 py-2 text-sm" rows="4"></textarea>
              <p v-if="editErrors.kegiatan" class="text-xs text-red-600 mt-1">{{ editErrors.kegiatan }}</p>
            </div>
            <div>
              <p class="text-sm mb-2">Kendala (opsional)</p>
              <textarea v-model="editForm.kendala" class="w-full rounded-md border px-3 py-2 text-sm" rows="3"></textarea>
            </div>
            <div>
              <p class="text-sm font-medium">Ganti Dokumentasi</p>
              <p class="text-xs text-gray-600 mb-2">Upload File (Opsional)</p>
              <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
                <Upload class="mx-auto h-8 w-8 text-gray-400" />
                <p class="mt-2 text-sm font-medium text-gray-700">Pilih file dokumentasi</p>
                <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG (Max 5MB)</p>
                <div class="mt-3">
                  <Button type="button" class="bg-blue-600 hover:bg-blue-700" @click="browseEditFile">Browse File</Button>
                </div>
                <input ref="editFileInputRef" type="file" :accept="acceptDocs" @change="handleEditFileChange" class="hidden" />
                <p v-if="editForm.file" class="mt-2 text-xs text-gray-600">Terpilih: {{ editForm.file?.name }}</p>
              </div>
              <p v-if="editErrors.file" class="text-xs text-red-600 mt-1">{{ editErrors.file }}</p>
            </div>
            <DialogFooter>
              <Button type="submit" :disabled="isSavingEdit" class="bg-blue-600 hover:bg-blue-700">
                <span v-if="isSavingEdit">Menyimpan...</span>
                <span v-else>Simpan Perubahan</span>
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <!-- Delete Modal -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Hapus Jurnal</DialogTitle>
            <DialogDescription>Jurnal yang dihapus tidak dapat dikembalikan.</DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="destructive" @click="confirmDelete"><Trash2 class="h-4 w-4 mr-2" /> Hapus</Button>
            <Button variant="outline" @click="closeDeleteModal">Batal</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Detail Modal -->
      <Dialog v-model:open="showDetailModal">
        <DialogContent class="sm:max-w-xl">
          <DialogHeader>
            <DialogTitle>Detail Jurnal Harian</DialogTitle>
          </DialogHeader>
          <div v-if="selectedItem" class="space-y-3">
            <div class="flex items-center gap-2">
              <CalendarDays class="h-4 w-4" />
              <span class="text-sm">{{ new Date(selectedItem.tanggal).toLocaleDateString() }}</span>
            </div>
            <div>
              <p class="font-medium">Kegiatan</p>
              <p class="text-sm text-gray-700">{{ selectedItem.kegiatan }}</p>
            </div>
            <div v-if="selectedItem.kendala">
              <p class="font-medium">Kendala</p>
              <p class="text-sm text-gray-700">{{ selectedItem.kendala }}</p>
            </div>
            <div>
              <p class="font-medium">Status</p>
              <span :class="['px-2 py-1 rounded-full text-xs font-medium', statusBadgeClass(selectedItem.status_verifikasi)]">{{ selectedItem.status_verifikasi }}</span>
            </div>
            <div v-if="selectedItem.file" class="mt-2">
              <p class="font-medium mb-2">Dokumentasi</p>
              <template v-if="selectedItem.file.match(/\.(jpg|jpeg|png)$/i)">
                <img :src="'/storage/' + selectedItem.file" alt="Dokumentasi" class="rounded-md border max-h-72 object-cover" />
              </template>
              <template v-else>
                <a :href="'/storage/' + selectedItem.file" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:underline text-sm">
                  <Upload class="h-4 w-4" /> Unduh dokumentasi
                </a>
              </template>
            </div>

            <!-- Catatan Guru & Verifikasi (Role Guru) -->
            <div v-if="props.canVerify && user.role === 'guru'" class="mt-4 space-y-2">
              <p class="font-medium">Catatan Guru</p>
              <textarea v-model="verifyForm.catatan_guru" class="w-full rounded-md border px-3 py-2 text-sm" rows="3" placeholder="Tambahkan catatan untuk siswa"></textarea>
              <div class="flex items-center gap-2 mt-2">
                <Button class="bg-green-600 hover:bg-green-700" @click="verifyForm.status_verifikasi='disetujui'; submitVerify()"><CheckCircle class="h-4 w-4 mr-1" /> Setujui</Button>
                <Button variant="destructive" @click="verifyForm.status_verifikasi='ditolak'; submitVerify()"><XCircle class="h-4 w-4 mr-1" /> Tolak</Button>
              </div>
            </div>
          </div>
        </DialogContent>
      </Dialog>

    </div>
  </AppLayout>
</template>