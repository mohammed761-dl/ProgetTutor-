````vue
````vue
````vue
<script setup>
// filepath: resources/js/Pages/Dashboard/AdminDashboard.vue

import AdminApp from "../../Layout/AdminApp.vue";
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

// Icons
import { 
    Package, 
    Building2, 
    Users, 
    CheckCircle,
    AlertTriangle,
    Clock,
    Crown,
    Briefcase,
    Eye,
    Activity
} from "lucide-vue-next";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            // Products stats
            totalProducts: 0,
            lowStockProducts: 0,
            outOfStockProducts: 0,
            
            // Customers stats
            totalCustomers: 0,
            goodCustomers: 0,
            averageCustomers: 0,
            poorCustomers: 0,
            
            // Users stats
            totalUsers: 0,
            ceoUsers: 0,
            commercialUsers: 0,
            viewerUsers: 0,
            
            // Activities
            recentActivities: []
        })
    }
});

// Calculate percentages
const productPercentages = computed(() => ({
    inStock: props.stats.totalProducts > 0 ? Math.round(((props.stats.totalProducts - props.stats.outOfStockProducts) / props.stats.totalProducts) * 100) : 0,
    lowStock: props.stats.totalProducts > 0 ? Math.round((props.stats.lowStockProducts / props.stats.totalProducts) * 100) : 0,
    outOfStock: props.stats.totalProducts > 0 ? Math.round((props.stats.outOfStockProducts / props.stats.totalProducts) * 100) : 0,
}));

const customerPercentages = computed(() => ({
    good: props.stats.totalCustomers > 0 ? Math.round((props.stats.goodCustomers / props.stats.totalCustomers) * 100) : 0,
    average: props.stats.totalCustomers > 0 ? Math.round((props.stats.averageCustomers / props.stats.totalCustomers) * 100) : 0,
    poor: props.stats.totalCustomers > 0 ? Math.round((props.stats.poorCustomers / props.stats.totalCustomers) * 100) : 0,
}));

const userPercentages = computed(() => ({
    ceo: props.stats.totalUsers > 0 ? Math.round((props.stats.ceoUsers / props.stats.totalUsers) * 100) : 0,
    commercial: props.stats.totalUsers > 0 ? Math.round((props.stats.commercialUsers / props.stats.totalUsers) * 100) : 0,
    viewer: props.stats.totalUsers > 0 ? Math.round((props.stats.viewerUsers / props.stats.totalUsers) * 100) : 0,
}));

// Get status class for badges
const getStatusClass = (status) => {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        case 'processing':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
        default:
            return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
    }
};

// Get activity icon
const getActivityIcon = (type) => {
    switch (type) {
        case 'product':
            return Package;
        case 'customer':
            return Building2;
        case 'user':
            return Users;
        default:
            return Activity;
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
</script>

<template>
    <AdminApp>
        <div class="pt-6 space-y-8">
            <!-- ✅ Main Stats Cards - 3 Key Metrics -->
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Total Products -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-blue-600">
                            <Package class="w-5 h-5 mr-2" />
                            Total Products
                        </CardTitle>
                        <CardDescription>
                            Products in inventory system
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-blue-600">{{ stats.totalProducts }}</div>
                        <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                            <div class="text-center">
                                <div class="text-green-600 font-semibold">{{ stats.totalProducts - stats.outOfStockProducts }}</div>
                                <div class="text-gray-500">In Stock</div>
                            </div>
                            <div class="text-center">
                                <div class="text-orange-600 font-semibold">{{ stats.lowStockProducts }}</div>
                                <div class="text-gray-500">Low Stock</div>
                            </div>
                            <div class="text-center">
                                <div class="text-red-600 font-semibold">{{ stats.outOfStockProducts }}</div>
                                <div class="text-gray-500">Out Stock</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Customers -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-green-600">
                            <Building2 class="w-5 h-5 mr-2" />
                            Total Customers
                        </CardTitle>
                        <CardDescription>
                            Registered customers in system
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-green-600">{{ stats.totalCustomers }}</div>
                        <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                            <div class="text-center">
                                <div class="text-green-600 font-semibold">{{ stats.goodCustomers }}</div>
                                <div class="text-gray-500">Good</div>
                            </div>
                            <div class="text-center">
                                <div class="text-orange-600 font-semibold">{{ stats.averageCustomers }}</div>
                                <div class="text-gray-500">Average</div>
                            </div>
                            <div class="text-center">
                                <div class="text-red-600 font-semibold">{{ stats.poorCustomers }}</div>
                                <div class="text-gray-500">Poor</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Users -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-purple-600">
                            <Users class="w-5 h-5 mr-2" />
                            Total Users
                        </CardTitle>
                        <CardDescription>
                            Admin and staff users
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-purple-600">{{ stats.totalUsers }}</div>
                        <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                            <div class="text-center">
                                <div class="text-red-600 font-semibold">{{ stats.ceoUsers }}</div>
                                <div class="text-gray-500">CEO</div>
                            </div>
                            <div class="text-center">
                                <div class="text-blue-600 font-semibold">{{ stats.commercialUsers }}</div>
                                <div class="text-gray-500">Commercial</div>
                            </div>
                            <div class="text-center">
                                <div class="text-green-600 font-semibold">{{ stats.viewerUsers }}</div>
                                <div class="text-gray-500">Viewer</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ✅ Recent Activities Table with Dynamic Data -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <Activity class="w-5 h-5 mr-2" />
                        Recent Activities
                    </CardTitle>
                    <CardDescription>
                        Latest system activities and changes
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Action</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Type</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="activity in stats.recentActivities" 
                                :key="activity.id"
                                class="hover:bg-gray-50"
                            >
                                <TableCell class="font-medium">
                                    {{ activity.action }}
                                </TableCell>
                                <TableCell>
                                    {{ activity.description }}
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(activity.date) }}
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusClass(activity.status)">
                                        {{ activity.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <component 
                                            :is="getActivityIcon(activity.type)" 
                                            class="w-4 h-4 mr-2 text-gray-400" 
                                        />
                                        {{ activity.type }}
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="stats.recentActivities.length === 0" class="text-center py-8">
                        <Activity class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No recent activities</h3>
                        <p class="text-gray-500">Activities will appear here as they happen.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminApp>
</template>