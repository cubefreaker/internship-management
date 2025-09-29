<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Plus, Search, Edit, Trash2, Users, CheckCircle, XCircle, Filter, Eye } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
// Removed Select import - using HTML select instead
import AddUserModal from '@/components/AddUserModal.vue';
import EditUserModal from '@/components/EditUserModal.vue';
import DeleteUserDialog from '@/components/DeleteUserDialog.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Pengguna',
        href: '/users',
    },
];

const page = usePage();
const props = defineProps<{
    users: any;
    stats: {
        total_users: number;
        admin_users: number;
        guru_users: number;
        siswa_users: number;
    };
    filters: {
        search?: string;
        role?: string;
        per_page?: number;
    };
}>();

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'all');
const perPage = ref(props.filters.per_page || 5);
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteDialog = ref(false);
const selectedUser = ref<any>(null);

// Watch for search changes and debounce
let searchTimeout: number;
watch(search, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/users', {
            search: newValue,
            role: roleFilter.value,
            per_page: perPage.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

// Watch for role filter changes
watch(roleFilter, (newValue) => {
    router.get('/users', {
        search: search.value,
        role: newValue,
        per_page: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

// Watch for per page changes
watch(perPage, (newValue) => {
    router.get('/users', {
        search: search.value,
        role: roleFilter.value,
        per_page: newValue,
    }, {
        preserveState: true,
        replace: true,
    });
});

const getRoleBadgeVariant = (role: string) => {
    switch (role) {
        case 'admin':
            return 'default';
        case 'guru':
            return 'secondary';
        case 'siswa':
            return 'outline';
        default:
            return 'outline';
    }
};

const getRoleIcon = (role: string) => {
    switch (role) {
        case 'admin':
            return '👑';
        case 'guru':
            return '👨‍🏫';
        case 'siswa':
            return '🎓';
        default:
            return '👤';
    }
};

const handleEdit = (user: any) => {
    selectedUser.value = user;
    showEditModal.value = true;
};

const handleDelete = (user: any) => {
    selectedUser.value = user;
    showDeleteDialog.value = true;
};

const handleAddSuccess = () => {
    showAddModal.value = false;
    router.reload();
};

const handleEditSuccess = () => {
    showEditModal.value = false;
    selectedUser.value = null;
    router.reload();
};

const handleDeleteSuccess = () => {
    showDeleteDialog.value = false;
    selectedUser.value = null;
    router.reload();
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head title="Manajemen User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Manajemen User</h1>
                        <p class="text-gray-600">Kelola pengguna sistem</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total User</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_users }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Admin</CardTitle>
                        <span class="text-lg">👑</span>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.admin_users }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Guru</CardTitle>
                        <span class="text-lg">👨‍🏫</span>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.guru_users }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Siswa</CardTitle>
                        <span class="text-lg">🎓</span>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.siswa_users }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-gray-600" />
                            <CardTitle>Daftar User</CardTitle>
                        </div>
                        <Button @click="showAddModal = true" class="bg-blue-600 hover:bg-blue-700">
                            <Plus class="h-4 w-4 mr-2" />
                            Tambah User
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Search and Filter Bar -->
                    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                <Input
                                    v-model="search"
                                    placeholder="Cari nama, email, atau role..."
                                    class="pl-10 w-full sm:w-80"
                                />
                            </div>
                            <div class="flex gap-2">
                                <select
                                    v-model="roleFilter"
                                    class="flex h-10 w-40 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="all">Semua Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="guru">Guru</option>
                                    <option value="siswa">Siswa</option>
                                </select>
                                <select
                                    v-model="perPage"
                                    class="flex h-10 w-32 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="5">Tampilkan: 5</option>
                                    <option value="10">Tampilkan: 10</option>
                                    <option value="25">Tampilkan: 25</option>
                                    <option value="50">Tampilkan: 50</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">User</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Email & Verifikasi</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Role</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Terdaftar</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-gray-50">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                                {{ getInitials(user.name) }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ user.name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ user.id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-900">{{ user.email }}</span>
                                            <Badge v-if="user.email_verified_at" variant="default" class="bg-green-100 text-green-800">
                                                <CheckCircle class="h-3 w-3 mr-1" />
                                                Verified
                                            </Badge>
                                            <Badge v-else variant="secondary" class="bg-gray-100 text-gray-800">
                                                <XCircle class="h-3 w-3 mr-1" />
                                                Unverified
                                            </Badge>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <Badge :variant="getRoleBadgeVariant(user.role)" class="flex items-center gap-1 w-fit">
                                            <span>{{ getRoleIcon(user.role) }}</span>
                                            {{ user.role === 'admin' ? 'Admin' : user.role === 'guru' ? 'Guru' : 'Siswa' }}
                                        </Badge>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="handleEdit(user)"
                                                class="h-8 w-8 p-0"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="handleDelete(user)"
                                                class="h-8 w-8 p-0 text-red-600 hover:text-red-700 hover:bg-red-50"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between mt-6">
                        <div class="text-sm text-gray-600">
                            Menampilkan {{ users.from }} sampai {{ users.to }} dari {{ users.total }} entri
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!users.prev_page_url"
                                @click="router.get(users.prev_page_url)"
                            >
                                &lt;&lt;
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!users.prev_page_url"
                                @click="router.get(users.prev_page_url)"
                            >
                                &lt;
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!users.next_page_url"
                                @click="router.get(users.next_page_url)"
                            >
                                &gt;
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!users.next_page_url"
                                @click="router.get(users.next_page_url)"
                            >
                                &gt;&gt;
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Add User Modal -->
        <AddUserModal
            :open="showAddModal"
            @update:open="showAddModal = $event"
            @success="handleAddSuccess"
        />

        <!-- Edit User Modal -->
        <EditUserModal
            :open="showEditModal"
            :user="selectedUser"
            @update:open="showEditModal = $event"
            @success="handleEditSuccess"
        />

        <!-- Delete User Dialog -->
        <DeleteUserDialog
            :open="showDeleteDialog"
            :user="selectedUser"
            @update:open="showDeleteDialog = $event"
            @success="handleDeleteSuccess"
        />
    </AppLayout>
</template>
