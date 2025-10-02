<script setup>
// filepath: resources/js/Pages/DeliveryNote/Edit.vue

import Layout from "../../Layout/App.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import Textarea from "@/components/ui/textarea/Textarea.vue";
import { Truck, Save, ArrowLeft, Calendar, FileCheck, MapPin, Package, Plus, Trash2 } from "lucide-vue-next";

const props = defineProps({
    deliveryNote: Object,
    aros: Array
});

// Available products from selected ARO
const aroProducts = ref([]);
const selectedARO = ref(null);

const form = useForm({
    id_aro: props.deliveryNote.id_aro || '',
    planned_delivery_date: props.deliveryNote.planned_delivery_date || '',
    actual_delivery_date: props.deliveryNote.actual_delivery_date || '',
    status: props.deliveryNote.status || 'Pending',
    incoterms: props.deliveryNote.incoterms || '',
    delivery_address: props.deliveryNote.delivery_address || '',
    packaging_details: props.deliveryNote.packaging_details || '',
    remarks: props.deliveryNote.remarks || '',
    products: props.deliveryNote.products || [
        {
            id_product: '',
            product_quantity_delivered: 0
        }
    ]
});

// Initialize selected ARO
if (form.id_aro) {
    selectedARO.value = props.aros.find(aro => aro.id_aro == form.id_aro);
}

// Watch for ARO selection changes
watch(() => form.id_aro, (newAroId) => {
    if (newAroId) {
        selectedARO.value = props.aros.find(aro => aro.id_aro == newAroId);
        // You would typically fetch ARO products here via API
        aroProducts.value = [];
    } else {
        selectedARO.value = null;
        aroProducts.value = [];
    }
});

// Add product row
const addProduct = () => {
    form.products.push({
        id_product: '',
        product_quantity_delivered: 0
    });
};

// Remove product row
const removeProduct = (index) => {
    if (form.products.length > 1) {
        form.products.splice(index, 1);
    }
};

const submit = () => {
    form.put(`/delivery-notes/${props.deliveryNote.id_dn}`, {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Layout>
        <div class="max-w-4xl mx-auto p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <Truck class="w-8 h-8 mr-3 text-blue-600" />
                        Edit Delivery Note
                    </h1>
                    <p class="text-gray-600 mt-1">Update delivery note: {{ deliveryNote.dn_number }}</p>
                </div>
                <Button variant="outline" @click="goBack" class="flex items-center">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>
            </div>

            <!-- Main Form -->
            <Card>
                <CardContent class="p-6">
                    <form @submit.prevent="submit" class="space-y-8">
                        
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Truck class="w-5 h-5 mr-2 text-blue-600" />
                                    Basic Information
                                </h3>
                                <p class="text-sm text-gray-600">DN Number: {{ deliveryNote.dn_number }} (Auto-generated)</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- ARO Reference -->
                                <div class="space-y-2 md:col-span-2">
                                    <Label for="id_aro" class="text-sm font-medium text-gray-700 flex items-center">
                                        <FileCheck class="w-4 h-4 mr-1 text-purple-600" />
                                        ARO Reference *
                                    </Label>
                                    <select 
                                        id="id_aro"
                                        v-model="form.id_aro" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Select ARO</option>
                                        <option v-for="aro in aros" :key="aro.id_aro" :value="aro.id_aro">
                                            {{ aro.aro_number }} - {{ aro.customer_name }} (PO: {{ aro.po_number }})
                                        </option>
                                    </select>
                                    <div v-if="form.errors.id_aro" class="text-red-500 text-sm">
                                        {{ form.errors.id_aro }}
                                    </div>
                                </div>
                            </div>

                            <!-- Selected ARO Info -->
                            <div v-if="selectedARO" class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <h4 class="font-semibold text-blue-800 mb-2">Selected ARO Details</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><strong>ARO:</strong> {{ selectedARO.aro_number }}</div>
                                    <div><strong>Customer:</strong> {{ selectedARO.customer_name }}</div>
                                    <div><strong>PO Number:</strong> {{ selectedARO.po_number }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Calendar class="w-5 h-5 mr-2 text-green-600" />
                                    Delivery Information
                                </h3>
                                <p class="text-sm text-gray-600">Update delivery dates and status</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Planned Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="planned_delivery_date" class="text-sm font-medium text-gray-700">
                                        Planned Delivery Date *
                                    </Label>
                                    <Input
                                        id="planned_delivery_date"
                                        v-model="form.planned_delivery_date"
                                        type="date"
                                        class="w-full"
                                        required
                                    />
                                    <div v-if="form.errors.planned_delivery_date" class="text-red-500 text-sm">
                                        {{ form.errors.planned_delivery_date }}
                                    </div>
                                </div>

                                <!-- Actual Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="actual_delivery_date" class="text-sm font-medium text-gray-700">
                                        Actual Delivery Date
                                    </Label>
                                    <Input
                                        id="actual_delivery_date"
                                        v-model="form.actual_delivery_date"
                                        type="date"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.actual_delivery_date" class="text-red-500 text-sm">
                                        {{ form.errors.actual_delivery_date }}
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label for="status" class="text-sm font-medium text-gray-700">Status *</Label>
                                    <select 
                                        id="status"
                                        v-model="form.status" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="Pending">⏳ Pending</option>
                                        <option value="Delivered">✅ Delivered</option>
                                        <option value="Partially Delivered">📦 Partially Delivered</option>
                                        <option value="Returned">🔄 Returned</option>
                                        <option value="Cancelled">❌ Cancelled</option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-500 text-sm">
                                        {{ form.errors.status }}
                                    </div>
                                </div>

                                <!-- Incoterms -->
                                <div class="space-y-2">
                                    <Label for="incoterms" class="text-sm font-medium text-gray-700">
                                        Incoterms
                                    </Label>
                                    <select 
                                        id="incoterms"
                                        v-model="form.incoterms"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Select Incoterms</option>
                                        <option value="EXW">EXW - Ex Works</option>
                                        <option value="FCA">FCA - Free Carrier</option>
                                        <option value="CPT">CPT - Carriage Paid To</option>
                                        <option value="CIP">CIP - Carriage and Insurance Paid To</option>
                                        <option value="DAP">DAP - Delivered At Place</option>
                                        <option value="DPU">DPU - Delivered at Place Unloaded</option>
                                        <option value="DDP">DDP - Delivered Duty Paid</option>
                                        <option value="FAS">FAS - Free Alongside Ship</option>
                                        <option value="FOB">FOB - Free On Board</option>
                                        <option value="CFR">CFR - Cost and Freight</option>
                                        <option value="CIF">CIF - Cost, Insurance and Freight</option>
                                    </select>
                                    <div v-if="form.errors.incoterms" class="text-red-500 text-sm">
                                        {{ form.errors.incoterms }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address & Details -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <MapPin class="w-5 h-5 mr-2 text-pink-600" />
                                    Address & Details
                                </h3>
                                <p class="text-sm text-gray-600">Update delivery address and additional information</p>
                            </div>
                            
                            <!-- Delivery Address -->
                            <div class="space-y-2">
                                <Label for="delivery_address" class="text-sm font-medium text-gray-700">
                                    Delivery Address *
                                </Label>
                                <Textarea
                                    id="delivery_address"
                                    v-model="form.delivery_address"
                                    placeholder="Enter complete delivery address..."
                                    rows="3"
                                    class="w-full"
                                    required
                                />
                                <div v-if="form.errors.delivery_address" class="text-red-500 text-sm">
                                    {{ form.errors.delivery_address }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Packaging Details -->
                                <div class="space-y-2">
                                    <Label for="packaging_details" class="text-sm font-medium text-gray-700 flex items-center">
                                        <Package class="w-4 h-4 mr-1 text-orange-600" />
                                        Packaging Details
                                    </Label>
                                    <Textarea
                                        id="packaging_details"
                                        v-model="form.packaging_details"
                                        placeholder="Describe packaging details..."
                                        rows="3"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.packaging_details" class="text-red-500 text-sm">
                                        {{ form.errors.packaging_details }}
                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div class="space-y-2">
                                    <Label for="remarks" class="text-sm font-medium text-gray-700">
                                        Remarks
                                    </Label>
                                    <Textarea
                                        id="remarks"
                                        v-model="form.remarks"
                                        placeholder="Additional notes or remarks..."
                                        rows="3"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.remarks" class="text-red-500 text-sm">
                                        {{ form.errors.remarks }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Products Section -->
                        <div class="space-y-4">
                            <div class="border-b pb-2 flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <Package class="w-5 h-5 mr-2 text-blue-600" />
                                        Products to Deliver
                                    </h3>
                                    <p class="text-sm text-gray-600">Update products and quantities for delivery</p>
                                </div>
                                <Button type="button" @click="addProduct" variant="outline" size="sm">
                                    <Plus class="w-4 h-4 mr-1" />
                                    Add Product
                                </Button>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(product, index) in form.products" :key="index" 
                                     class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg">
                                    
                                    <!-- Product Selection -->
                                    <div class="space-y-2 md:col-span-2">
                                        <Label :for="`product_${index}`" class="text-sm font-medium text-gray-700">
                                            Product *
                                        </Label>
                                        <div class="flex flex-col space-y-1">
                                            <Input
                                                :id="`product_${index}`"
                                                v-model="product.id_product"
                                                type="text"
                                                placeholder="Enter product ID"
                                                class="w-full"
                                                required
                                            />
                                            <div v-if="product.product_name" class="text-sm text-gray-600">
                                                Current: {{ product.product_name }} - ${{ product.product_price }}
                                            </div>
                                        </div>
                                        <div v-if="form.errors[`products.${index}.id_product`]" class="text-red-500 text-sm">
                                            {{ form.errors[`products.${index}.id_product`] }}
                                        </div>
                                    </div>

                                    <!-- Quantity & Remove -->
                                    <div class="flex space-x-2">
                                        <div class="flex-1 space-y-2">
                                            <Label :for="`quantity_${index}`" class="text-sm font-medium text-gray-700">
                                                Quantity Delivered *
                                            </Label>
                                            <Input
                                                :id="`quantity_${index}`"
                                                v-model.number="product.product_quantity_delivered"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                placeholder="0.00"
                                                class="w-full"
                                                required
                                            />
                                            <div v-if="form.errors[`products.${index}.product_quantity_delivered`]" class="text-red-500 text-sm">
                                                {{ form.errors[`products.${index}.product_quantity_delivered`] }}
                                            </div>
                                        </div>
                                        
                                        <!-- Remove Button -->
                                        <div class="flex items-end">
                                            <Button 
                                                type="button" 
                                                @click="removeProduct(index)"
                                                variant="outline"
                                                size="sm"
                                                class="text-red-600 hover:text-red-700"
                                                :disabled="form.products.length === 1"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t">
                            <Button type="button" variant="outline" @click="goBack" class="flex items-center">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Cancel
                            </Button>
                            
                            <Button 
                                type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-8 py-2 flex items-center"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Updating Note...' : 'Update Delivery Note' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </Layout>
</template>

<style scoped>
/* Custom focus styles for better UX */
input:focus, select:focus, textarea:focus {
    outline: none;
    box-shadow: 0 0 0 2px #3b82f6;
    border-color: #3b82f6;
}

/* Loading state for submit button */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
