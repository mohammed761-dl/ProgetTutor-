<script setup>
/**
 * Quote Index Page
 * Display only - shows list of quotes with search and filtering
 * No calculations performed in frontend
 */
import Layout from "../../Layout/App.vue";
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
    CalendarDays, 
    TrendingUp, 
    DollarSign, 
    ShoppingCart,
    CheckCircle,
    Clock,
    AlertTriangle,
    BarChart3,
    HourglassIcon,
    AlertCircle,
    Printer, // ✅ Printer icon for individual quotes
    Search,
    X
} from "lucide-vue-next";
import Input from "@/components/ui/input/Input.vue";

const props = defineProps({
    quotes: {
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
const selectedPOStatus = ref("");

const filteredAndSearchedData = computed(() => {
    let filtered = props.quotes;
    
    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(quote => 
            quote.quote_number?.toLowerCase().includes(query) ||
            quote.customer?.company_name?.toLowerCase().includes(query) ||
            quote.customer?.contact_name?.toLowerCase().includes(query) ||
            quote.user?.name?.toLowerCase().includes(query) ||
            quote.currency?.toLowerCase().includes(query) ||
            quote.total_amount?.toString().includes(query) ||
            quote.total_ttc?.toString().includes(query)
        );
    }
    
    // Status filter
    if (selectedStatus.value) {
        filtered = filtered.filter(quote => quote.status === selectedStatus.value);
    }
    
    // PO Status filter
    if (selectedPOStatus.value) {
        const hasPO = selectedPOStatus.value === "has_po";
        filtered = filtered.filter(quote => quote.has_po === hasPO);
    }
    
    return filtered;
});

function handleSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
}

const sortedData = computed(() => {
    if (!sortField.value) return filteredAndSearchedData.value;
    return [...filteredAndSearchedData.value].sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];
        
        // Handle date sorting
        if (sortField.value === "date_quote" || sortField.value === "valid_until") {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        }
        
        // Handle numeric sorting
        if (sortField.value === "total_amount" || sortField.value === "total_ttc") {
            aValue = Number(aValue) || 0;
            bValue = Number(bValue) || 0;
        }
        
        if (aValue < bValue) return sortDirection.value === "asc" ? -1 : 1;
        if (aValue > bValue) return sortDirection.value === "asc" ? 1 : -1;
        return 0;
    });
});

const paginatedData = computed(() =>
    sortedData.value.slice(
        (currentPage.value - 1) * itemsPerPage,
        currentPage.value * itemsPerPage
    )
);

const totalPages = computed(() => Math.ceil(sortedData.value.length / itemsPerPage));

const clearSearch = () => {
    searchQuery.value = "";
    selectedStatus.value = "";
    selectedPOStatus.value = "";
    currentPage.value = 1;
};

const validStatuses = [
    "Sent same day",
    "Sent within 2-3 days", 
    "Sent after 4+ days"
];

const today = new Date();
const totalQuotesCount = props.quotes.length;
const monthlyTrend = "+12%";
const pendingPO = computed(() =>
    props.quotes.filter((q) => q.has_po === false)
);
const delayedQuotes = computed(() =>
    props.quotes.filter(
        (q) => q.status === "Sent after 4+ days"
    )
);

const deleteForm = useForm({});

const deleteQuote = (quoteId) => {
    if (!quoteId) return;
    
    if (confirm('Are you sure you want to delete this quote?')) {
        deleteForm.delete(`/Quote/${quoteId}`, {
            onSuccess: () => console.log('Quote deleted successfully'),
            onError: (errors) => console.error('Error deleting quote:', errors)
        });
    }
};

const printQuote = (quoteId) => {
    window.open(`/Quote/${quoteId}/print`, '_blank');
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Sent same day':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'Sent within 2-3 days':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        case 'Sent after 4+ days':
            return 'bg-red-100 text-red-800 hover:bg-red-200';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusIcon = (status) => {
    switch (status) {
        case 'Sent same day':
            return CheckCircle;
        case 'Sent within 2-3 days':
            return Clock;
        case 'Sent after 4+ days':
            return AlertTriangle;
        default:
            return Clock;
    }
};

const getPOStatusBadgeClass = (hasPO) => {
    return hasPO 
        ? 'bg-green-100 text-green-800 hover:bg-green-200' 
        : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
};

const getPOStatusIcon = (hasPO) => {
    return hasPO ? CheckCircle : Clock;
};

const getCurrencyColor = (currency) => {
    switch (currency) {
        case 'EUR': return 'text-blue-600';
        case 'USD': return 'text-green-600';
        case 'MAD': return 'text-purple-600';
        default: return 'text-gray-600';
    }
};
</script>

<template>
    <Layout>
        <div class="grid gap-6 md:grid-cols-3 mb-0">
            <!-- ✅ Total Quotes Created Card with proper icon -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center text-blue-600">
                            <BarChart3 class="w-5 h-5 mr-2" />
                            Total Quotes Created
                        </CardTitle>
                        <CardDescription>Total number of quotes in the system</CardDescription>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-blue-600">
                        {{ totalQuotesCount }}
                    </div>
                    <div class="text-sm text-green-600 mt-1">
                        {{ monthlyTrend }} this month
                    </div>
                </CardContent>
            </Card>
            
            <!-- ✅ Pending PO Card with proper icon -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-yellow-600">
                        <HourglassIcon class="w-5 h-5 mr-2" />
                        Pending PO
                    </CardTitle>
                    <CardDescription>Quotes waiting for customer purchase order</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-yellow-600">{{ pendingPO.length }}</div>
                    <div class="text-sm text-muted-foreground mt-1">
                        <span v-if="pendingPO.length">
                            Most recent: {{ pendingPO[0]?.customer?.company_name || "-" }} 
                            ({{ pendingPO[0]?.date_quote ? new Date(pendingPO[0].date_quote).toLocaleDateString("en-US") : "-" }})
                        </span>
                        <span v-else>None pending</span>
                    </div>
                </CardContent>
            </Card>
            
            <!-- ✅ Delayed Quotes Card with proper icon -->
            <Card class="border-red-500 border-2 bg-red-50">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center text-red-600">
                            <AlertCircle class="w-5 h-5 mr-2" />
                            Delayed Quotes
                        </CardTitle>
                        <CardDescription>Quotes sent after 4+ days</CardDescription>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold text-red-600">
                        {{ delayedQuotes.length }}
                    </div>
                    <div class="text-sm text-red-700 mt-1" v-if="delayedQuotes.length">
                        Latest: {{ delayedQuotes[0]?.customer?.company_name || "-" }} 
                        ({{ delayedQuotes[0]?.date_quote ? new Date(delayedQuotes[0].date_quote).toLocaleDateString("en-US") : "-" }})
                    </div>
                    <div class="text-sm text-muted-foreground mt-1" v-else>
                        No delayed quotes
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Recent Activity Table -->
        <Card class="mt-0">
            <CardHeader>
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle>All Quotes</CardTitle>
                        <CardDescription>Complete list of all quotes and their status</CardDescription>
                    </div>
                    <!-- ✅ Only Add New Quote button -->
                    <Link href="/Quote/create">
                        <Button class="bg-green-600 hover:bg-green-700 text-white">
                            <Plus class="w-4 h-4 mr-2" />
                            Add New Quote
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
                                placeholder="Search quotes by number, customer, user, amount..."
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
                            <option value="">All Delivery Status</option>
                            <option v-for="status in validStatuses" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>
                    </div>
                    
                    <!-- PO Status Filter -->
                    <div class="min-w-[140px]">
                        <select
                            v-model="selectedPOStatus"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All PO Status</option>
                            <option value="has_po">Accepted (Has PO)</option>
                            <option value="pending">Pending (No PO)</option>
                        </select>
                    </div>
                    
                    <!-- Clear Filters Button -->
                    <Button
                        v-if="searchQuery || selectedStatus || selectedPOStatus"
                        @click="clearSearch"
                        variant="outline"
                        class="px-4"
                    >
                        <X class="w-4 h-4 mr-2" />
                        Clear
                    </Button>
                </div>
                
                <!-- ✅ Results Count -->
                <div v-if="searchQuery || selectedStatus || selectedPOStatus" class="mt-2 text-sm text-gray-600">
                    Showing {{ paginatedData.length }} of {{ sortedData.length }} quotes
                    <span v-if="sortedData.length !== totalQuotesCount">
                        (filtered from {{ totalQuotesCount }} total)
                    </span>
                </div>
                
                <!-- ✅ Results Count -->
                <div class="mt-2 text-sm text-gray-600">
                    Showing {{ sortedData.length }} of {{ props.quotes.length }} quotes
                    <span v-if="searchQuery || selectedStatus || selectedPOStatus" class="text-blue-600">
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
                                    <button
                                        @click="handleSort('quote_number')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        <FileText class="w-4 h-4 text-blue-600" />
                                        Quote Number
                                        <span v-if="sortField === 'quote_number'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('customer')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        <Building2 class="w-4 h-4 text-purple-600" />
                                        Customer
                                        <span v-if="sortField === 'customer'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('user')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        <User class="w-4 h-4 text-indigo-600" />
                                        Created By
                                        <span v-if="sortField === 'user'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('date_quote')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        <Calendar class="w-4 h-4 text-green-600" />
                                        Quote Date
                                        <span v-if="sortField === 'date_quote'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('valid_until')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        <CalendarDays class="w-4 h-4 text-orange-600" />
                                        Valid Until
                                        <span v-if="sortField === 'valid_until'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('status')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        <TrendingUp class="w-4 h-4 text-pink-600" />
                                        Status
                                        <span v-if="sortField === 'status'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button
                                        @click="handleSort('total_ttc')"
                                        class="font-semibold flex items-center gap-2"
                                    >
                                        <DollarSign class="w-4 h-4 text-emerald-600" />
                                        Total TTC
                                        <span v-if="sortField === 'total_ttc'">{{
                                            sortDirection === "asc" ? "▲" : "▼"
                                        }}</span>
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold flex items-center gap-2">
                                        <ShoppingCart class="w-4 h-4 text-cyan-600" />
                                        PO Status
                                    </div>
                                </TableHead>
                                <TableHead>
                                    <div class="font-semibold">Actions</div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="quote in paginatedData"
                                :key="quote.id_quote"
                            >
                                <TableCell>
                                    <div class="flex items-center">
                                        <FileText class="w-4 h-4 mr-2 text-blue-400" />
                                        <span class="font-mono text-sm">{{ quote.quote_number }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Building2 class="w-4 h-4 mr-2 text-purple-400" />
                                        <div>
                                            <div class="font-semibold">
                                                {{ quote.customerSnapshot?.company_name || quote.customer?.company_name || 'Unknown Customer' }}
                                                <span v-if="quote.customerSnapshot && quote.customer?.company_name !== quote.customerSnapshot.company_name" 
                                                      class="text-xs text-blue-600 ml-2" 
                                                      title="Customer details have changed since quote creation">
                                                    (Historical)
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ quote.customerSnapshot?.contact_name || quote.customer?.contact_name || 'No contact' }}
                                            </div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <User class="w-4 h-4 mr-2 text-indigo-400" />
                                        {{ quote.user?.name || 'Unknown User' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <Calendar class="w-4 h-4 mr-2 text-green-400" />
                                        {{
                                            quote.date_quote ? new Date(quote.date_quote).toLocaleDateString("en-US") : 'N/A'
                                        }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <CalendarDays class="w-4 h-4 mr-2 text-orange-400" />
                                        <span :class="{ 'text-red-600 font-semibold': new Date(quote.valid_until) < new Date() }">
                                            {{
                                                quote.valid_until ? new Date(quote.valid_until).toLocaleDateString("en-US") : 'N/A'
                                            }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadgeClass(quote.status)">
                                        <component :is="getStatusIcon(quote.status)" class="w-3 h-3 mr-1" />
                                        {{ quote.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <DollarSign class="w-4 h-4 mr-2 text-emerald-400" />
                                        <div>
                                            <div :class="getCurrencyColor(quote.currency)" class="font-medium">
                                                {{ quote.total_ttc ? `${quote.total_ttc} ${quote.currency}` : (quote.total_amount ? `${quote.total_amount} ${quote.currency}` : 'N/A') }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <template v-if="quote.total_amount">
                                                    HT: {{ quote.total_amount }} {{ quote.currency }}
                                                </template>
                                                <div v-if="quote.vat_rate !== undefined && quote.vat_rate !== null" class="mt-1">
                                                    <span class="text-purple-600">VAT: {{ (quote.vat_rate * 100).toFixed(0) }}%</span>
                                                </div>
                                                <div v-if="quote.quoteProducts?.length" class="mt-1">
                                                    <span class="text-blue-600">{{ quote.quoteProducts.length }}</span> products
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <ShoppingCart class="w-4 h-4 mr-2 text-cyan-400" />
                                        <!-- ✅ Updated PO Status with colors and icons -->
                                        <Badge :class="getPOStatusBadgeClass(quote.has_po)">
                                            <component :is="getPOStatusIcon(quote.has_po)" class="w-3 h-3 mr-1" />
                                            {{ quote.has_po ? 'Has PO' : 'Pending' }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <!-- ✅ Actions column with 4 buttons: View, Print, Edit, Delete -->
                                <TableCell>
                                    <div class="flex items-center space-x-2">
                                        <!-- 1️⃣ EDIT Button (green with Edit icon) -->
                                        <Link 
                                            v-if="quote.id_quote" 
                                            :href="`/Quote/${quote.id_quote}/edit`"
                                        >
                                            <Button variant="outline" size="sm" class="text-green-600 hover:text-green-700 hover:bg-green-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                                        <!-- 2️⃣ PRINT Button (purple with Printer icon) -->
                                        <Button 
                                            v-if="quote.id_quote"
                                            variant="outline" 
                                            size="sm" 
                                            class="text-purple-600 hover:text-purple-700 hover:bg-purple-50"
                                            @click="printQuote(quote.id_quote)"
                                        >
                                            <Printer class="w-4 h-4" />
                                        </Button>
                                        
                                        <!-- 3️⃣ DELETE Button (red with Trash icon) -->
                                        <Button 
                                            v-if="quote.id_quote"
                                            variant="outline" 
                                            size="sm" 
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                            @click="deleteQuote(quote.id_quote)"
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
                        Showing
                        {{ (currentPage - 1) * itemsPerPage + 1 }} to
                        {{
                            Math.min(
                                currentPage * itemsPerPage,
                                sortedData.length
                            )
                        }}
                        of {{ sortedData.length }} results
                        <span v-if="searchQuery || selectedStatus || selectedPOStatus" class="text-blue-600">
                            (filtered from {{ props.quotes.length }} total)
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
                                :class="
                                    currentPage === page
                                        ? 'btn-primary'
                                        : 'btn-outline'
                                "
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