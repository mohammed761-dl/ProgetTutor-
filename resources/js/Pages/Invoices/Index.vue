<script setup>
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
    FileText, 
    Building2, 
    User, 
    Calendar, 
    CreditCard,
    DollarSign,
    Receipt,
    CheckCircle,
    Clock,
    AlertTriangle,
    XCircle,
    BarChart3,
    Truck,
    Search,
    X,
    Printer
} from "lucide-vue-next";
import Input from "@/components/ui/input/Input.vue";

const props = defineProps({
    invoices: {
        type: Array,
        default: () => []
    }
});

const itemsPerPage = 10;
const currentPage = ref(1);
const sortField = ref(null);
const sortDirection = ref("asc");
const searchQuery = ref("");
const selectedStatus = ref("");
const selectedPaymentStatus = ref("");

// ✅ Filter and search function
const filteredAndSearchedData = computed(() => {
    let filtered = props.invoices;
    
    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(invoice => 
            invoice.invoice_number?.toLowerCase().includes(query) ||
            invoice.customer?.company_name?.toLowerCase().includes(query) ||
            invoice.customer?.contact_name?.toLowerCase().includes(query) ||
            invoice.user?.name?.toLowerCase().includes(query) ||
            invoice.currency?.toLowerCase().includes(query) ||
            invoice.total_amount?.toString().includes(query) ||
            invoice.total_incl_vat?.toString().includes(query) ||
            invoice.payment_terms?.toLowerCase().includes(query)
        );
    }
    
    // Status filter
    if (selectedStatus.value) {
        filtered = filtered.filter(invoice => invoice.status === selectedStatus.value);
    }
    
    // Payment Status filter
    if (selectedPaymentStatus.value) {
        if (selectedPaymentStatus.value === "overdue") {
            filtered = filtered.filter(invoice => invoice.is_overdue);
        } else if (selectedPaymentStatus.value === "not_overdue") {
            filtered = filtered.filter(invoice => !invoice.is_overdue);
        }
    }
    
    return filtered;
});

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
    if (!sortField.value) return filteredAndSearchedData.value;
    return [...filteredAndSearchedData.value].sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];
        
        if (sortField.value === "issue_date" || sortField.value === "due_date") {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        }
        
        // Handle numeric sorting
        if (sortField.value === "total_amount" || sortField.value === "total_incl_vat") {
            aValue = Number(aValue) || 0;
            bValue = Number(bValue) || 0;
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
const totalPages = computed(() => Math.ceil(sortedData.value.length / itemsPerPage));

// ✅ Clear search function
const clearSearch = () => {
    searchQuery.value = "";
    selectedStatus.value = "";
    selectedPaymentStatus.value = "";
    currentPage.value = 1;
};

// ✅ Valid invoice statuses only
const validInvoiceStatuses = [
    "Draft",
    "Unpaid", 
    "Partially Paid",
    "Paid",
    "Overdue",
    "Cancelled"
];

// Statistics
const totalInvoices = props.invoices.length;
const draftInvoices = computed(() => 
    props.invoices.filter(inv => inv.status === 'Draft').length
);
const unpaidInvoices = computed(() => 
    props.invoices.filter(inv => inv.status === 'Unpaid').length
);
const paidInvoices = computed(() => 
    props.invoices.filter(inv => inv.status === 'Paid').length
);
const overdueInvoices = computed(() => 
    props.invoices.filter(inv => inv.is_overdue).length
);

// Total amounts
const totalRevenue = computed(() => 
    props.invoices.reduce((sum, inv) => sum + (parseFloat(inv.grand_total) || 0), 0)
);

const deleteForm = useForm({});

const deleteInvoice = (invoiceId) => {
    if (!invoiceId) return;
    
    if (confirm('Are you sure you want to delete this Invoice?')) {
        deleteForm.delete(`/invoices/${invoiceId}`, {
            onSuccess: () => console.log('Invoice deleted successfully'),
            onError: (errors) => console.error('Error deleting Invoice:', errors)
        });
    }
};

// ✅ Status badge colors
const getStatusBadgeClass = (status, isOverdue = false) => {
    if (isOverdue) {
        return 'bg-red-100 text-red-800 hover:bg-red-200';
    }
    
    switch (status) {
        case 'Draft':
            return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
        case 'Unpaid':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        case 'Partially Paid':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
        case 'Paid':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'Cancelled':
            return 'bg-red-100 text-red-800 hover:bg-red-200';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// ✅ Status icon
const getStatusIcon = (status, isOverdue = false) => {
    if (isOverdue) {
        return AlertTriangle;
    }
    
    switch (status) {
        case 'Draft':
            return FileText;
        case 'Unpaid':
            return Clock;
        case 'Partially Paid':
            return CreditCard;
        case 'Paid':
            return CheckCircle;
        case 'Cancelled':
            return XCircle;
        default:
            return FileText;
    }
};

// Format currency
const formatCurrency = (amount, currency = 'MAD') => {
    if (!amount) return '0.00 ' + currency;
    return parseFloat(amount).toFixed(2) + ' ' + currency;
};
</script>

<template>
    <Layout>
        <!-- Statistics Cards -->
        <div class="grid gap-6 md:grid-cols-4 mb-6">
            <!-- Total Invoices Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center text-blue-600">
                            <BarChart3 class="w-5 h-5 mr-2" />
                            Total Invoices
                        </CardTitle>
                        <CardDescription>All invoices in system</CardDescription>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-blue-600">{{ totalInvoices }}</div>
                    <div class="text-sm text-muted-foreground mt-1">{{ draftInvoices }} drafts</div>
                </CardContent>
            </Card>

            <!-- Total Revenue -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-green-600">
                        <DollarSign class="w-5 h-5 mr-2" />
                        Total Revenue
                    </CardTitle>
                    <CardDescription>Total invoiced amount</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-green-600">{{ formatCurrency(totalRevenue) }}</div>
                    <div class="text-sm text-muted-foreground mt-1">{{ paidInvoices }} paid invoices</div>
                </CardContent>
            </Card>

            <!-- Unpaid Invoices -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-yellow-600">
                        <Clock class="w-5 h-5 mr-2" />
                        Unpaid
                    </CardTitle>
                    <CardDescription>Awaiting payment</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-yellow-600">{{ unpaidInvoices }}</div>
                    <div class="text-sm text-muted-foreground mt-1">Need follow-up</div>
                </CardContent>
            </Card>

            <!-- Overdue Invoices -->
            <Card class="border-red-500 border-2 bg-red-50">
                <CardHeader>
                    <CardTitle class="flex items-center text-red-600">
                        <AlertTriangle class="w-5 h-5 mr-2" />
                        Overdue
                    </CardTitle>
                    <CardDescription>Past due date</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-red-600">{{ overdueInvoices }}</div>
                    <div class="text-sm text-red-700 mt-1">Urgent attention</div>
                </CardContent>
            </Card>
        </div>

        <!-- Invoices Table -->
        <Card>
            <CardHeader>
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle>Invoice Management</CardTitle>
                        <CardDescription>Manage customer invoices and payments</CardDescription>
                    </div>
                    <Link href="/invoices/create">
                        <Button class="bg-green-600 hover:bg-green-700 text-white">
                            <Plus class="w-4 h-4 mr-2" />
                            Create Invoice
                        </Button>
                    </Link>
                </div>
                
                <!-- ✅ Search and Filter Section -->
                <div class="flex flex-col sm:flex-row gap-4 mt-4 p-4 bg-gray-50 rounded-lg">
                    <!-- Search Input -->
                    <div class="flex-1">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search invoices by number, customer, amount..."
                                class="pl-10 pr-10"
                            />
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="min-w-[160px]">
                        <select
                            v-model="selectedStatus"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Status</option>
                            <option v-for="status in validInvoiceStatuses" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>
                    </div>
                    
                    <!-- Payment Status Filter -->
                    <div class="min-w-[140px]">
                        <select
                            v-model="selectedPaymentStatus"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Payment Status</option>
                            <option value="overdue">Overdue</option>
                            <option value="not_overdue">Not Overdue</option>
                        </select>
                    </div>
                    
                    <!-- Clear Filters Button -->
                    <Button
                        v-if="searchQuery || selectedStatus || selectedPaymentStatus"
                        @click="clearSearch"
                        variant="outline"
                        class="px-4"
                    >
                        <X class="w-4 h-4 mr-2" />
                        Clear
                    </Button>
                </div>
                
                <!-- ✅ Results Count -->
                <div v-if="searchQuery || selectedStatus || selectedPaymentStatus" class="mt-2 text-sm text-gray-600">
                    Showing {{ paginatedData.length }} of {{ sortedData.length }} invoices
                    <span v-if="sortedData.length !== totalInvoices">
                        (filtered from {{ totalInvoices }} total)
                    </span>
                </div>
                
                <!-- ✅ Results Count -->
                <div class="mt-2 text-sm text-gray-600">
                    Showing {{ sortedData.length }} of {{ props.invoices.length }} invoices
                    <span v-if="searchQuery || selectedStatus || selectedPaymentStatus" class="text-blue-600">
                        (filtered)
                    </span>
                </div>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto h-96 overflow-y-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button @click="handleSort('invoice_number')" class="font-semibold flex items-center gap-2">
                                        <Receipt class="w-4 h-4 text-blue-600" />
                                        Invoice #
                                        <span v-if="sortField === 'invoice_number'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <Truck class="w-4 h-4 text-purple-600" />
                                        Delivery Note
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <Building2 class="w-4 h-4 text-orange-600" />
                                        Customer
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('issue_date')" class="font-semibold flex items-center gap-2">
                                        <Calendar class="w-4 h-4 text-green-600" />
                                        Issue / Due Date
                                        <span v-if="sortField === 'issue_date'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <DollarSign class="w-4 h-4 text-emerald-600" />
                                        Grand Total
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <button @click="handleSort('status')" class="font-semibold flex items-center gap-2">
                                        <CreditCard class="w-4 h-4 text-pink-600" />
                                        Status
                                        <span v-if="sortField === 'status'">{{ sortDirection === "asc" ? "▲" : "▼" }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold">Actions</div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="invoice in paginatedData" :key="invoice.id_invoice">
                                <TableCell>
                                    <div class="flex items-center">
                                        <Receipt class="w-4 h-4 mr-2 text-blue-400" />
                                        <span class="font-mono text-sm font-semibold">{{ invoice.invoice_number }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Truck class="w-4 h-4 mr-2 text-purple-400" />
                                        <span class="font-semibold">{{ invoice.delivery_note_number }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Building2 class="w-4 h-4 mr-2 text-orange-400" />
                                        <div>
                                            <div class="font-semibold">{{ invoice.customer_name }}</div>
                                            <div class="text-sm text-gray-500">{{ invoice.currency }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Calendar class="w-4 h-4 mr-2 text-green-400" />
                                        <div>
                                            <div class="font-semibold">{{ new Date(invoice.issue_date).toLocaleDateString() }}</div>
                                            <div class="text-sm" :class="invoice.is_overdue ? 'text-red-600 font-semibold' : 'text-gray-500'">
                                                Due: {{ new Date(invoice.due_date).toLocaleDateString() }}
                                            </div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <DollarSign class="w-4 h-4 mr-2 text-emerald-400" />
                                        <div>
                                            <div class="font-semibold text-emerald-600">{{ formatCurrency(invoice.grand_total, invoice.currency) }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadgeClass(invoice.status, invoice.is_overdue)">
                                        <component :is="getStatusIcon(invoice.status, invoice.is_overdue)" class="w-3 h-3 mr-1" />
                                        {{ invoice.is_overdue ? 'Overdue' : invoice.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <!-- Edit Button -->
                                        <Link v-if="invoice.id_invoice" :href="`/invoices/${invoice.id_invoice}/edit`">
                                            <Button variant="outline" size="sm" class="bg-green-600 hover:bg-green-700 text-white">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>

                                        <!-- Print Button -->
                                        <Link v-if="invoice.id_invoice" :href="`/invoices/${invoice.id_invoice}/download-pdf`" target="_blank">
                                            <Button variant="outline" size="sm" class="bg-purple-600 hover:bg-purple-700 text-white">
                                                <Printer class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                                        <!-- Delete Button -->
                                        <Button 
                                            v-if="invoice.id_invoice && (invoice.status === 'Draft' || invoice.status === 'Cancelled')"
                                            variant="outline" 
                                            size="sm" 
                                            class="bg-red-600 hover:bg-red-700 text-white"
                                            @click="deleteInvoice(invoice.id_invoice)"
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
                        {{ Math.min(currentPage * itemsPerPage, sortedData.length) }}
                        of {{ sortedData.length }} results
                        <span v-if="searchQuery || selectedStatus || selectedPaymentStatus" class="text-blue-600">
                            (filtered from {{ props.invoices.length }} total)
                        </span>
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