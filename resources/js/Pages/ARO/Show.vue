<template>
    <Layout>
        <div :class="{'print-view': isPrintView, 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6': !isPrintView}">
            <!-- Header -->
            <div v-if="!isPrintView" class="py-6 md:flex md:items-center md:justify-between no-print border-b border-gray-200">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight flex items-center">
                        <FileCheck class="w-8 h-8 mr-3 text-blue-600" />
                        View ARO: {{ aro.aro_number }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">Acknowledgment of Receipt Order Details</p>
                </div>
                <div class="mt-4 flex flex-shrink-0 md:ml-4 md:mt-0">
                    <Link :href="`/aro/${aro.id_aro}/edit`">
                        <Button variant="default" class="mr-2 bg-blue-600 hover:bg-blue-700 text-white">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit ARO
                        </Button>
                    </Link>
                    <a :href="`/aro/${aro.id_aro}/download-pdf`" target="_blank" class="inline-block mr-2">
                        <Button variant="secondary" class="bg-purple-600 hover:bg-purple-700 text-white">
                            <Download class="w-4 h-4 mr-2" />
                            Download PDF
                        </Button>
                    </a>
                    <button @click="goToPrint" class="inline-block mr-2">
                        <Button variant="secondary" class="bg-gray-600 hover:bg-gray-700 text-white">
                            <Printer class="w-4 h-4 mr-2" />
                            Print View
                        </Button>
                    </button>
                    <Button variant="outline" @click="goBack" class="flex items-center">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back
                    </Button>
                </div>
            </div>

            <!-- Print View Header -->
            <div v-if="isPrintView" class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2">ACKNOWLEDGMENT OF RECEIPT OF ORDER (ARO)</h1>
                <h2 class="text-xl">{{ aro.aro_number }}</h2>
            </div>

            <!-- ARO Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 flex items-center">
                        <ClipboardList class="w-5 h-5 mr-2 text-blue-600" />
                        ARO Information
                    </h3>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-100">
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">ARO Number</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ aro.aro_number }}</dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Status</dt>
                                <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                    <Badge :class="getStatusBadgeClass(aro.status)" class="inline-flex items-center">
                                        <component :is="getStatusIcon(aro.status)" class="w-4 h-4 mr-1" />
                                        {{ aro.status }}
                                    </Badge>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Purchase Order Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 flex items-center">
                        <FileText class="w-5 h-5 mr-2 text-purple-600" />
                        Purchase Order Details
                    </h3>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-100">
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">PO Number</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ aro.purchase_order?.po_number }}</dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">PO Status</dt>
                                <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                    <Badge :class="getStatusBadgeClass(aro.purchase_order?.status)" class="inline-flex items-center">
                                        {{ aro.purchase_order?.status }}
                                    </Badge>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Customer Information -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium leading-6 text-gray-900 mb-4">Customer Information</h4>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ aro.purchase_order?.customer?.company_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ aro.purchase_order?.customer?.contact_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ aro.purchase_order?.customer?.email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ aro.purchase_order?.customer?.phone }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium leading-6 text-gray-900 mb-4">Products</h4>
                        <div class="mt-4 flow-root">
                            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-300">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Product</th>
                                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Quantity</th>
                                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Unit Price</th>
                                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white">
                                                <tr v-for="product in aro.purchase_order?.products" :key="product.id">
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                                                        <div>
                                                            <p class="font-medium text-gray-900">{{ product.name }}</p>
                                                            <p class="text-sm text-gray-500">{{ product.product_code }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500">{{ product.pivot.quantity }}</td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500">{{ formatCurrency(product.pivot.unit_price) }}</td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500">{{ formatCurrency(product.pivot.quantity * product.pivot.unit_price) }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr>
                                                    <td colspan="3" class="py-3.5 pl-4 pr-3 text-right text-sm font-semibold text-gray-900 sm:pl-6">Total:</td>
                                                    <td class="whitespace-nowrap px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
                                                        {{ formatCurrency(calculateTotal()) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 flex items-center">
                        <Info class="w-5 h-5 mr-2 text-gray-600" />
                        Additional Information
                    </h3>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-100">
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Created By</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ aro.creator?.name }}</dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Creation Date</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ formatDate(aro.date_aro) }}</dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Remarks</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0 whitespace-pre-line">{{ aro.remarks || 'No remarks' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Print Footer -->
            <div v-if="isPrintView" class="text-sm text-gray-500 text-center mt-8">
                <p>Generated on {{ new Date().toLocaleString() }}</p>
                <p>This is a computer-generated document. No signature is required.</p>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import Layout from "../../Layout/App.vue";
import { Link } from "@inertiajs/vue3";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Button from "@/components/ui/button/Button.vue";
import Badge from "@/components/ui/badge/Badge.vue";
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
    FileCheck, 
    Edit, 
    ArrowLeft, 
    FileText, 
    ClipboardList,
    Info,
    CheckCircle,
    Clock,
    XCircle,
    BarChart3,
    Download,
    Printer
} from "lucide-vue-next";

const props = defineProps({
    aro: {
        type: Object,
        required: true
    },
    isPrintView: {
        type: Boolean,
        default: false
    }
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString();
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const calculateTotal = () => {
    if (!props.aro.purchase_order?.products) return 0;
    return props.aro.purchase_order.products.reduce((total, product) => {
        return total + (product.pivot.quantity * product.pivot.unit_price);
    }, 0);
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Delivered':
            return 'bg-green-100 text-green-800';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'Partially Delivered':
            return 'bg-blue-100 text-blue-800';
        case 'Cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

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

const goBack = () => {
    window.history.back();
};

const goToPrint = () => {
    // Simple navigation to test if route works
    window.location.href = `/aro/${props.aro.id_aro}/print`;
};

// Auto-print when in print view
const autoPrint = () => {
    if (props.isPrintView) {
        setTimeout(() => {
            window.print();
        }, 500);
    }
};

onMounted(() => {
    autoPrint();
});
</script>

<style>
/* Print-specific styles */
@media print {
    @page {
        margin: 2cm;
        size: A4;
    }
    
    /* Hide non-printable elements */
    button, 
    .no-print,
    [variant="outline"],
    a[href]:after {
        display: none !important;
    }
    
    /* Reset page margins and padding */
    .max-w-4xl {
        max-width: none !important;
        margin: 0 !important;
        padding: 2cm !important;
    }
    
    /* Ensure tables fit on page */
    table {
        page-break-inside: avoid;
    }
    
    /* Typography adjustments for print */
    body {
        font-size: 12pt;
        line-height: 1.3;
    }
    
    h1 { font-size: 18pt; }
    h2 { font-size: 16pt; }
    h3 { font-size: 14pt; }
    
    /* Ensure white background */
    * {
        background-color: transparent !important;
        color: black !important;
    }
}

/* Print view specific styles */
.print-view {
    padding: 2cm;
}
</style>

