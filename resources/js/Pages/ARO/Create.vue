<script setup>
// filepath: resources/js/Pages/ARO/Create.vue

import Layout from "../../Layout/App.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import { FileCheck, Save, ArrowLeft, Calendar, User, ClipboardList, Truck, MessageSquare } from "lucide-vue-next";

import { ref, watch } from 'vue';

const props = defineProps({
    purchaseOrders: Array,
    statuses: Array,
    debugInfo: Object
});

// Initialize form first
const form = useForm({
    id_po: '',
    date_aro: new Date().toISOString().split('T')[0], // Add missing date_aro field
    status: 'Pending',
    remarks: '',
    planned_delivery_date: null,
    actual_delivery_date: null,
    products: [] // Initialize empty products array
});

const selectedPO = ref(null);

// Watch for changes in the selected PO after form is initialized
watch(() => form.id_po, async (newValue) => {
    console.log('PO ID changed to:', newValue);
    
    if (newValue) {
        // Find the selected PO
        selectedPO.value = props.purchaseOrders.find(po => po.id_po === parseInt(newValue));
        console.log('Selected PO:', selectedPO.value);
        
        // Get products from quote
        if (selectedPO.value?.quote) {
            console.log('Found Quote:', selectedPO.value.quote);
            console.log('Quote ID:', selectedPO.value.quote.id_quote);
            
            // Reset form products array
            form.products = [];
            
            // Get quote products from the loaded relationship
            let quoteProducts = selectedPO.value.quote.quoteProducts || [];
            console.log('Quote products found:', quoteProducts);
            console.log('Quote products type:', typeof quoteProducts);
            console.log('Quote products length:', quoteProducts.length);
            
            if (quoteProducts && quoteProducts.length > 0) {
                console.log('Processing quote products...');
                // Add each product from quote
                quoteProducts.forEach((quoteProduct, index) => {
                    console.log(`Quote product ${index}:`, quoteProduct);
                    form.products.push({
                        quote_product_id: quoteProduct.id,
                        quantity_received: 0,
                        quantity_ordered: quoteProduct.quantity || 1,
                        product_name: quoteProduct.name || 'Product',
                        product_code: quoteProduct.product_code || 'CODE',
                        description: quoteProduct.description || 'No description',
                        remarks: ''
                    });
                });
            } else {
                // No quote products found - this means the relationship isn't loading properly
                console.log('No quote products found - relationship not loading properly');
                console.log('Available quote properties:', Object.keys(selectedPO.value.quote));
                
                            // Let's try to get the products from the PO products instead
            if (selectedPO.value.products && selectedPO.value.products.length > 0) {
                console.log('Found PO products instead:', selectedPO.value.products);
                selectedPO.value.products.forEach((poProduct, index) => {
                    form.products.push({
                        quote_product_id: poProduct.quote_product_id || poProduct.id,
                        quantity_received: 0,
                        quantity_ordered: poProduct.quantity || 1,
                        product_name: poProduct.name || 'Product',
                        product_code: poProduct.product_code || 'CODE',
                        description: poProduct.description || 'No description',
                        remarks: ''
                    });
                });
            } else {
                console.log('No products found anywhere - fetching from API...');
                
                // Fetch quote products directly from the API
                try {
                    const response = await axios.get(`/api/quotes/${selectedPO.value.quote.id_quote}/products`);
                    console.log('API response for quote products:', response.data);
                    
                    // The API returns { products: [...], quote: {...} }
                    if (response.data && response.data.products && response.data.products.length > 0) {
                        console.log('Found products in API response:', response.data.products);
                        response.data.products.forEach((product, index) => {
                            form.products.push({
                                quote_product_id: product.quote_product_id, // Use the correct field name
                                quantity_received: 0,
                                quantity_ordered: product.quantity || 1,
                                product_name: product.product_name || 'Product',
                                product_code: product.product_code || 'CODE',
                                description: 'Product from quote', // API doesn't return description
                                remarks: ''
                            });
                        });
                        console.log('Products loaded from API:', form.products);
                    } else {
                        console.log('No products returned from API');
                        console.log('Response data structure:', response.data);
                    }
                } catch (error) {
                    console.error('Error fetching quote products:', error);
                }
            }
            }
            
            console.log('Final form products:', form.products);
        } else {
            console.log('No Quote found in selected PO');
            form.products = [];
        }
    } else {
        selectedPO.value = null;
        form.products = [];
    }
});

// Generate ARO Number (for display only)
const generateARONumber = () => {
    const today = new Date();
    const year = today.getFullYear();
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    return `SNX-ARO-${year}-${random}`;
};

const submit = () => {
    // Debug log to see what we're sending
    console.log('Form data being sent:', {
        id_po: form.id_po,
        date_aro: form.date_aro,
        status: form.status,
        remarks: form.remarks,
        products: form.products
    });

    // Validate that we have products before submitting
    if (!form.products || form.products.length === 0) {
        console.error('No products in form!');
        alert('Please select a Purchase Order first to load products.');
        return;
    }

    form.post(route('aro.store'), {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            console.log('Current form state:', form);
            console.log('Selected PO:', selectedPO.value);
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
                        <FileCheck class="w-8 h-8 mr-3 text-blue-600" />
                        Create ARO
                    </h1>
                    <p class="text-gray-600 mt-1">Create a new Acknowledgment of Receipt of Order</p>
                </div>
                <Button variant="outline" @click="goBack" class="flex items-center">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>
            </div>
            <!-- No Purchase Orders Warning -->
            <div v-if="!purchaseOrders || purchaseOrders.length === 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">No Purchase Orders Available</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>There are no Purchase Orders available for ARO creation. Possible reasons:</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>No Purchase Orders have been created yet</li>
                                <li>Purchase Orders are not in 'Approved' or 'Delivered' status</li>
                                <li>All eligible Purchase Orders already have AROs</li>
                            </ul>
                            <p class="mt-2">Please create Purchase Orders and set their status to 'Approved' or 'Delivered', or check existing PO statuses.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <Card>
                <CardContent class="p-6">
                    <form @submit.prevent="submit" class="space-y-8">
                        
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <FileCheck class="w-5 h-5 mr-2 text-blue-600" />
                                    Basic Information
                                </h3>
                                <p class="text-sm text-gray-600">Enter the basic ARO details</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Purchase Order -->
                                <div class="space-y-2">
                                    <Label for="id_po" class="text-sm font-medium text-gray-700 flex items-center">
                                        <ClipboardList class="w-4 h-4 mr-1 text-purple-600" />
                                        Purchase Order *
                                    </Label>
                                    <select 
                                        id="id_po"
                                        v-model="form.id_po" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Select Purchase Order</option>
                                        <option v-for="po in purchaseOrders" :key="po.id_po" :value="po.id_po">
                                            {{ po.po_number }} - {{ po.customer?.company_name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.id_po" class="text-red-500 text-sm">
                                        {{ form.errors.id_po }}
                                    </div>
                                </div>

                                <!-- ARO Date -->
                                <div class="space-y-2">
                                    <Label for="date_aro" class="text-sm font-medium text-gray-700 flex items-center">
                                        <Calendar class="w-4 h-4 mr-1 text-green-600" />
                                        ARO Date *
                                    </Label>
                                    <Input
                                        id="date_aro"
                                        v-model="form.date_aro"
                                        type="date"
                                        required
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.date_aro" class="text-red-500 text-sm">
                                        {{ form.errors.date_aro }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Calendar class="w-5 h-5 mr-2 text-green-600" />
                                    Date Information
                                </h3>
                                <p class="text-sm text-gray-600">ARO will be created by the current logged-in user</p>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-blue-700 mb-2">
                                    <strong>Note:</strong> ARO number will be automatically generated in format: 
                                    <span class="font-mono">SNX-ARO-{{ new Date().getFullYear() }}-XXXXX</span>
                                </p>
                                <p class="text-sm text-blue-700">
                                    Creation date and user will be set automatically when you submit the form.
                                </p>
                            </div>
                        </div>



                        <!-- Products -->
                        <div v-if="selectedPO" class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Truck class="w-5 h-5 mr-2 text-orange-600" />
                                    Products
                                </h3>
                                <p class="text-sm text-gray-600">Enter received quantities for each product</p>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-for="(product, index) in form.products" :key="index" 
                                    class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-lg bg-gray-50">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-700">Product</Label>
                                        <div class="mt-1 text-sm text-gray-900">{{ product.product_name }}</div>
                                        <div class="text-xs text-gray-500">Code: {{ product.product_code }}</div>
                                        <div class="text-xs text-gray-500">Description: {{ product.description }}</div>
                                    </div>
                                    
                                    <div>
                                        <Label :for="'quantity_' + index" class="text-sm font-medium text-gray-700">
                                            Quantity Received *
                                        </Label>
                                        <Input 
                                            :id="'quantity_' + index"
                                            v-model="form.products[index].quantity_received"
                                            type="number"
                                            min="0"
                                            :max="product.quantity_ordered"
                                            required
                                            class="mt-1"
                                        />
                                        <div class="text-xs text-gray-500">
                                            Ordered: {{ product.quantity_ordered }}
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <Label :for="'remarks_' + index" class="text-sm font-medium text-gray-700">
                                            Product Remarks
                                        </Label>
                                        <textarea
                                            :id="'remarks_' + index"
                                            v-model="form.products[index].remarks"
                                            rows="2"
                                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md"
                                            placeholder="Any notes about this product..."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Remarks -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <MessageSquare class="w-5 h-5 mr-2 text-pink-600" />
                                    Status & Remarks
                                </h3>
                                <p class="text-sm text-gray-600">Set ARO status and add remarks</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label for="status" class="text-sm font-medium text-gray-700">Status *</Label>
                                    <select 
                                        id="status"
                                        v-model="form.status" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="Pending">🟡 Pending</option>
                                        <option value="Delivered">🟢 Delivered</option>
                                        <option value="Partially Delivered">🔵 Partially Delivered</option>
                                        <option value="Cancelled">🔴 Cancelled</option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-500 text-sm">
                                        {{ form.errors.status }}
                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div class="space-y-2">
                                    <Label for="remarks" class="text-sm font-medium text-gray-700">
                                        Remarks
                                    </Label>
                                    <textarea
                                        id="remarks"
                                        v-model="form.remarks"
                                        rows="3"
                                        placeholder="Additional notes or comments..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    ></textarea>
                                    <div v-if="form.errors.remarks" class="text-red-500 text-sm">
                                        {{ form.errors.remarks }}
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
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 flex items-center"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Creating ARO...' : 'Create ARO' }}
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