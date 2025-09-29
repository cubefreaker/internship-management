<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    settings: {
        id?: number;
        logo_url?: string;
        nama_sekolah?: string;
        alamat?: string;
        telepon?: string;
        email?: string;
        website?: string;
        kepala_sekolah?: string;
        npsn?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan Sekolah',
        href: '/settings/school',
    },
];

const form = ref({
    nama_sekolah: props.settings?.nama_sekolah || '',
    alamat: props.settings?.alamat || '',
    telepon: props.settings?.telepon || '',
    email: props.settings?.email || '',
    website: props.settings?.website || '',
    kepala_sekolah: props.settings?.kepala_sekolah || '',
    npsn: props.settings?.npsn || '',
    logo: null as File | null,
});

const errors = ref<Record<string, string>>({});
const isLoading = ref(false);
const logoPreview = ref<string | null>(props.settings?.logo_url ? `/storage/${props.settings.logo_url}` : null);

const handleLogoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        form.value.logo = file;
        
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submitForm = () => {
    isLoading.value = true;
    errors.value = {};
    
    // Create FormData to handle file upload
    const formData = new FormData();
    formData.append('nama_sekolah', form.value.nama_sekolah);
    formData.append('alamat', form.value.alamat);
    formData.append('telepon', form.value.telepon);
    formData.append('email', form.value.email);
    formData.append('website', form.value.website);
    formData.append('kepala_sekolah', form.value.kepala_sekolah);
    formData.append('npsn', form.value.npsn);
    
    if (form.value.logo) {
        formData.append('logo', form.value.logo);
    }
    
    router.post('/settings/school', formData, {
        onSuccess: () => {
            isLoading.value = false;
        },
        onError: (err) => {
            errors.value = err;
            isLoading.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Pengaturan Sekolah" />

        <div class="px-4 py-6">
            <HeadingSmall
                title="Pengaturan Sekolah"
                description="Kelola informasi sekolah dan lihat pratinjau tampilan"
            />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">
                <!-- Left Column - Form -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-6 w-6 bg-cyan-500 rounded flex items-center justify-center">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                Informasi Sekolah
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitForm" class="space-y-6">
                                                <div class="space-y-4">
                                    <!-- Logo Sekolah -->
                                    <div class="space-y-2">
                                        <Label for="logo">Logo Sekolah</Label>
                                        <div class="flex items-center space-x-4">
                                            <div 
                                                class="h-24 w-24 rounded-md border border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50"
                                            >
                                                <img 
                                                    v-if="logoPreview" 
                                                    :src="logoPreview" 
                                                    alt="Logo Sekolah" 
                                                    class="h-full w-full object-contain"
                                                />
                                                <span v-else class="text-gray-400 text-sm">Logo</span>
                                            </div>
                                            <div class="flex flex-col space-y-2">
                                                <Input
                                                    id="logo"
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleLogoChange"
                                                />
                                                <p class="text-xs text-gray-500">Recommended: 200x200 pixels, max 2MB</p>
                                            </div>
                                        </div>
                                        <InputError :message="errors.logo" />
                                    </div>

                                    <!-- Nama Sekolah -->
                                    <div class="space-y-2">
                                        <Label for="nama_sekolah">Nama Sekolah/Instansi <span class="text-red-500">*</span></Label>
                                        <Input
                                            id="nama_sekolah"
                                            v-model="form.nama_sekolah"
                                            required
                                            placeholder="Masukkan nama sekolah"
                                        />
                                        <InputError :message="errors.nama_sekolah" />
                                    </div>

                                    <!-- Alamat -->
                                    <div class="space-y-2">
                                        <Label for="alamat">Alamat Lengkap <span class="text-red-500">*</span></Label>
                                        <Textarea
                                            id="alamat"
                                            v-model="form.alamat"
                                            required
                                            placeholder="Masukkan alamat lengkap"
                                            :rows="3"
                                        />
                                        <InputError :message="errors.alamat" />
                                    </div>

                                    <!-- Telepon -->
                                    <div class="space-y-2">
                                        <Label for="telepon">Telepon</Label>
                                        <Input
                                            id="telepon"
                                            v-model="form.telepon"
                                            placeholder="Contoh: 031-5678910"
                                        />
                                        <InputError :message="errors.telepon" />
                                    </div>

                                    <!-- Email -->
                                    <div class="space-y-2">
                                        <Label for="email">Email</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            v-model="form.email"
                                            placeholder="Contoh: info@smkn1surabaya.sch.id"
                                        />
                                        <InputError :message="errors.email" />
                                    </div>

                                    <!-- Website -->
                                    <div class="space-y-2">
                                        <Label for="website">Website</Label>
                                        <Input
                                            id="website"
                                            v-model="form.website"
                                            placeholder="Contoh: www.smkn1surabaya.sch.id"
                                        />
                                        <InputError :message="errors.website" />
                                    </div>

                                    <!-- Kepala Sekolah -->
                                    <div class="space-y-2">
                                        <Label for="kepala_sekolah">Kepala Sekolah</Label>
                                        <Input
                                            id="kepala_sekolah"
                                            v-model="form.kepala_sekolah"
                                            placeholder="Nama kepala sekolah"
                                        />
                                        <InputError :message="errors.kepala_sekolah" />
                                    </div>

                                    <!-- NPSN -->
                                    <div class="space-y-2">
                                        <Label for="npsn">NPSN (Nomor Pokok Sekolah Nasional)</Label>
                                        <Input
                                            id="npsn"
                                            v-model="form.npsn"
                                            placeholder="Nomor Pokok Sekolah Nasional"
                                        />
                                        <InputError :message="errors.npsn" />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4">
                                    <Button type="submit" :disabled="isLoading" class="bg-cyan-500 hover:bg-cyan-600 text-white">
                                        <span v-if="isLoading">Menyimpan...</span>
                                        <span v-else>Simpan</span>
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column - Preview -->
                <div class="space-y-6">
                    <!-- Preview Tampilan Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-6 w-6 bg-gray-500 rounded flex items-center justify-center">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                Preview Tampilan
                            </CardTitle>
                            <p class="text-sm text-gray-600">Pratinjau bagaimana informasi sekolah akan ditampilkan</p>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-gray-600 mb-4">Informasi Penggunaan:</p>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 bg-blue-500 rounded"></div>
                                    <span><strong>Dashboard:</strong> Logo dan nama sekolah ditampilkan di header navigasi</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 bg-blue-500 rounded"></div>
                                    <span><strong>Rapor/Sertifikat:</strong> Informasi lengkap sebagai kop dokumen resmi</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 bg-blue-500 rounded"></div>
                                    <span><strong>Dokumen Cetak:</strong> Footer atau header pada laporan yang dicetak</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Dashboard Header Preview -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-6 w-6 bg-blue-500 rounded flex items-center justify-center">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
                                    </svg>
                                </div>
                                Dashboard Header
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 rounded overflow-hidden bg-white border flex items-center justify-center">
                                        <img 
                                            v-if="logoPreview" 
                                            :src="logoPreview" 
                                            alt="Logo Sekolah" 
                                            class="h-full w-full object-contain"
                                        />
                                        <span v-else class="text-gray-400 text-xs">Logo</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg">{{ form.nama_sekolah || 'SMK Negeri 1 Surabaya' }}</h3>
                                        <p class="text-sm text-gray-600">Sistem Informasi Magang</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Header Rapor/Sertifikat Preview -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-6 w-6 bg-green-500 rounded flex items-center justify-center">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Header Rapor/Sertifikat
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-white p-6 rounded-lg border text-center">
                                <div class="flex justify-center mb-4">
                                    <div class="h-16 w-16 rounded overflow-hidden bg-gray-50 border flex items-center justify-center">
                                        <img 
                                            v-if="logoPreview" 
                                            :src="logoPreview" 
                                            alt="Logo Sekolah" 
                                            class="h-full w-full object-contain"
                                        />
                                        <span v-else class="text-gray-400 text-xs">Logo</span>
                                    </div>
                                </div>
                                <h2 class="font-bold text-lg uppercase">{{ form.nama_sekolah || 'SMK Negeri 1 Surabaya' }}</h2>
                                <p class="text-sm">{{ form.alamat || 'Jl. SMEA No.4, Sawahan, Kec. Sawahan, Kota Surabaya, Jawa Timur 60252' }}</p>
                                <p class="text-sm">Telp: {{ form.telepon || '031-5678910' }} | Email: {{ form.email || 'info@smkn1surabaya.sch.id' }}</p>
                                <p class="text-sm">Web: {{ form.website || 'www.smkn1surabaya.sch.id' }}</p>
                                <div class="mt-4 pt-2 border-t">
                                    <h3 class="text-base font-bold">SERTIFIKAT MAGANG</h3>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Dokumen Cetak Preview -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-6 w-6 bg-purple-500 rounded flex items-center justify-center">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </div>
                                Dokumen Cetak
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-white p-4 rounded-lg border">
                                <div class="flex items-center justify-between text-xs text-gray-600 border-b pb-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded overflow-hidden bg-gray-50 border flex items-center justify-center">
                                            <img 
                                                v-if="logoPreview" 
                                                :src="logoPreview" 
                                                alt="Logo Sekolah" 
                                                class="h-full w-full object-contain"
                                            />
                                            <span v-else class="text-gray-400 text-xs">Logo</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold">{{ form.nama_sekolah || 'SMK Negeri 1 Surabaya' }}</div>
                                            <div>NPSN: {{ form.npsn || '20567890' }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div>{{ form.alamat || 'Jl. SMEA No.4, Sawahan, Kec. Sawahan, Kota Surabaya, Jawa Timur 60252' }}</div>
                                        <div>{{ form.telepon || '031-5678910' }} | {{ form.email || 'info@smkn1surabaya.sch.id' }}</div>
                                        <div>Kepala Sekolah: {{ form.kepala_sekolah || 'Drs. H. Surtiono, M.Pd.' }}</div>
                                    </div>
                                </div>
                                <div class="text-center text-sm text-gray-500 mt-2">
                                    Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>