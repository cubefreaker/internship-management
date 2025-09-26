<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// Removed Select import - using HTML select instead
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Info } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    user?: any;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    name: '',
    email: '',
    role: '',
    email_verified: 'false',
});

const errors = ref<Record<string, string>>({});
const isLoading = ref(false);

const isEdit = computed(() => !!props.user);
const title = computed(() => isEdit.value ? 'Edit User' : 'Tambah User Baru');
const description = computed(() => isEdit.value ? 'Perbarui informasi user' : 'Lengkapi semua informasi yang diperlukan');

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        role: '',
        email_verified: 'false',
    };
    errors.value = {};
};

// Watch for user prop changes to populate form
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.value = {
            name: newUser.name || '',
            email: newUser.email || '',
            role: newUser.role || '',
            email_verified: !!newUser.email_verified_at ? 'true' : 'false',
        };
    } else {
        resetForm();
    }
}, { immediate: true });

// Watch for modal open/close to populate or reset form
watch(() => props.open, (isOpen) => {
    if (isOpen && props.user) {
        // Populate form when modal opens with user data
        form.value = {
            name: props.user.name || '',
            email: props.user.email || '',
            role: props.user.role || '',
            email_verified: !!props.user.email_verified_at ? 'true' : 'false',
        };
    } else if (!isOpen) {
        // Reset form when modal closes
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

    return Object.keys(errors.value).length === 0;
};

const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    isLoading.value = true;

    router.put(`/users/${props.user.id}`, {
        name: form.value.name,
        email: form.value.email,
        role: form.value.role,
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
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
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

                <!-- Password Note -->
                <Alert>
                    <Info class="h-4 w-4" />
                    <AlertDescription>
                        <strong>Catatan:</strong> Untuk mengubah password, silakan gunakan fitur reset password yang terpisah.
                    </AlertDescription>
                </Alert>

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
