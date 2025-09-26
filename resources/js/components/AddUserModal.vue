<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// Removed Select import - using HTML select instead
import { Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    name: '',
    email: '',
    role: 'siswa',
    password: '',
    password_confirmation: '',
    email_verified: 'false',
});

const errors = ref<Record<string, string>>({});
const isLoading = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        role: 'siswa',
        password: '',
        password_confirmation: '',
        email_verified: 'false',
    };
    errors.value = {};
};

// Watch for modal open/close to reset form
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        resetForm();
    }
});

const validateForm = () => {
    errors.value = {};

    if (!form.value.name.trim()) {
        errors.value.name = 'Nama lengkap harus diisi';
    }

    if (!form.value.email.trim()) {
        errors.value.email = 'Email harus diisi';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = 'Format email tidak valid';
    }

    if (!form.value.role) {
        errors.value.role = 'Role harus dipilih';
    }

    if (!form.value.password) {
        errors.value.password = 'Password harus diisi';
    } else if (form.value.password.length < 6) {
        errors.value.password = 'Password minimal 6 karakter';
    }

    if (!form.value.password_confirmation) {
        errors.value.password_confirmation = 'Konfirmasi password harus diisi';
    } else if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = 'Konfirmasi password tidak cocok';
    }

    return Object.keys(errors.value).length === 0;
};

const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    isLoading.value = true;

    router.post('/users', {
        name: form.value.name,
        email: form.value.email,
        role: form.value.role,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        email_verified: form.value.email_verified === 'true',
    }, {
        onSuccess: () => {
            emit('success');
        },
        onError: (error) => {
            errors.value = error;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const handleCancel = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Tambah User Baru</DialogTitle>
                <DialogDescription>Lengkapi semua informasi yang diperlukan</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Nama Lengkap -->
                <div class="space-y-2">
                    <Label for="name">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Masukkan nama lengkap"
                        :class="{ 'border-red-500': errors.name }"
                    />
                    <p v-if="errors.name" class="text-sm text-red-500">
                        {{ errors.name }}
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
                        placeholder="Contoh: user@email.com"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="text-sm text-red-500">
                        {{ errors.email }}
                    </p>
                </div>

                <!-- Role -->
                <div class="space-y-2">
                    <Label for="role">
                        Role <span class="text-red-500">*</span>
                    </Label>
                    <select
                        id="role"
                        v-model="form.role"
                        :class="[
                            'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                            { 'border-red-500': errors.role }
                        ]"
                    >
                        <option value="">Pilih role</option>
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                        <option value="admin">Admin</option>
                    </select>
                    <p v-if="errors.role" class="text-sm text-red-500">
                        {{ errors.role }}
                    </p>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <Label for="password">
                        Password <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Masukkan password (min. 6 karakter)"
                            :class="{ 'border-red-500': errors.password }"
                        />
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                            @click="showPassword = !showPassword"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </Button>
                    </div>
                    <p v-if="errors.password" class="text-sm text-red-500">
                        {{ errors.password }}
                    </p>
                </div>

                <!-- Konfirmasi Password -->
                <div class="space-y-2">
                    <Label for="password_confirmation">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            placeholder="Ulangi password"
                            :class="{ 'border-red-500': errors.password_confirmation }"
                        />
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                        >
                            <Eye v-if="!showPasswordConfirmation" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </Button>
                    </div>
                    <p v-if="errors.password_confirmation" class="text-sm text-red-500">
                        {{ errors.password_confirmation }}
                    </p>
                </div>

                <!-- Email Verification -->
                <div class="space-y-2">
                    <Label for="email_verified">Email Verification</Label>
                    <select
                        id="email_verified"
                        v-model="form.email_verified"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="false">Unverified</option>
                        <option value="true">Verified</option>
                    </select>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleCancel">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="isLoading" class="bg-blue-600 hover:bg-blue-700">
                        {{ isLoading ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
