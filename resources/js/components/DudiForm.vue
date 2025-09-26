<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    open: boolean;
    dudi?: any;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    nama_perusahaan: '',
    alamat: '',
    telepon: '',
    email: '',
    penanggung_jawab: '',
    status: 'aktif',
});

const errors = ref<Record<string, string>>({});
const isLoading = ref(false);

const isEdit = computed(() => !!props.dudi);
const title = computed(() => isEdit.value ? 'Ubah DUDI' : 'Tambah DUDI Baru');
const description = computed(() => isEdit.value ? 'Perbarui informasi DUDI' : 'Lengkapi semua informasi yang diperlukan');

const resetForm = () => {
    form.value = {
        nama_perusahaan: '',
        alamat: '',
        telepon: '',
        email: '',
        penanggung_jawab: '',
        status: 'aktif',
    };
    errors.value = {};
};

// Watch for dudi prop changes to populate form
watch(() => props.dudi, (newDudi) => {
    if (newDudi) {
        form.value = {
            nama_perusahaan: newDudi.nama_perusahaan || '',
            alamat: newDudi.alamat || '',
            telepon: newDudi.telepon || '',
            email: newDudi.email || '',
            penanggung_jawab: newDudi.penanggung_jawab || '',
            status: newDudi.status || 'aktif',
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for modal open/close to reset form
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        resetForm();
    }
});

const validateForm = () => {
    errors.value = {};

    if (!form.value.nama_perusahaan.trim()) {
        errors.value.nama_perusahaan = 'Nama perusahaan wajib diisi.';
    }

    if (!form.value.alamat.trim()) {
        errors.value.alamat = 'Alamat wajib diisi.';
    }

    if (!form.value.telepon.trim()) {
        errors.value.telepon = 'Telepon wajib diisi.';
    }

    if (!form.value.email.trim()) {
        errors.value.email = 'Email wajib diisi.';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = 'Format email tidak valid.';
    }

    if (!form.value.penanggung_jawab.trim()) {
        errors.value.penanggung_jawab = 'Penanggung jawab wajib diisi.';
    }

    if (!form.value.status) {
        errors.value.status = 'Status wajib dipilih.';
    }

    return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
    if (!validateForm()) {
        return;
    }

    isLoading.value = true;

    try {
        if (isEdit.value) {
            await router.put(`/dudi/${props.dudi.id}`, form.value, {
                onSuccess: () => {
                    emit('success');
                    emit('update:open', false);
                },
                onError: (error) => {
                    errors.value = error;
                },
            });
        } else {
            await router.post('/dudi', form.value, {
                onSuccess: () => {
                    emit('success');
                    emit('update:open', false);
                },
                onError: (error) => {
                    errors.value = error;
                },
            });
        }
    } finally {
        isLoading.value = false;
    }
};

const handleCancel = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Nama Perusahaan -->
                <div class="space-y-2">
                    <Label for="nama_perusahaan">
                        Nama Perusahaan <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="nama_perusahaan"
                        v-model="form.nama_perusahaan"
                        placeholder="Masukkan nama perusahaan"
                        :class="{ 'border-red-500': errors.nama_perusahaan }"
                    />
                    <p v-if="errors.nama_perusahaan" class="text-sm text-red-500">
                        {{ errors.nama_perusahaan }}
                    </p>
                </div>

                <!-- Alamat -->
                <div class="space-y-2">
                    <Label for="alamat">
                        Alamat <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="alamat"
                        v-model="form.alamat"
                        placeholder="Masukkan alamat lengkap"
                        :class="{ 'border-red-500': errors.alamat }"
                    />
                    <p v-if="errors.alamat" class="text-sm text-red-500">
                        {{ errors.alamat }}
                    </p>
                </div>

                <!-- Telepon -->
                <div class="space-y-2">
                    <Label for="telepon">
                        Telepon <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="telepon"
                        v-model="form.telepon"
                        placeholder="Contoh: 021-12345678"
                        :class="{ 'border-red-500': errors.telepon }"
                    />
                    <p v-if="errors.telepon" class="text-sm text-red-500">
                        {{ errors.telepon }}
                    </p>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <Label for="email">
                        Email <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Contoh: info@perusahaan.com"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="text-sm text-red-500">
                        {{ errors.email }}
                    </p>
                </div>

                <!-- Penanggung Jawab -->
                <div class="space-y-2">
                    <Label for="penanggung_jawab">
                        Penanggung Jawab <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="penanggung_jawab"
                        v-model="form.penanggung_jawab"
                        placeholder="Nama penanggung jawab"
                        :class="{ 'border-red-500': errors.penanggung_jawab }"
                    />
                    <p v-if="errors.penanggung_jawab" class="text-sm text-red-500">
                        {{ errors.penanggung_jawab }}
                    </p>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <Label for="status">
                        Status <span class="text-red-500">*</span>
                    </Label>
                    <select
                        id="status"
                        v-model="form.status"
                        :class="[
                            'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                            { 'border-red-500': errors.status }
                        ]"
                    >
                        <option value="">Pilih status</option>
                        <option value="aktif">Aktif</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                    <p v-if="errors.status" class="text-sm text-red-500">
                        {{ errors.status }}
                    </p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleCancel" :disabled="isLoading">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="isLoading" class="bg-blue-600 hover:bg-blue-700">
                        <span v-if="isLoading">Menyimpan...</span>
                        <span v-else>Simpan</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
