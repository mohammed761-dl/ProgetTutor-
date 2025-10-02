<script setup>
// filepath: resources/js/Pages/ARO/Index.vue

import Layout from "../../Layout/App.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Badge from "@/components/ui/badge/Badge.vue";
import Table from "@/components/ui/table/Table.vue";
import TableHeader from "@/components/ui/table/TableHeader.vue";
import TableBody from "@/components/ui/table/TableBody.vue";
import TableRow from "@/components/ui/table/TableRow.vue";
import TableCell from "@/components/ui/table/TableCell.vue";
import TableHead from "@/components/ui/table/TableHead.vue";
import Button from "@/components/ui/button/Button.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import { 
    Trash2, 
    Edit, 
    Plus, 
    Eye, 
    FileCheck, 
    Building2, 
    User, 
    Calendar, 
    CheckCircle, 
    Clock, 
    XCircle,
    BarChart3,
    ClipboardList,
    TrendingUp,
    FileText,
    Printer
} from "lucide-vue-next";

const props = defineProps({
    aros: {
        type: [Array, Object],
        default: () => []
    }
});

const itemsPerPage = 10;
const currentPage = ref(1);
const sortField = ref(null);
const sortDirection = ref("asc");

// ✅ Sort function
function handleSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
}

// Helper function to get the actual data array
const getDataArray = computed(() => {
    if (Array.isArray(props.aros)) {
        return props.aros;
    }
    // If it's a paginated object, return the data property
    return props.aros?.data || [];
});

// ✅ Sorted data
const sortedData = computed(() => {
    const data = getDataArray.value;
    if (!sortField.value) return data;
    return [...data].sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];
        
        if (sortField.value === "date_aro") {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        }
        
        if (aValue < bValue) return sortDirection.value === "asc" ? -1 : 1;
        if (aValue > bValue) return sortDirection.value === "asc" ? 1 : -1;
        return 0;
    });
});

// ✅ Paginated data - use Laravel's pagination if available, otherwise client-side
const paginatedData = computed(() => {
    // If it's a paginated object from Laravel, use its data
    if (props.aros?.data && !Array.isArray(props.aros)) {
        return sortedData.value;
    }
    // Otherwise, do client-side pagination
    return sortedData.value.slice(
        (currentPage.value - 1) * itemsPerPage,
        currentPage.value * itemsPerPage
    );
});

// ✅ Total pages - use Laravel's pagination if available
const totalPages = computed(() => {
    if (props.aros?.last_page && !Array.isArray(props.aros)) {
        return props.aros.last_page;
    }
    return Math.ceil(getDataArray.value.length / itemsPerPage);
});

// Statistics
const totalAROs = computed(() => {
    if (props.aros?.total && !Array.isArray(props.aros)) {
        return props.aros.total;
    }
    return getDataArray.value.length;
});
const monthlyTrend = "+12%";
const deliveredAROs = computed(() => 
    getDataArray.value.filter(aro => aro.status === 'Delivered').length
);
const pendingAROs = computed(() => 
    getDataArray.value.filter(aro => aro.status === 'Pending').length
);
const partiallyDeliveredAROs = computed(() => 
    getDataArray.value.filter(aro => aro.status === 'Partially Delivered').length
);
const cancelledAROs = computed(() => 
    getDataArray.value.filter(aro => aro.status === 'Cancelled').length
);

const deleteForm = useForm({});

const deleteARO = (aroId) => {
    if (!aroId) return;
    
    if (confirm('Are you sure you want to delete this ARO?')) {
        deleteForm.delete(`/aro/${aroId}`, {
            onSuccess: () => console.log('ARO deleted successfully'),
            onError: (errors) => console.error('Error deleting ARO:', errors)
        });
    }
};

// ✅ Status badge colors
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Delivered':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        case 'Partially Delivered':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
        case 'Cancelled':
            return 'bg-red-100 text-red-800 hover:bg-red-200';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// ✅ Status icon
const getStatusIcon = (status) => {
    switch (status) {
        case 'Delivered':
            return CheckCircle;
        case 'Pending':
            return Clock;
        case 'Partially Delivered':
            return BarChart3;
        case 'Cancelled':
            return XCircle;
        default:
            return Clock;
    }
};

const goToPrint = (aroId) => {
    // Simple navigation to test if route works
    window.location.href = `/aro/${aroId}/print`;
};
</script>

<template>
    <Layout>
        <!-- Statistics Cards -->
        <div class="grid gap-6 md:grid-cols-4 mb-6">
           

            <!-- Delivered AROs -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-green-600">
                        <CheckCircle class="w-5 h-5 mr-2" />
                        Delivered
                    </CardTitle>
                    <CardDescription>Completed deliveries</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-green-600">{{ deliveredAROs }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Successfully delivered</div>
                </CardContent>
            </Card>

            <!-- Pending AROs -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-yellow-600">
                        <Clock class="w-5 h-5 mr-2" />
                        Pending
                    </CardTitle>
                    <CardDescription>Awaiting delivery</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-yellow-600">{{ pendingAROs }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Need processing</div>
                </CardContent>
            </Card>

            <!-- Partially Delivered AROs -->
            <Card class="border-blue-500 border-2 bg-blue-50">
                <CardHeader>
                    <CardTitle class="flex items-center text-blue-600">
                        <BarChart3 class="w-5 h-5 mr-2" />
                        Partial
                    </CardTitle>
                    <CardDescription>Partially delivered</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-blue-600">{{ partiallyDeliveredAROs }}</div>
                    <div class="text-sm text-blue-700 mt-1">In progress</div>
                </CardContent>
            </Card>

            <!-- Cancelled AROs -->
            <Card class="border-red-500 border-2 bg-red-50">
                <CardHeader>
                    <CardTitle class="flex items-center text-red-600">
                        <XCircle class="w-5 h-5 mr-2" />
                        Cancelled
                    </CardTitle>
                    <CardDescription>Cancelled deliveries</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-red-600">{{ cancelledAROs }}</div>
                    <div class="text-sm text-red-700 mt-1">Require attention</div>
                </CardContent>
            </Card>
        </div>

        <!-- AROs Table -->
        <Card>
            <CardHeader>
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle>ARO Management</CardTitle>
                        <CardDescription>Acknowledgment of Receipt of Orders</CardDescription>
                    </div>
                    <Link href="/aro/create" preserve-scroll>
                        <Button class="bg-green-600 hover:bg-green-700 text-white">
                            <Plus class="w-4 h-4 mr-2" />
                            Create ARO
                        </Button>
                    </Link>
                </div>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto h-96 overflow-y-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button @click="handleSort('aro_number')" class="font-semibold flex items-center gap-2">
                                        <FileCheck class="w-4 h-4 text-blue-600" />
                                        ARO Number
                                        <span v-if="sortField === 'aro_number'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <ClipboardList class="w-4 h-4 text-purple-600" />
                                        Purchase Order
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <Building2 class="w-4 h-4 text-orange-600" />
                                        Customer
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('date_aro')" class="font-semibold flex items-center gap-2">
                                        <Calendar class="w-4 h-4 text-green-600" />
                                        ARO Date
                                        <span v-if="sortField === 'date_aro'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('planned_delivery_date')" class="font-semibold flex items-center gap-2">
                                        <Calendar class="w-4 h-4 text-blue-600" />
                                        Planned Delivery
                                        <span v-if="sortField === 'planned_delivery_date'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('actual_delivery_date')" class="font-semibold flex items-center gap-2">
                                        <CheckCircle class="w-4 h-4 text-green-600" />
                                        Actual Delivery
                                        <span v-if="sortField === 'actual_delivery_date'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('status')" class="font-semibold flex items-center gap-2">
                                        <TrendingUp class="w-4 h-4 text-pink-600" />
                                        Status
                                        <span v-if="sortField === 'status'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('user')" class="font-semibold flex items-center gap-2">
                                        <User class="w-4 h-4 text-indigo-600" />
                                        Created By
                                        <span v-if="sortField === 'user'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold">Actions</div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="aro in paginatedData" :key="aro.id_aro">
                                <TableCell>
                                    <div class="flex items-center">
                                        <FileCheck class="w-4 h-4 mr-2 text-blue-400" />
                                        <span class="font-mono text-sm font-semibold">{{ aro.aro_number }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <ClipboardList class="w-4 h-4 mr-2 text-purple-400" />
                                        <div>
                                            <div class="font-semibold">{{ aro.purchase_order?.po_number || 'No PO' }}</div>
                                            <div class="text-sm text-gray-500">{{ aro.purchase_order?.vat_number || 'No VAT' }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Building2 class="w-4 h-4 mr-2 text-orange-400" />
                                        <div>
                                            <div class="font-semibold">{{ aro.purchase_order?.customer?.company_name || 'Unknown Customer' }}</div>
                                            <div class="text-sm text-gray-500">{{ aro.purchase_order?.customer?.contact_name || 'No contact' }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Calendar class="w-4 h-4 mr-2 text-green-400" />
                                        {{ aro.date_aro ? new Date(aro.date_aro).toLocaleDateString("en-US") : 'N/A' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Calendar class="w-4 h-4 mr-2 text-blue-400" />
                                        {{ aro.planned_delivery_date ? new Date(aro.planned_delivery_date).toLocaleDateString("en-US") : 'Not set' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <CheckCircle class="w-4 h-4 mr-2 text-green-400" />
                                        {{ aro.actual_delivery_date ? new Date(aro.actual_delivery_date).toLocaleDateString("en-US") : 'Pending' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadgeClass(aro.status)">
                                        <component :is="getStatusIcon(aro.status)" class="w-3 h-3 mr-1" />
                                        {{ aro.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <User class="w-4 h-4 mr-2 text-indigo-400" />
                                        {{ aro.creator?.name || 'Unknown User' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <!-- 1️⃣ EDIT Button (green with Edit icon) -->
                                        <Link v-if="aro.id_aro" :href="`/aro/${aro.id_aro}/edit`">
                                            <Button variant="outline" size="sm" class="text-green-600 hover:text-green-700 hover:bg-green-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                                        <!-- 2️⃣ PRINT Button (purple with Printer icon) -->
                                        <button v-if="aro.id_aro" @click="goToPrint(aro.id_aro)" class="inline-block">
                                            <Button variant="outline" size="sm" class="text-purple-600 hover:text-purple-700 hover:bg-purple-50">
                                                <Printer class="w-4 h-4" />
                                            </Button>
                                        </button>
                                        
                                        <!-- 3️⃣ DELETE Button (red with Trash icon) -->
                                        <Button 
                                            v-if="aro.id_aro"
                                            variant="outline" 
                                            size="sm" 
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                            @click="deleteARO(aro.id_aro)"
                                            :disabled="deleteForm.processing"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                
                <!-- Pagination -->
                <div class="flex items-center justify-between space-x-2 py-4">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
                        {{ Math.min(currentPage * itemsPerPage, getDataArray.length) }}
                        of {{ totalAROs }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                        >
                            Previous
                        </button>
                        <div class="flex items-center space-x-1">
                            <button
                                v-for="page in totalPages"
                                :key="page"
                                class="px-3 py-2 text-sm font-medium rounded-md"
                                :class="currentPage === page 
                                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                    : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'"
                                @click="currentPage = page"
                            >
                                {{ page }}
                            </button>
                        </div>
                        <button
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            @click="currentPage++"
                            :disabled="currentPage === totalPages"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </Layout>
</template>