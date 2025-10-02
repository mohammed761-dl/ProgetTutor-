<script setup>
// filepath: resources/js/Pages/ARO/Edit.vue

import Layout from "../../Layout/App.vue";
import { useForm } from "@inertiajs/vue3";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import { FileCheck, Save, ArrowLeft, Calendar, User, ClipboardList, Truck, MessageSquare } from "lucide-vue-next";

const props = defineProps({
    aro: Object,
    statuses: Array
});

const form = useForm({
    status: props.aro.status || 'Pending',
    planned_delivery_date: props.aro.planned_delivery_date || '',
    actual_delivery_date: props.aro.actual_delivery_date || '',
    remarks: props.aro.remarks || '',
    products: props.aro.products || []
});

const submit = () => {
    form.put(`/aro/${props.aro.id_aro}`, {
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
                        <FileCheck class="w-8 h-8 mr-3 text-blue-600" />
                        Edit ARO: {{ props.aro.aro_number }}
                    </h1>
                    <p class="text-gray-600 mt-1">Update ARO details</p>
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
                        
                        <!-- ARO Information (Read-only) -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <FileCheck class="w-5 h-5 mr-2 text-blue-600" />
                                    ARO Information
                                </h3>
                                <p class="text-sm text-gray-600">ARO details (cannot be changed)</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- ARO Number -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-gray-700">ARO Number</Label>
                                    <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-700 font-mono">
                                        {{ props.aro.aro_number }}
                                    </div>
                                </div>

                                <!-- Purchase Order -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-gray-700 flex items-center">
                                        <ClipboardList class="w-4 h-4 mr-1 text-purple-600" />
                                        Purchase Order
                                    </Label>
                                    <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-700">
                                        {{ props.aro.purchase_order?.po_number }} - {{ props.aro.purchase_order?.customer?.company_name }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Information (Read-only) -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <User class="w-5 h-5 mr-2 text-indigo-600" />
                                    User Information
                                </h3>
                                <p class="text-sm text-gray-600">Creator and date information (cannot be changed)</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- ARO Date -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-gray-700">ARO Creation Date</Label>
                                    <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-700">
                                        {{ props.aro.date_aro ? new Date(props.aro.date_aro).toLocaleDateString() : 'N/A' }}
                                    </div>
                                </div>

                                <!-- Created By -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-gray-700">Created By</Label>
                                    <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-700">
                                        {{ props.aro.creator?.name || 'Unknown User' }}
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Products -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Truck class="w-5 h-5 mr-2 text-orange-600" />
                                    Products
                                </h3>
                                <p class="text-sm text-gray-600">Update received quantities for each product</p>
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

                        <!-- Status, Dates & Remarks -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <MessageSquare class="w-5 h-5 mr-2 text-pink-600" />
                                    Status, Delivery Dates & Remarks
                                </h3>
                                <p class="text-sm text-gray-600">Update ARO status, delivery dates, and remarks</p>
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

                                <!-- Planned Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="planned_delivery_date" class="text-sm font-medium text-gray-700">
                                        <Calendar class="w-4 h-4 mr-1 inline text-green-600" /> Planned Delivery Date
                                    </Label>
                                    <Input
                                        id="planned_delivery_date"
                                        v-model="form.planned_delivery_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.planned_delivery_date" class="text-red-500 text-sm">
                                        {{ form.errors.planned_delivery_date }}
                                    </div>
                                </div>

                                <!-- Actual Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="actual_delivery_date" class="text-sm font-medium text-gray-700">
                                        <Calendar class="w-4 h-4 mr-1 inline text-blue-600" /> Actual Delivery Date
                                    </Label>
                                    <Input
                                        id="actual_delivery_date"
                                        v-model="form.actual_delivery_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.actual_delivery_date" class="text-red-500 text-sm">
                                        {{ form.errors.actual_delivery_date }}
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
                                {{ form.processing ? 'Updating ARO...' : 'Update ARO' }}
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