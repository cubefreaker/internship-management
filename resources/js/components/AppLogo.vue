<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Get school settings from shared data
const page = usePage();
const schoolSettings = computed(() => page.props.schoolSettings);
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground overflow-hidden"
    >
        <img 
            v-if="schoolSettings?.logo_url" 
            :src="`/storage/${schoolSettings.logo_url}`" 
            alt="Logo Sekolah" 
            class="h-full w-full object-contain"
        />
        <AppLogoIcon v-else class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ schoolSettings?.nama_sekolah || 'Sistem Manajemen Magang' }}
        </span>
    </div>
</template>
