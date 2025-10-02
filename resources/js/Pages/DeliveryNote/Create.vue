<script setup>
// filepath: resources/js/Pages/DeliveryNote/Create.vue

import Layout from "../../Layout/App.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import Textarea from "@/components/ui/textarea/Textarea.vue";
import { Truck, Save, ArrowLeft, Calendar, FileCheck, MapPin, Package } from "lucide-vue-next";

const props = defineProps({
    aros: Array
});

// Available products from selected ARO
const aroProducts = ref([]);
const selectedARO = ref(null);

const form = useForm({
    id_aro: '',
    date_delivery: new Date().toISOString().split('T')[0],
    planned_delivery_date: new Date().toISOString().split('T')[0],
    actual_delivery_date: '',
    status: 'Pending',
    incoterms: '',
    delivery_address: '',
    packaging_details: '',
    remarks: ''
    // Remove products - they come from ARO automatically
});

// Watch for ARO selection changes to show ARO details
watch(() => form.id_aro, (newAroId) => {
    if (newAroId) {
        selectedARO.value = props.aros.find(aro => aro.id_aro == newAroId);
        
        // Fetch ARO products for display only (not for form submission)
        fetch(`/api/aros/${newAroId}/products`)
            .then(response => response.json())
            .then(data => {
                aroProducts.value = data.products;
            })
            .catch(error => {
                console.error('Error fetching ARO products:', error);
                aroProducts.value = [];
            });
    } else {
        selectedARO.value = null;
        aroProducts.value = [];
    }
});

const submit = () => {
    form.post('/delivery-notes', {
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
                        Create Delivery Note
                    </h1>
                    <p class="text-gray-600 mt-1">Create a new delivery note for shipping</p>
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
                                <p class="text-sm text-gray-600">DN number will be auto-generated upon creation</p>
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
                                            {{ aro.aro_number }} - {{ aro.customer_name }}
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
                                <p class="text-sm text-gray-600">Set delivery dates and status</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="date_delivery" class="text-sm font-medium text-gray-700">
                                        Delivery Date *
                                    </Label>
                                    <Input
                                        id="date_delivery"
                                        v-model="form.date_delivery"
                                        type="date"
                                        class="w-full"
                                        required
                                    />
                                    <div v-if="form.errors.date_delivery" class="text-red-500 text-sm">
                                        {{ form.errors.date_delivery }}
                                    </div>
                                </div>

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
                                <p class="text-sm text-gray-600">Delivery address and additional information</p>
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

                        <!-- Products Section - Display Only -->
                        <div class="space-y-4" v-if="selectedARO && aroProducts.length > 0">
                            <div class="border-b pb-2">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <Package class="w-5 h-5 mr-2 text-blue-600" />
                                        Products from ARO
                                    </h3>
                                    <p class="text-sm text-gray-600">These products will be automatically included in the delivery note</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div v-for="aroProduct in aroProducts" :key="aroProduct.id" 
                                     class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                    
                                    <!-- Product Info -->
                                    <div class="col-span-2">
                                        <div class="space-y-2">
                                            <p class="font-medium text-gray-900">{{ aroProduct.name }}</p>
                                            <p class="text-sm text-gray-600">Code: {{ aroProduct.product_code }}</p>
                                            <p class="text-sm text-gray-600 line-clamp-2">{{ aroProduct.description }}</p>
                                            <p v-if="aroProduct.technical_specs" class="text-xs text-gray-500 line-clamp-1">
                                                <span class="font-medium">Tech Specs:</span> {{ aroProduct.technical_specs }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Confirmed Quantity -->
                                    <div class="space-y-2">
                                        <Label class="text-sm font-medium text-gray-700">Confirmed Quantity</Label>
                                        <div class="text-sm font-medium text-blue-600">
                                            {{ aroProduct.quantity_confirmed }}
                                        </div>
                                    </div>

                                    <!-- Unit Price -->
                                    <div class="space-y-2">
                                        <Label class="text-sm font-medium text-gray-700">Unit Price</Label>
                                        <div class="text-sm font-medium text-green-600">
                                            ${{ aroProduct.unit_price_agreed?.toFixed(2) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- No products message -->
                                <div v-if="selectedARO && aroProducts.length === 0" class="text-center py-8 text-gray-500">
                                    <Package class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                    <p>No products found in the selected ARO</p>
                                </div>
                            </div>
                        </div>

                        <!-- Select ARO first message -->
                        <div v-else-if="!selectedARO" class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Package class="w-5 h-5 mr-2 text-blue-600" />
                                    Products to Deliver
                                </h3>
                                <p class="text-sm text-gray-600">Select an ARO above to see products that will be included</p>
                            </div>
                            
                            <div class="text-center py-8 text-gray-500">
                                <FileCheck class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                <p>Please select an ARO first to preview products</p>
                                <p class="text-xs mt-2">All ARO products will be automatically included in the delivery note</p>
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
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 flex items-center"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Creating Note...' : 'Create Delivery Note' }}
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