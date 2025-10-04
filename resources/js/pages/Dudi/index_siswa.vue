<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
// Pagination komponen khusus tidak tersedia; gunakan Link dari Inertia sebagai navigasi pagination
import { Building2, Info, CheckCircle } from 'lucide-vue-next';

const props = defineProps<{ dudi: any, stats: any, filters: any, pendaftaran: any }>();
const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 6);
const selectedDudi = ref<any | null>(null);
const showDetailModal = ref(false);
const appliedIds = computed(() => new Set((props.pendaftaran || []).map((p: any) => p.dudi_id)));

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'DUDI', href: '/dudi' },
  { title: 'Cari Tempat Magang', href: '/dudi' }
];

const handleSearch = () => {
  router.get('/dudi', { search: search.value, per_page: perPage.value }, { preserveState: true });
};

const openDetail = (dudi: any) => {
  selectedDudi.value = dudi;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  selectedDudi.value = null;
};

const canApplyMore = computed(() => (props.pendaftaran || []).length < 3);

const apply = (dudi: any) => {
  if (!canApplyMore.value) return;
  router.post(`/dudi/${dudi.id}/apply`, {}, {
    onSuccess: () => {
      // notifikasi handled by flash messages
    }
  });
};

const getQuotaBar = (dudi: any) => {
  const total = dudi.total_siswa_magang || 0;
  const capacity = 12; // placeholder visual bar
  const percent = Math.min(100, Math.round((total / capacity) * 100));
  return { percent, total, capacity };
};
</script>

<template>
  <Head title="Cari Tempat Magang" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Cari Tempat Magang</h1>
          <p class="text-sm text-gray-500">Silakan pilih perusahaan dan ajukan pendaftaran (maks 3).</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
            <Building2 class="h-5 w-5 text-green-600" />
          </div>
          <div>
            <p class="text-xs font-medium text-gray-600">Total DUDI Aktif</p>
            <p class="text-lg font-bold text-gray-900">{{ stats.dudi_aktif }}</p>
          </div>
        </div>
      </div>

      <Card>
        <CardContent>
          <div class="flex items-center gap-4 mb-6">
            <div class="relative flex-1">
              <Input v-model="search" placeholder="Cari perusahaan, bidang..." @keyup.enter="handleSearch" />
            </div>
            <div>
              <Input type="number" v-model="perPage" min="3" max="24" class="w-24" />
            </div>
            <Button class="bg-blue-600 hover:bg-blue-700" @click="handleSearch">Terapkan</Button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="item in dudi.data" :key="item.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ item.nama_perusahaan }}</h3>
                  <p class="text-xs text-gray-500">{{ item.alamat }}</p>
                </div>
                <div class="text-right">
                  <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                        :class="'bg-green-100 text-green-800 border-green-200'">
                    Aktif
                  </span>
                </div>
              </div>

              <div class="mt-4">
                <p class="text-sm text-gray-700">Kontak: {{ item.penanggung_jawab }} • {{ item.telepon }}</p>
                <p class="text-sm text-gray-700">Email: {{ item.email }}</p>
              </div>

              <div class="mt-4">
                <p class="text-xs text-gray-500 mb-2">Kuota Magang</p>
                <div class="h-2 w-full bg-gray-200 rounded-full">
                  <div class="h-2 rounded-full bg-blue-500" :style="{ width: getQuotaBar(item).percent + '%' }"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ getQuotaBar(item).total }}/{{ getQuotaBar(item).capacity }}</p>
              </div>

              <div class="mt-4 text-sm text-gray-600">
                <p>Magang untuk meningkatkan kompetensi dan pengalaman kerja.</p>
              </div>

              <div class="mt-5 flex items-center justify-between">
                <Button variant="outline" class="border-gray-300" @click="openDetail(item)">
                  <Info class="h-4 w-4 mr-2" /> Detail
                </Button>

                <Button :disabled="appliedIds.has(item.id) || !canApplyMore"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300"
                        @click="apply(item)">
                  <CheckCircle class="h-4 w-4 mr-2" />
                  {{ appliedIds.has(item.id) ? 'Sudah Mendaftar' : (canApplyMore ? 'Daftar' : 'Batas Tercapai') }}
                </Button>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-600">
              Menampilkan {{ dudi.from || 0 }} sampai {{ dudi.to || 0 }} dari {{ dudi.total }} entri
            </div>
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                :disabled="!dudi.prev_page_url"
                @click="router.get(dudi.prev_page_url)"
              >
                <<
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
                >>
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Detail Modal -->
      <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-lg rounded-xl border border-gray-200 bg-white shadow-lg">
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-900">{{ selectedDudi?.nama_perusahaan }}</h3>
            <p class="text-sm text-gray-600 mt-2">{{ selectedDudi?.alamat }}</p>
            <div class="mt-4 text-sm text-gray-700">
              <p>Penanggung Jawab: {{ selectedDudi?.penanggung_jawab }}</p>
              <p>Telepon: {{ selectedDudi?.telepon }}</p>
              <p>Email: {{ selectedDudi?.email }}</p>
            </div>
          </div>
          <div class="p-6 border-t flex items-center justify-end gap-3">
            <Button variant="outline" class="border-gray-300" @click="closeDetail">Tutup</Button>
            <Button :disabled="appliedIds.has(selectedDudi?.id) || !canApplyMore" class="bg-blue-600 hover:bg-blue-700" @click="apply(selectedDudi)">Daftar</Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>