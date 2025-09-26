<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Building2, Users, Settings } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        subtitle: 'Ringkasan sistem',
    },
    {
        title: 'DUDI',
        href: '#', // TODO: Add proper route
        icon: Building2,
        subtitle: 'Manajemen DUDI',
    },
    {
        title: 'Pengguna',
        href: '#', // TODO: Add proper route
        icon: Users,
        subtitle: 'Manajemen user',
    },
    {
        title: 'Pengaturan',
        href: '#', // TODO: Add proper route
        icon: Settings,
        subtitle: 'Konfigurasi sistem',
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <div class="px-2 py-4">
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <div class="h-2 w-2 rounded-full bg-green-500"></div>
                    <span class="font-medium">SMK Negeri 1 Surabaya</span>
                </div>
                <div class="text-xs text-gray-500 ml-4">
                    Sistem Pelaporan v1.0
                </div>
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
