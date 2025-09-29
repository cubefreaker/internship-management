<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Users, Calendar } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

// Get school settings from shared data
const page = usePage();
const schoolSettings = computed(() => page.props.schoolSettings);
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
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <div>
                <h1 class="text-md font-semibold text-gray-900">{{ schoolSettings.nama_sekolah }}</h1>
                <p class="text-xs text-gray-600">{{ schoolSettings.alamat }}</p>
            </div>
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
    </header>
</template>
