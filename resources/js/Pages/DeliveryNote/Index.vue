<script setup>
// filepath: resources/js/Pages/DeliveryNote/Index.vue

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
import { ref, computed } from "vue";
import { 
    Trash2, 
    Edit, 
    Plus, 
    Eye, 
    Truck, 
    Building2, 
    Calendar, 
    CheckCircle, 
    Clock, 
    XCircle,
    BarChart3,
    FileCheck,
    MapPin,
    AlertTriangle,
    RotateCcw,
    Package,
    Printer
} from "lucide-vue-next";

const props = defineProps({
    deliveryNotes: {
        type: Array,
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

// ✅ Sorted data
const sortedData = computed(() => {
    if (!sortField.value) return props.deliveryNotes;
    return [...props.deliveryNotes].sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];
        
        if (sortField.value === "planned_delivery_date" || sortField.value === "actual_delivery_date") {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        }
        
        if (aValue < bValue) return sortDirection.value === "asc" ? -1 : 1;
        if (aValue > bValue) return sortDirection.value === "asc" ? 1 : -1;
        return 0;
    });
});

// ✅ Paginated data
const paginatedData = computed(() =>
    sortedData.value.slice(
        (currentPage.value - 1) * itemsPerPage,
        currentPage.value * itemsPerPage
    )
);

// ✅ Total pages
const totalPages = computed(() => Math.ceil(props.deliveryNotes.length / itemsPerPage));

// Statistics
const totalNotes = props.deliveryNotes.length;
const monthlyTrend = "+8%";
const pendingNotes = computed(() => 
    props.deliveryNotes.filter(note => note.status === 'Pending').length
);
const deliveredNotes = computed(() => 
    props.deliveryNotes.filter(note => note.status === 'Delivered').length
);
const partiallyDeliveredNotes = computed(() => 
    props.deliveryNotes.filter(note => note.status === 'Partially Delivered').length
);
const returnedNotes = computed(() => 
    props.deliveryNotes.filter(note => note.status === 'Returned').length
);
const cancelledNotes = computed(() => 
    props.deliveryNotes.filter(note => note.status === 'Cancelled').length
);

const deleteForm = useForm({});

const deleteNote = (noteId) => {
    if (!noteId) return;
    
    if (confirm('Are you sure you want to delete this Delivery Note?')) {
        deleteForm.delete(`/delivery-notes/${noteId}`, {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Delivery Note deleted successfully');
            },
            onError: (errors) => {
                console.error('Error deleting Delivery Note:', errors);
            }
        });
    }
};

// ✅ Status badge colors
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        case 'Delivered':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'Partially Delivered':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
        case 'Returned':
            return 'bg-orange-100 text-orange-800 hover:bg-orange-200';
        case 'Cancelled':
            return 'bg-red-100 text-red-800 hover:bg-red-200';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// ✅ Status icon
const getStatusIcon = (status) => {
    switch (status) {
        case 'Pending':
            return Clock;
        case 'Delivered':
            return CheckCircle;
        case 'Partially Delivered':
            return Package;
        case 'Returned':
            return RotateCcw;
        case 'Cancelled':
            return XCircle;
        default:
            return Clock;
    }
};
</script>

<template>
    <Layout>
        <!-- Statistics Cards -->
        <div class="grid gap-6 md:grid-cols-5 mb-6">
            <!-- Total Notes Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center text-blue-600">
                            <BarChart3 class="w-5 h-5 mr-2" />
                            Total Notes
                        </CardTitle>
                        <CardDescription>Total Delivery Notes</CardDescription>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-blue-600">{{ totalNotes }}</div>
                    <div class="text-sm text-green-600 mt-1">{{ monthlyTrend }} this month</div>
                </CardContent>
            </Card>

            <!-- Pending Notes -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-yellow-600">
                        <Clock class="w-5 h-5 mr-2" />
                        Pending
                    </CardTitle>
                    <CardDescription>Awaiting delivery</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-yellow-600">{{ pendingNotes }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Need processing</div>
                </CardContent>
            </Card>

            <!-- Delivered Notes -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-green-600">
                        <CheckCircle class="w-5 h-5 mr-2" />
                        Delivered
                    </CardTitle>
                    <CardDescription>Successfully delivered</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-green-600">{{ deliveredNotes }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Complete deliveries</div>
                </CardContent>
            </Card>

            <!-- Partially Delivered Notes -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-blue-600">
                        <Package class="w-5 h-5 mr-2" />
                        Partial
                    </CardTitle>
                    <CardDescription>Partially delivered</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-blue-600">{{ partiallyDeliveredNotes }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Incomplete deliveries</div>
                </CardContent>
            </Card>

            <!-- Returned/Cancelled Notes -->
            <Card class="border-red-500 border-2 bg-red-50">
                <CardHeader>
                    <CardTitle class="flex items-center text-red-600">
                        <XCircle class="w-5 h-5 mr-2" />
                        Issues
                    </CardTitle>
                    <CardDescription>Returned/Cancelled</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-red-600">{{ returnedNotes + cancelledNotes }}</div>
                    <div class="text-sm text-red-700 mt-1">Need attention</div>
                </CardContent>
            </Card>
        </div>

        <!-- Delivery Notes Table -->
        <Card>
            <CardHeader>
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle>Delivery Notes</CardTitle>
                        <CardDescription>Manage delivery notes and tracking</CardDescription>
                    </div>
                    <Link href="/delivery-notes/create">
                        <Button class="bg-green-600 hover:bg-green-700 text-white">
                            <Plus class="w-4 h-4 mr-2" />
                            Create Note
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
                                    <button @click="handleSort('dn_number')" class="font-semibold flex items-center gap-2">
                                        <Truck class="w-4 h-4 text-blue-600" />
                                        DN Number
                                        <span v-if="sortField === 'dn_number'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <FileCheck class="w-4 h-4 text-purple-600" />
                                        ARO Reference
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <Building2 class="w-4 h-4 text-orange-600" />
                                        Customer
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('planned_delivery_date')" class="font-semibold flex items-center gap-2">
                                        <Calendar class="w-4 h-4 text-green-600" />
                                        Planned Date
                                        <span v-if="sortField === 'planned_delivery_date'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('actual_delivery_date')" class="font-semibold flex items-center gap-2">
                                        <Calendar class="w-4 h-4 text-blue-600" />
                                        Actual Date
                                        <span v-if="sortField === 'actual_delivery_date'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('status')" class="font-semibold flex items-center gap-2">
                                        Status
                                        <span v-if="sortField === 'status'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold">Created By</div>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold">Actions</div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="note in paginatedData" :key="note.id_dn">
                                <TableCell>
                                    <div class="flex items-center">
                                        <Truck class="w-4 h-4 mr-2 text-blue-400" />
                                        <span class="font-mono text-sm font-semibold">{{ note.dn_number }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <FileCheck class="w-4 h-4 mr-2 text-purple-400" />
                                        <div>
                                            <div class="font-semibold">{{ note.aro_number || 'No ARO' }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Building2 class="w-4 h-4 mr-2 text-orange-400" />
                                        <div>
                                            <div class="font-semibold">{{ note.customer_name || 'Unknown Customer' }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Calendar class="w-4 h-4 mr-2 text-green-400" />
                                        {{ note.planned_delivery_date ? new Date(note.planned_delivery_date).toLocaleDateString("en-US") : 'N/A' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Calendar class="w-4 h-4 mr-2 text-blue-400" />
                                        {{ note.actual_delivery_date ? new Date(note.actual_delivery_date).toLocaleDateString("en-US") : 'Not delivered' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadgeClass(note.status)">
                                        <component :is="getStatusIcon(note.status)" class="w-3 h-3 mr-1" />
                                        {{ note.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">
                                        <div class="font-semibold">{{ note.created_by }}</div>
                                        <div class="text-gray-500">{{ note.created_at }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <!-- 1️⃣ EDIT Button (green with Edit icon) -->
                                        <Link v-if="note.id_dn" :href="`/delivery-notes/${note.id_dn}/edit`">
                                            <Button variant="outline" size="sm" class="text-green-600 hover:text-green-700 hover:bg-green-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                                        <!-- 2️⃣ PRINT Button (purple with Printer icon) -->
                                        <Link v-if="note.id_dn" :href="`/delivery-notes/${note.id_dn}/print`" target="_blank">
                                            <Button variant="outline" size="sm" class="text-purple-600 hover:text-purple-700 hover:bg-purple-50">
                                                <Printer class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                                        <!-- 3️⃣ DELETE Button (red with Trash icon) -->
                                        <Button 
                                            v-if="note.id_dn"
                                            variant="outline" 
                                            size="sm" 
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                            @click="deleteNote(note.id_dn)"
                                            :disabled="deleteForm.processing || note.status === 'Delivered'"
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
                        {{ Math.min(currentPage * itemsPerPage, props.deliveryNotes.length) }}
                        of {{ props.deliveryNotes.length }} results
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