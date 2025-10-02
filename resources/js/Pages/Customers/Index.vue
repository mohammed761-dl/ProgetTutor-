<script setup>

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
import { Trash2, Edit, Plus, Building2, CheckCircle, Clock, AlertTriangle, XCircle } from "lucide-vue-next";

// ✅ Define props
const props = defineProps({
    customers: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    performanceFlags: {
        type: Array,
        default: () => ['Always on time', 'Small delays', 'Frequent big delays', 'No payment']
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

// ✅ Check if we're in development mode
const isDevelopment = import.meta.env.DEV;

// ✅ Filter out null/invalid customers
const validCustomers = computed(() => {
    return (props.customers || []).filter(customer => 
        customer && 
        customer.id_customer !== null && 
        customer.id_customer !== undefined &&
        typeof customer === 'object'
    );
});

// Form for deletion
const deleteForm = useForm({});

// Delete customer function
const deleteCustomer = (customerId) => {
    if (!customerId) {
        console.error('Invalid customer ID');
        return;
    }
    
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        deleteForm.delete(route('customers.destroy', customerId), {
            onSuccess: () => {
                console.log('Customer deleted successfully');
                // Optionally refresh the page or update the list
                window.location.reload();
            },
            onError: (errors) => {
                console.error('Error deleting customer:', errors);
                
                // Show user-friendly error message
                if (errors.error) {
                    alert(`Delete Failed: ${errors.error}`);
                } else {
                    alert('Failed to delete customer. Please try again.');
                }
            }
        });
    }
};

// Get performance badge class with colors
const getPerformanceBadgeClass = (performance) => {
    if (!performance) return 'bg-gray-100 text-gray-800';
    
    switch (performance) {
        case 'Always on time':
            return 'bg-green-100 text-green-800 hover:bg-green-200';
        case 'Small delays':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        case 'Frequent big delays':
            return 'bg-orange-100 text-orange-800 hover:bg-orange-200';
        case 'No payment':
            return 'bg-red-100 text-red-800 hover:bg-red-200';
        default:
            return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
    }
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return 'Invalid Date';
    }
};

// ✅ Performance-based customer statistics
const totalCustomers = computed(() => validCustomers.value.length);

const customersAlwaysOnTime = computed(() => 
    validCustomers.value.filter(customer => customer.performance_flag === 'Always on time').length
);

const customersWithDelays = computed(() => 
    validCustomers.value.filter(customer => 
        customer.performance_flag === 'Small delays' || customer.performance_flag === 'Frequent big delays'
    ).length
);

const customersNoPayment = computed(() => 
    validCustomers.value.filter(customer => customer.performance_flag === 'No payment').length
);
</script>

<template>
    <AdminApp>
        <div class="pt-6 space-y-8">
            <!-- Customers Management Card -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle class="flex items-center">
                                <Building2 class="w-5 h-5 mr-2" />
                                Customers Management
                            </CardTitle>
                            <CardDescription>
                                Manage your customers and their performance information
                            </CardDescription>
                        </div>
                        <Link :href="route('customers.create')">
                            <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                                <Plus class="w-4 h-4 mr-2" />
                                Add New Customer
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Customers Table -->
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Company Name</TableHead>
                                <TableHead>Contact Person</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Performance Rating</TableHead>
                                <TableHead>VAT Number</TableHead>
                                <TableHead>Created Date</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="customer in validCustomers" 
                                :key="customer.id_customer" 
                                class="hover:bg-gray-50"
                            >
                                <TableCell class="font-medium">
                                    #{{ customer.id_customer || 'N/A' }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    <div class="flex items-center">
                                        <Building2 class="w-4 h-4 mr-2 text-gray-400" />
                                        {{ customer.company_name || 'Unknown Company' }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ customer.contact_name || 'No contact' }}
                                </TableCell>
                                <TableCell>
                                    <a :href="`mailto:${customer.email}`" class="text-blue-600 hover:underline">
                                        {{ customer.email || 'No email' }}
                                    </a>
                                </TableCell>
                                <TableCell>
                                    {{ customer.phone || 'No phone' }}
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getPerformanceBadgeClass(customer.performance_flag)">
                                        <CheckCircle v-if="customer.performance_flag === 'Always on time'" class="w-3 h-3 mr-1" />
                                        <Clock v-else-if="customer.performance_flag === 'Small delays'" class="w-3 h-3 mr-1" />
                                        <AlertTriangle v-else-if="customer.performance_flag === 'Frequent big delays'" class="w-3 h-3 mr-1" />
                                        <XCircle v-else-if="customer.performance_flag === 'No payment'" class="w-3 h-3 mr-1" />
                                        {{ customer.performance_flag || 'N/A' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="font-mono text-sm">
                                        {{ customer.vat_number || 'No VAT' }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(customer.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center justify-center space-x-2">
                                        <Link 
                                            v-if="customer.id_customer" 
                                            :href="route('customers.edit', customer.id_customer)"
                                        >
                                            <Button variant="outline" size="sm" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50">
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                        </Link>
                                        
                                        <Button 
                                            v-if="customer.id_customer"
                                            variant="outline" 
                                            size="sm" 
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                            @click="deleteCustomer(customer.id_customer)"
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
                    <div v-if="validCustomers.length === 0" class="text-center py-12">
                        <Building2 class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No customers found</h3>
                        <p class="text-gray-500 mb-4">Get started by adding your first customer.</p>
                        <Link :href="route('customers.create')">
                            <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                                <Plus class="w-4 h-4 mr-2" />
                                Add First Customer
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- ✅ Performance-Based Statistics Cards -->
            <div class="grid gap-6 md:grid-cols-4">
                <!-- Total Customers -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-blue-600">
                            <Building2 class="w-5 h-5 mr-2" />
                            Total Customers
                        </CardTitle>
                        <CardDescription>All registered customers</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-blue-600">{{ totalCustomers }}</div>
                    </CardContent>
                </Card>

                <!-- Always On Time -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-green-600">
                            <CheckCircle class="w-5 h-5 mr-2" />
                            Always On Time
                        </CardTitle>
                        <CardDescription>Customers who pay on time</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-green-600">{{ customersAlwaysOnTime }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalCustomers > 0 ? Math.round((customersAlwaysOnTime / totalCustomers) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>

                <!-- With Delays -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-orange-600">
                            <AlertTriangle class="w-5 h-5 mr-2" />
                            Frequent Delays
                        </CardTitle>
                        <CardDescription>Customers with payment delays</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-orange-600">{{ customersWithDelays }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalCustomers > 0 ? Math.round((customersWithDelays / totalCustomers) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>

                <!-- No Payment -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-red-600">
                            <XCircle class="w-5 h-5 mr-2" />
                            No Payment
                        </CardTitle>
                        <CardDescription>Customers with payment issues</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-red-600">{{ customersNoPayment }}</div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ totalCustomers > 0 ? Math.round((customersNoPayment / totalCustomers) * 100) : 0 }}% of total
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>