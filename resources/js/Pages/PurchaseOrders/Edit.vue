<script setup>
// filepath: resources/js/Pages/PurchaseOrders/Edit.vue

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
import Textarea from "@/components/ui/textarea/Textarea.vue";
import { FileText, Save, ArrowLeft, Calendar, User, Building2, FileCheck } from "lucide-vue-next";

const props = defineProps({
    purchaseOrder: Object,
    customers: Array,
    users: Array,
    quotes: Array,
    products: Array
});

const form = useForm({
    id_customer: props.purchaseOrder.id_customer || '',
    id_quote: props.purchaseOrder.id_quote || '',
    status: props.purchaseOrder.status || 'Pending',
    planned_delivery_date: props.purchaseOrder.planned_delivery_date || '',
    actual_delivery_date: props.purchaseOrder.actual_delivery_date || '',
    remarks: props.purchaseOrder.remarks || ''
});

// Auto-fill customer when quote is selected (optional)
const handleQuoteChange = () => {
    if (form.id_quote) {
        const selectedQuote = props.quotes.find(q => q.id_quote == form.id_quote);
        if (selectedQuote) {
            form.id_customer = selectedQuote.id_customer;
        }
    }
};

const submit = () => {
    form.put(`/po/${props.purchaseOrder.id_po}`, {
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
                        <FileText class="w-8 h-8 mr-3 text-blue-600" />
                        Edit Purchase Order: {{ props.purchaseOrder.po_number }}
                    </h1>
                    <p class="text-gray-600 mt-1">Update purchase order details</p>
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
                                    <FileText class="w-5 h-5 mr-2 text-blue-600" />
                                    Basic Information
                                </h3>
                                <p class="text-sm text-gray-600">Update the basic purchase order details</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- PO Number (Read-only) -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-gray-700">
                                        PO Number (Auto-generated)
                                    </Label>
                                    <Input
                                        :value="props.purchaseOrder.po_number"
                                        disabled
                                        class="bg-gray-100 text-gray-500 cursor-not-allowed"
                                    />
                                    <p class="text-xs text-gray-500">PO number cannot be changed</p>
                                </div>

                                <!-- Quote Reference (Optional) -->
                                <div class="space-y-2">
                                    <Label for="id_quote" class="text-sm font-medium text-gray-700">
                                        Quote Reference (Optional)
                                    </Label>
                                    <select
                                        id="id_quote"
                                        v-model="form.id_quote"
                                        @change="handleQuoteChange"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    >
                                        <option value="">Select a quote (optional)</option>
                                        <option v-for="quote in quotes" :key="quote.id_quote" :value="quote.id_quote">
                                            {{ quote.quote_number }} - {{ quote.customer?.company_name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.id_quote" class="text-red-500 text-sm">
                                        {{ form.errors.id_quote }}
                                    </div>
                                </div>

                                <!-- Customer -->
                                <div class="space-y-2">
                                    <Label for="id_customer" class="text-sm font-medium text-gray-700">
                                        Customer *
                                    </Label>
                                    <select
                                        id="id_customer"
                                        v-model="form.id_customer"
                                        required
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    >
                                        <option value="">Select a customer</option>
                                        <option v-for="customer in customers" :key="customer.id_customer" :value="customer.id_customer">
                                            {{ customer.company_name }} - {{ customer.contact_name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.id_customer" class="text-red-500 text-sm">
                                        {{ form.errors.id_customer }}
                                    </div>
                                </div>

                                <!-- Created By (Read-only) -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-gray-700">
                                        Created By
                                    </Label>
                                    <Input
                                        :value="props.purchaseOrder.creator?.name || 'Unknown User'"
                                        disabled
                                        class="bg-gray-100 text-gray-700 cursor-not-allowed"
                                    />
                                    <p class="text-xs text-gray-500">Cannot be changed</p>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Delivery -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Calendar class="w-5 h-5 mr-2 text-green-600" />
                                    Status & Delivery Information
                                </h3>
                                <p class="text-sm text-gray-600">Update status and delivery dates</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label for="status" class="text-sm font-medium text-gray-700">
                                        Status *
                                    </Label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    >
                                        <option value="Pending">Pending</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Delivered">Delivered</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-500 text-sm">
                                        {{ form.errors.status }}
                                    </div>
                                </div>

                                <!-- Planned Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="planned_delivery_date" class="text-sm font-medium text-gray-700">
                                        Planned Delivery Date
                                    </Label>
                                    <Input
                                        type="date"
                                        id="planned_delivery_date"
                                        v-model="form.planned_delivery_date"
                                        class="text-sm"
                                    />
                                    <div v-if="form.errors.planned_delivery_date" class="text-red-500 text-sm">
                                        {{ form.errors.planned_delivery_date }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                                <p class="text-sm text-gray-600">Add notes and remarks</p>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="remarks" class="text-sm font-medium text-gray-700">
                                    Remarks
                                </Label>
                                <Textarea
                                    id="remarks"
                                    v-model="form.remarks"
                                    rows="3"
                                    class="w-full text-sm"
                                    placeholder="Add any additional notes or remarks..."
                                />
                                <div v-if="form.errors.remarks" class="text-red-500 text-sm">
                                    {{ form.errors.remarks }}
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
                                {{ form.processing ? 'Updating PO...' : 'Update Purchase Order' }}
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
    border-color: #3b82f6;
}

/* Loading state for submit button */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>