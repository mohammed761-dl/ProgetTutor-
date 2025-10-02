<script setup>
// filepath: resources/js/Pages/DeliveryNote/Show.vue

import Layout from "../../Layout/App.vue";
import { Link } from "@inertiajs/vue3";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import Badge from "@/components/ui/badge/Badge.vue";
import Button from "@/components/ui/button/Button.vue";
import { 
    ArrowLeft, 
    Edit, 
    Truck, 
    Calendar, 
    MapPin, 
    Package, 
    FileCheck, 
    User,
    Building2,
    Clock,
    CheckCircle,
    RotateCcw,
    XCircle,
    AlertTriangle
} from "lucide-vue-next";

const props = defineProps({
    deliveryNote: Object
});

// Status icon mapping
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

// Status color mapping
const getStatusColor = (status) => {
    switch (status) {
        case 'Pending':
            return 'text-yellow-600';
        case 'Delivered':
            return 'text-green-600';
        case 'Partially Delivered':
            return 'text-blue-600';
        case 'Returned':
            return 'text-orange-600';
        case 'Cancelled':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
};
</script>

<template>
    <Layout>
        <div class="max-w-6xl mx-auto p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <Truck class="w-8 h-8 mr-3 text-blue-600" />
                        Delivery Note Details
                    </h1>
                    <p class="text-gray-600 mt-1">{{ deliveryNote.dn_number }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="`/delivery-notes/${deliveryNote.id_dn}/edit`">
                        <Button class="bg-green-600 hover:bg-green-700 text-white">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit
                        </Button>
                    </Link>
                    <Link href="/delivery-notes">
                        <Button variant="outline" class="flex items-center">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to List
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Truck class="w-5 h-5 mr-2 text-blue-600" />
                                Delivery Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">DN Number</label>
                                    <p class="font-mono text-lg font-semibold">{{ deliveryNote.dn_number }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Status</label>
                                    <div class="flex items-center mt-1">
                                        <component :is="getStatusIcon(deliveryNote.status)" 
                                                 :class="['w-5 h-5 mr-2', getStatusColor(deliveryNote.status)]" />
                                        <Badge :class="deliveryNote.status_badge">
                                            {{ deliveryNote.status }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 flex items-center">
                                        <Calendar class="w-4 h-4 mr-1" />
                                        Planned Delivery Date
                                    </label>
                                    <p class="text-lg">
                                        {{ deliveryNote.planned_delivery_date ? 
                                           new Date(deliveryNote.planned_delivery_date).toLocaleDateString('en-US', {
                                             weekday: 'long',
                                             year: 'numeric', 
                                             month: 'long', 
                                             day: 'numeric'
                                           }) : 'Not set' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 flex items-center">
                                        <Calendar class="w-4 h-4 mr-1" />
                                        Actual Delivery Date
                                    </label>
                                    <p class="text-lg">
                                        {{ deliveryNote.actual_delivery_date ? 
                                           new Date(deliveryNote.actual_delivery_date).toLocaleDateString('en-US', {
                                             weekday: 'long',
                                             year: 'numeric', 
                                             month: 'long', 
                                             day: 'numeric'
                                           }) : 'Not delivered yet' }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="deliveryNote.incoterms">
                                <label class="text-sm font-medium text-gray-500">Incoterms</label>
                                <p class="text-lg">{{ deliveryNote.incoterms }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- ARO Reference -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <FileCheck class="w-5 h-5 mr-2 text-purple-600" />
                                ARO Reference
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-purple-700">ARO Number</label>
                                        <p class="font-semibold text-purple-800">{{ deliveryNote.aro.aro_number }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-purple-700">Customer</label>
                                        <p class="font-semibold text-purple-800">{{ deliveryNote.aro.customer_name }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-purple-700">PO Number</label>
                                        <p class="font-semibold text-purple-800">{{ deliveryNote.aro.po_number }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Delivery Address -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <MapPin class="w-5 h-5 mr-2 text-pink-600" />
                                Delivery Address
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <pre class="whitespace-pre-wrap text-gray-900">{{ deliveryNote.delivery_address }}</pre>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Products -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Package class="w-5 h-5 mr-2 text-orange-600" />
                                Products Delivered
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Product Code</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Product Name</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Description</th>
                                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Quantity</th>
                                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Price</th>
                                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Subtotal</th>
                                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Tracking</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="product in deliveryNote.products" :key="product.id" 
                                            class="border-b border-gray-100">
                                            <td class="py-3 px-4 font-mono text-sm">{{ product.product_code }}</td>
                                            <td class="py-3 px-4">
                                                <p class="font-semibold">{{ product.name }}</p>
                                                <p v-if="product.technical_specs" class="text-xs text-gray-600">
                                                    {{ product.technical_specs }}
                                                </p>
                                            </td>
                                            <td class="py-3 px-4">
                                                <p class="text-sm text-gray-600">{{ product.description }}</p>
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold">
                                                {{ product.quantity_shipped }}
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <p class="font-semibold">${{ parseFloat(product.unit_price).toFixed(2) }}</p>
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold">
                                                ${{ product.total_line_price }}
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <div v-if="product.tracking_code || product.serial_numbers" class="space-y-1">
                                                    <p v-if="product.tracking_code" class="text-sm font-mono">
                                                        {{ product.tracking_code }}
                                                    </p>
                                                    <p v-if="product.serial_numbers" class="text-xs text-gray-600">
                                                        S/N: {{ product.serial_numbers }}
                                                    </p>
                                                </div>
                                                <p v-else class="text-sm text-gray-400">-</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="py-3 px-4 text-right font-semibold">Total Value:</td>
                                            <td class="py-3 px-4 text-right font-bold text-lg">
                                                ${{ deliveryNote.products.reduce((total, product) => 
                                                    total + (parseFloat(product.product_price) * parseFloat(product.product_quantity_delivered)), 0
                                                ).toFixed(2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Creation Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <User class="w-5 h-5 mr-2 text-gray-600" />
                                Creation Info
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Created By</label>
                                <p class="font-semibold">{{ deliveryNote.created_by }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Created On</label>
                                <p class="font-semibold">{{ deliveryNote.created_at }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Packaging Details -->
                    <Card v-if="deliveryNote.packaging_details">
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Package class="w-5 h-5 mr-2 text-orange-600" />
                                Packaging Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                                <pre class="whitespace-pre-wrap text-orange-900">{{ deliveryNote.packaging_details }}</pre>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Remarks -->
                    <Card v-if="deliveryNote.remarks">
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <AlertTriangle class="w-5 h-5 mr-2 text-amber-600" />
                                Remarks
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                                <pre class="whitespace-pre-wrap text-amber-900">{{ deliveryNote.remarks }}</pre>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link :href="`/delivery-notes/${deliveryNote.id_dn}/edit`" class="w-full">
                                <Button class="w-full bg-green-600 hover:bg-green-700 text-white">
                                    <Edit class="w-4 h-4 mr-2" />
                                    Edit Delivery Note
                                </Button>
                            </Link>
                            <Link href="/delivery-notes" class="w-full">
                                <Button variant="outline" class="w-full">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Back to List
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </Layout>
</template>
