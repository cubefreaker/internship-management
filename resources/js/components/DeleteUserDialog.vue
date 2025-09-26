<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    user?: any;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const isLoading = ref(false);

// Watch for modal open/close
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        isLoading.value = false;
    }
});

const handleDelete = () => {
    if (!props.user) return;

    isLoading.value = true;

    router.delete(`/users/${props.user.id}`, {
        onSuccess: () => {
            emit('success');
        },
        onError: (error) => {
            console.error('Error deleting user:', error);
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
                <DialogTitle class="flex items-center gap-2">
                    <AlertTriangle class="h-5 w-5 text-red-500" />
                    Konfirmasi Hapus
                </DialogTitle>
                <DialogDescription>
                    Apakah Anda yakin ingin menghapus data user ini?<br>
                    Tindakan ini tidak dapat dibatalkan.
                </DialogDescription>
            </DialogHeader>

            <div v-if="user" class="py-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                            {{ user.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ user.name }}</div>
                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                            <div class="text-sm text-gray-500 capitalize">{{ user.role }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="handleCancel" :disabled="isLoading">
                    Batal
                </Button>
                <Button 
                    type="button" 
                    variant="destructive" 
                    @click="handleDelete" 
                    :disabled="isLoading"
                    class="bg-red-600 hover:bg-red-700"
                >
                    {{ isLoading ? 'Menghapus...' : 'Ya, Hapus' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
