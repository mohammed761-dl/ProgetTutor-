<script setup>
// filepath: resources/js/Pages/Users/Index.vue

import AdminApp from "../../Layout/AdminApp.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

// ShadCN Card components
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Badge from "@/components/ui/badge/Badge.vue";

// ShadCN Table components
import Table from "@/components/ui/table/Table.vue";
import TableHeader from "@/components/ui/table/TableHeader.vue";
import TableBody from "@/components/ui/table/TableBody.vue";
import TableRow from "@/components/ui/table/TableRow.vue";
import TableCell from "@/components/ui/table/TableCell.vue";
import TableHead from "@/components/ui/table/TableHead.vue";

// ShadCN Button components
import Button from "@/components/ui/button/Button.vue";
import { Trash2, Edit, Plus, Users, Crown, Briefcase, Eye } from "lucide-vue-next";

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
});

const isDevelopment = import.meta.env.DEV;

const validUsers = computed(() => {
    return (props.users || []).filter(user => 
        user && user.id !== null && typeof user === 'object'
    );
});

const deleteForm = useForm({});

const deleteUser = (userId) => {
    if (!userId) return;
    
    if (confirm('Are you sure you want to delete this user?')) {
        deleteForm.delete(`/User/${userId}`, {
            onSuccess: () => console.log('User deleted successfully'),
            onError: (errors) => console.error('Error deleting user:', errors)
        });
    }
};

// Get role badge class
const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'CEO':
            return 'bg-red-100 text-red-800 hover:bg-red-200';
        case 'Commercial':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
        case 'Viewer':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        default:
            return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch {
        return 'Invalid Date';
    }
};

// ✅ Statistics for user roles
const totalUsers = computed(() => validUsers.value.length);
const ceoUsers = computed(() => 
    validUsers.value.filter(user => user.role === 'CEO').length
);
const commercialUsers = computed(() => 
    validUsers.value.filter(user => user.role === 'Commercial').length
);
const viewerUsers = computed(() => 
    validUsers.value.filter(user => user.role === 'Viewer').length
);
</script>

<template>
    <AdminApp>
        <div class="pt-6 space-y-8">
            <!-- Users Management Card -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle class="flex items-center">
                                <Users class="w-5 h-5 mr-2" />
                                Users Management
                            </CardTitle>
                            <CardDescription>
                                Manage system users and their permissions
                            </CardDescription>
                        </div>
                        <Link href="/User/create">
                            <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                                <Plus class="w-4 h-4 mr-2" />
                                Add New User
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Users Table -->
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User ID</TableHead>
                                <TableHead>Name & Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Created Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="user in validUsers" 
                                :key="user.id" 
                                class="hover:bg-gray-50"
                            >
                                <TableCell class="font-medium">
                                    <div class="flex items-center">
                                        <Users class="w-4 h-4 mr-2 text-gray-400" />
                                        <span class="font-mono text-sm">#{{ user.id || 'N/A' }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="font-medium">
                                    <div>
                                        <div class="font-semibold">{{ user.name || 'Unknown User' }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ user.email || 'No email' }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getRoleBadgeClass(user.role)">
                                        <Crown v-if="user.role === 'CEO'" class="w-3 h-3 mr-1" />
                                        <Briefcase v-else-if="user.role === 'Commercial'" class="w-3 h-3 mr-1" />
                                        <Eye v-else-if="user.role === 'Viewer'" class="w-3 h-3 mr-1" />
                                        {{ user.role || 'Viewer' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(user.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <Badge class="bg-green-100 text-green-800 hover:bg-green-200">
                                        Active
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center justify-center space-x-2">
                                        <Link 
                                            v-if="user.id" 
                                            :href="`/User/${user.id}/edit`"
                                        >
                                            <Button variant="outline" size="sm" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                                        <Button 
                                            v-if="user.id"
                                            variant="outline" 
                                            size="sm" 
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                            @click="deleteUser(user.id)"
                                            :disabled="deleteForm.processing"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="validUsers.length === 0" class="text-center py-12">
                        <Users class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                        <p class="text-gray-500 mb-4">Get started by adding your first user.</p>
                        <Link href="/User/create">
                            <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                                <Plus class="w-4 h-4 mr-2" />
                                Add First User
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- ✅ Statistics Cards - Same design as Products/Customers -->
            <div class="grid gap-6 md:grid-cols-4">
                <!-- Total Users -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-blue-600">
                            <Users class="w-5 h-5 mr-2" />
                            Total Users
                        </CardTitle>
                        <CardDescription>All system users</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-blue-600">{{ totalUsers }}</div>
                    </CardContent>
                </Card>

                <!-- CEO Users -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-red-600">
                            <Crown class="w-5 h-5 mr-2" />
                            CEO
                        </CardTitle>
                        <CardDescription>Executive administrators</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-red-600">{{ ceoUsers }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalUsers > 0 ? Math.round((ceoUsers / totalUsers) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>

                <!-- Commercial Users -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-blue-600">
                            <Briefcase class="w-5 h-5 mr-2" />
                            Commercial
                        </CardTitle>
                        <CardDescription>Sales and business users</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-blue-600">{{ commercialUsers }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalUsers > 0 ? Math.round((commercialUsers / totalUsers) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>

                <!-- Viewer Users -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-green-600">
                            <Eye class="w-5 h-5 mr-2" />
                            Viewer
                        </CardTitle>
                        <CardDescription>Read-only access users</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-green-600">{{ viewerUsers }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalUsers > 0 ? Math.round((viewerUsers / totalUsers) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>