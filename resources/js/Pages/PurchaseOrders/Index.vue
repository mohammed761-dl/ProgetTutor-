<script setup>
// filepath: resources/js/Pages/PurchaseOrders/Index.vue

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
    FileText, 
    Building2, 
    User, 
    Calendar, 
    CheckCircle, 
    Clock, 
    XCircle,
    BarChart3,
    AlertTriangle,
    TrendingUp,
    Printer
} from "lucide-vue-next";

const props = defineProps({
    purchaseOrders: {
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
    if (!sortField.value) return props.purchaseOrders;
    return [...props.purchaseOrders].sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];
        
        if (sortField.value === "created_at") {
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
const totalPages = computed(() => Math.ceil(props.purchaseOrders.length / itemsPerPage));

// Statistics
const totalPOs = props.purchaseOrders.length;
const monthlyTrend = "+15%";
const validatedPOs = computed(() => 
    props.purchaseOrders.filter(po => po.status === 'Validated').length
);
const pendingPOs = computed(() => 
    props.purchaseOrders.filter(po => po.status === 'Pending').length
);
const rejectedPOs = computed(() => 
    props.purchaseOrders.filter(po => po.status === 'Rejected').length
);

const deleteForm = useForm({});

const deletePO = (poId) => {
    if (!poId) return;
    
    if (confirm('Are you sure you want to delete this Purchase Order?')) {
        deleteForm.delete(`/po/${poId}`, {
            onSuccess: () => console.log('Purchase Order deleted successfully'),
            onError: (errors) => console.error('Error deleting Purchase Order:', errors)
        });
    }
};

// Download PO PDF function
const printPO = (po) => {
    if (!po.id_po) {
        alert('Invalid Purchase Order.');
        return;
    }

    // Use axios or fetch to download the file
    fetch(`/po/${po.id_po}/pdf`)
        .then(response => {
            if (!response.ok) {
                throw new Error('PDF not found');
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${po.po_number}.pdf`; // Use PO number as filename
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        })
        .catch(error => {
            console.error('Error downloading PDF:', error);
            alert('Error downloading PDF. Please try again.');
        });
};

// ✅ Status badge colors
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Validated':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        case 'Rejected':
            return 'bg-red-100 text-red-800 hover:bg-red-200';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// ✅ Status icon
const getStatusIcon = (status) => {
    switch (status) {
        case 'Validated':
            return CheckCircle;
        case 'Pending':
            return Clock;
        case 'Rejected':
            return XCircle;
        default:
            return Clock;
    }
};
</script>

<template>
    <Layout>
        <!-- Statistics Cards -->
        <div class="grid gap-6 md:grid-cols-4 mb-6">
            <!-- Total POs Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center text-blue-600">
                            <BarChart3 class="w-5 h-5 mr-2" />
                            Total POs
                        </CardTitle>
                        <CardDescription>Total Purchase Orders</CardDescription>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-blue-600">{{ totalPOs }}</div>
                    <div class="text-sm text-green-600 mt-1">{{ monthlyTrend }} this month</div>
                </CardContent>
            </Card>

            <!-- Validated POs -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-green-600">
                        <CheckCircle class="w-5 h-5 mr-2" />
                        Validated
                    </CardTitle>
                    <CardDescription>Approved Purchase Orders</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-green-600">{{ validatedPOs }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Ready for processing</div>
                </CardContent>
            </Card>

            <!-- Pending POs -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-yellow-600">
                        <Clock class="w-5 h-5 mr-2" />
                        Pending
                    </CardTitle>
                    <CardDescription>Awaiting approval</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-yellow-600">{{ pendingPOs }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Need review</div>
                </CardContent>
            </Card>

            <!-- Rejected POs -->
            <Card class="border-red-500 border-2 bg-red-50">
                <CardHeader>
                    <CardTitle class="flex items-center text-red-600">
                        <XCircle class="w-5 h-5 mr-2" />
                        Rejected
                    </CardTitle>
                    <CardDescription>Declined Purchase Orders</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-red-600">{{ rejectedPOs }}</div>
                    <div class="text-sm text-red-700 mt-1">Require attention</div>
                </CardContent>
            </Card>
        </div>

        <!-- Purchase Orders Table -->
        <Card>
            <CardHeader>
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle>Purchase Orders</CardTitle>
                        <CardDescription>Manage customer purchase orders</CardDescription>
                    </div>
                    <Link href="/po/create">
                        <Button class="bg-green-600 hover:bg-green-700 text-white">
                            <Plus class="w-4 h-4 mr-2" />
                            Create PO
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
                                    <button @click="handleSort('po_number')" class="font-semibold flex items-center gap-2">
                                        <FileText class="w-4 h-4 text-blue-600" />
                                        PO Number
                                        <span v-if="sortField === 'po_number'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('customer')" class="font-semibold flex items-center gap-2">
                                        <Building2 class="w-4 h-4 text-purple-600" />
                                        Customer
                                        <span v-if="sortField === 'customer'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('created_at')" class="font-semibold flex items-center gap-2">
                                        <Calendar class="w-4 h-4 text-green-600" />
                                        Date Created
                                        <span v-if="sortField === 'created_at'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
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
                            <TableRow v-for="po in paginatedData" :key="po.id_po">
                                <TableCell>
                                    <div class="flex items-center">
                                        <FileText class="w-4 h-4 mr-2 text-blue-400" />
                                        <span class="font-mono text-sm">{{ po.po_number }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Building2 class="w-4 h-4 mr-2 text-purple-400" />
                                        <div>
                                            <div class="font-semibold">{{ po.customer?.company_name || 'Unknown Customer' }}</div>
                                            <div class="text-sm text-gray-500">{{ po.customer?.contact_name || 'No contact' }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Calendar class="w-4 h-4 mr-2 text-green-400" />
                                        {{ po.created_at ? new Date(po.created_at).toLocaleDateString("en-US") : 'N/A' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadgeClass(po.status)">
                                        <component :is="getStatusIcon(po.status)" class="w-3 h-3 mr-1" />
                                        {{ po.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <User class="w-4 h-4 mr-2 text-indigo-400" />
                                        {{ po.creator?.name || 'Unknown User' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <!-- 1️⃣ EDIT Button (green with Edit icon) -->
                                        <Link v-if="po.id_po" :href="`/po/${po.id_po}/edit`">
                                            <Button variant="outline" size="sm" class="text-green-600 hover:text-green-700 hover:bg-green-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                        <!-- 2️⃣ PDF Button (purple with File icon) -->
                        <Button 
                            variant="outline" 
                            size="sm" 
                            class="text-purple-600 hover:text-purple-700 hover:bg-purple-50"
                            @click="printPO(po)"
                            :title="'Download Purchase Order PDF'"
                        >
                            <FileText class="w-4 h-4" />
                        </Button>                                        <!-- 3️⃣ DELETE Button (red with Trash icon) -->
                                        <Button 
                                            v-if="po.id_po"
                                            variant="outline" 
                                            size="sm" 
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                            @click="deletePO(po.id_po)"
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
                        {{ Math.min(currentPage * itemsPerPage, props.purchaseOrders.length) }}
                        of {{ props.purchaseOrders.length }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            class="btn btn-outline btn-sm"
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                        >
                            Previous
                        </button>
                        <div class="flex items-center space-x-1">
                            <button
                                v-for="page in totalPages"
                                :key="page"
                                class="btn btn-sm"
                                :class="currentPage === page ? 'btn-primary' : 'btn-outline'"
                                @click="currentPage = page"
                            >
                                {{ page }}
                            </button>
                        </div>
                        <button
                            class="btn btn-outline btn-sm"
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