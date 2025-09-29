<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        
        <!-- School Info in Header -->
        <div v-if="schoolSettings?.nama_sekolah" class="hidden md:flex items-center gap-2">
            <div v-if="schoolSettings?.logo_url" class="h-8 w-8 rounded overflow-hidden">
                <img 
                    :src="`/storage/${schoolSettings.logo_url}`" 
                    alt="Logo Sekolah" 
                    class="h-full w-full object-contain"
                />
            </div>
            <div class="text-sm">
                <span class="font-medium">{{ schoolSettings.nama_sekolah }}</span>
            </div>
        </div>
    </header>
</template>
