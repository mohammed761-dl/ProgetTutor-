<script setup>
// filepath: resources/js/Pages/Products/Index.vue

import AdminApp from "../../Layout/AdminApp.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue"; // ✅ Add ref import here
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
import { Trash2, Edit, Plus, Package, CheckCircle, Archive, AlertTriangle } from "lucide-vue-next";

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    statuses: {
        type: Array,
        default: () => ['Active', 'EOL', 'Archived']
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
        })
    }
});

const isDevelopment = import.meta.env.DEV;

const validProducts = computed(() => {
    return (props.products || []).filter(product => 
        product && product.id !== null && typeof product === 'object'
    );
});

// ✅ ADD THESE PAGINATION VARIABLES HERE (after validProducts)
const itemsPerPage = 10;
const currentPage = ref(1);
const sortField = ref(null);
const sortDirection = ref("asc");

// ✅ ADD THESE FUNCTIONS HERE
function handleSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
}

const sortedData = computed(() => {
    if (!sortField.value) return validProducts.value;
    return [...validProducts.value].sort((a, b) => {
        let aValue = a[sortField.value];
        let bValue = b[sortField.value];
        
        if (sortField.value === "created_at") {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        }
        
        if (sortField.value === "min_delivery_day" || sortField.value === "max_delivery_day" || sortField.value === "availability_yrs" || sortField.value === "unit_price") {
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

const totalPages = computed(() => Math.ceil(validProducts.value.length / itemsPerPage));

const deleteForm = useForm({});

const deleteProduct = (productId) => {
    if (!productId) return;
    
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        deleteForm.delete(route('products.destroy', productId), {
            onSuccess: () => {
                console.log('Product deleted successfully');
                // Optionally refresh the page or update the list
                window.location.reload();
            },
            onError: (errors) => {
                console.error('Error deleting product:', errors);
                
                // Show user-friendly error message
                if (errors.error) {
                    alert(`Delete Failed: ${errors.error}`);
                } else {
                    alert('Failed to delete product. Please try again.');
                }
            }
        });
    }
};

// Get status badge class - Updated for your 3 statuses
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Active':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'EOL':
            return 'bg-orange-100 text-orange-800 hover:bg-orange-200';
        case 'Archived':
            return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Format delivery time
const formatDeliveryTime = (minDays, maxDays) => {
    if (minDays === maxDays) return `${minDays} days`;
    return `${minDays}-${maxDays} days`;
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

// ✅ Statistics for your 3 status types
const totalProducts = computed(() => validProducts.value.length);
const activeProducts = computed(() => 
    validProducts.value.filter(product => product.status === 'Active').length
);
const eolProducts = computed(() => 
    validProducts.value.filter(product => product.status === 'EOL').length
);
const archivedProducts = computed(() => 
    validProducts.value.filter(product => product.status === 'Archived').length
);
</script>

<template>
    <AdminApp>
        <div class="pt-6 space-y-8">
            <!-- Products Management Card -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle class="flex items-center">
                                <Package class="w-5 h-5 mr-2" />
                                Products Management
                            </CardTitle>
                            <CardDescription>
                                Manage your B2B product catalog and specifications
                            </CardDescription>
                        </div>
                        <Link :href="route('products.create')">
                            <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                                <Plus class="w-4 h-4 mr-2" />
                                Add New Product
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- ✅ REPLACE EVERYTHING FROM HERE -->
                    <div class="overflow-x-auto h-96 overflow-y-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        <button
                                            @click="handleSort('product_code')"
                                            class="font-semibold flex items-center gap-2"
                                        >
                                            Product Code
                                                                                        <span v-if="sortField === 'product_code'">
                                                {{ sortDirection === "asc" ? "▲" : "▼" }}
                                            </span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="handleSort('name')"
                                            class="font-semibold flex items-center gap-2"
                                        >
                                            Product Name
                                            <span v-if="sortField === 'name'">
                                                {{ sortDirection === "asc" ? "▲" : "▼" }}
                                            </span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="handleSort('status')"
                                            class="font-semibold flex items-center gap-2"
                                        >
                                            Status
                                            <span v-if="sortField === 'status'">
                                                {{ sortDirection === "asc" ? "▲" : "▼" }}
                                            </span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="handleSort('min_delivery_day')"
                                            class="font-semibold flex items-center gap-2"
                                        >
                                            Delivery Time
                                            <span v-if="sortField === 'min_delivery_day'">
                                                {{ sortDirection === "asc" ? "▲" : "▼" }}
                                            </span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="handleSort('availability_yrs')"
                                            class="font-semibold flex items-center gap-2"
                                        >
                                            Availability
                                            <span v-if="sortField === 'availability_yrs'">
                                                {{ sortDirection === "asc" ? "▲" : "▼" }}
                                            </span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="handleSort('unit_price')"
                                            class="font-semibold flex items-center gap-2"
                                        >
                                            Unit Price
                                            <span v-if="sortField === 'unit_price'">
                                                {{ sortDirection === "asc" ? "▲" : "▼" }}
                                            </span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="handleSort('created_at')"
                                            class="font-semibold flex items-center gap-2"
                                        >
                                            Created Date
                                            <span v-if="sortField === 'created_at'">
                                                {{ sortDirection === "asc" ? "▲" : "▼" }}
                                            </span>
                                        </button>
                                    </TableHead>
                                    <TableHead class="text-center">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="product in paginatedData" 
                                    :key="product.id_product" 
                                    class="hover:bg-gray-50"
                                >
                                    <TableCell class="font-medium">
                                        <div class="flex items-center">
                                            <Package class="w-4 h-4 mr-2 text-gray-400" />
                                            <span class="font-mono text-sm">{{ product.product_code || 'No Code' }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        <div>
                                            <div class="font-semibold">{{ product.name || 'Unknown Product' }}</div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs">
                                                {{ product.description || 'No description' }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getStatusBadgeClass(product.status)">
                                            <CheckCircle v-if="product.status === 'Active'" class="w-3 h-3 mr-1" />
                                            <AlertTriangle v-else-if="product.status === 'EOL'" class="w-3 h-3 mr-1" />
                                            <Archive v-else-if="product.status === 'Archived'" class="w-3 h-3 mr-1" />
                                            {{ product.status || 'Unknown' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-blue-600 font-medium">
                                            {{ formatDeliveryTime(product.min_delivery_day, product.max_delivery_day) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-purple-600 font-medium">
                                            {{ product.availability_yrs || 0 }} years
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-green-600 font-semibold">
                                            €{{ Number(product.unit_price || 0).toFixed(2) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(product.created_at) }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center justify-center space-x-2">
                                            <Link 
                                                v-if="product.id_product" 
                                                :href="`/Product/${product.id_product}/edit`"
                                            >
                                                <Button variant="outline" size="sm" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50">
                                                    <Edit class="w-4 h-4" />
                                                </Button>
                                            </Link>
                                            
                                            <Button 
                                                v-if="product.id_product"
                                                variant="outline" 
                                                size="sm" 
                                                class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                                @click="deleteProduct(product.id_product)"
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

                    <!-- ✅ ADD PAGINATION HERE -->
                    <div class="flex items-center justify-between space-x-2 py-4">
                        <div class="text-sm text-muted-foreground">
                            Showing
                            {{ (currentPage - 1) * itemsPerPage + 1 }} to
                            {{ Math.min(currentPage * itemsPerPage, validProducts.length) }}
                            of {{ validProducts.length }} results
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
                    <!-- ✅ TO HERE -->

                    <!-- Empty State -->
                    <div v-if="validProducts.length === 0" class="text-center py-12">
                        <Package class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500 mb-4">Get started by adding your first product.</p>
                        <Link :href="route('products.create')">
                            <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                                <Plus class="w-4 h-4 mr-2" />
                                Add First Product
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- ✅ Status-Based Statistics Cards - Updated for your 3 statuses -->
            <div class="grid gap-6 md:grid-cols-4">
                <!-- Rest of your statistics cards remain the same -->
                <!-- Total Products -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-blue-600">
                            <Package class="w-5 h-5 mr-2" />
                            Total Products
                        </CardTitle>
                        <CardDescription>All products in catalog</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-blue-600">{{ totalProducts }}</div>
                    </CardContent>
                </Card>

                <!-- Active Products -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-green-600">
                            <CheckCircle class="w-5 h-5 mr-2" />
                            Active
                        </CardTitle>
                        <CardDescription>Currently available products</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-green-600">{{ activeProducts }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalProducts > 0 ? Math.round((activeProducts / totalProducts) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>

                <!-- EOL Products -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-orange-600">
                            <AlertTriangle class="w-5 h-5 mr-2" />
                            EOL
                        </CardTitle>
                        <CardDescription>End of life products</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-orange-600">{{ eolProducts }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalProducts > 0 ? Math.round((eolProducts / totalProducts) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>

                <!-- Archived Products -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-gray-600">
                            <Archive class="w-5 h-5 mr-2" />
                            Archived
                        </CardTitle>
                        <CardDescription>Archived products</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-gray-600">{{ archivedProducts }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalProducts > 0 ? Math.round((archivedProducts / totalProducts) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>