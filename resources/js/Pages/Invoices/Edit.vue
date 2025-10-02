<script setup>
import Layout from "../../Layout/App.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, watch, onMounted } from "vue";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import Textarea from "@/components/ui/textarea/Textarea.vue";
import { Receipt, Save, ArrowLeft, Calendar, User, FileText, DollarSign, Building2, AlertTriangle, CheckCircle, Info, Edit } from "lucide-vue-next";

const props = defineProps({
    invoice: Object,
    deliveryNotes: Array,
    users: Array,
    products: Array
});

// Alert state
const alert = ref({
    show: false,
    type: 'success',
    title: '',
    message: ''
});

// Show alert function
const showAlert = (type, title, message) => {
    alert.value = {
        show: true,
        type: type,
        title: title,
        message: message
    };
    
    if (type === 'success' || type === 'info') {
        setTimeout(() => {
            alert.value.show = false;
        }, 5000);
    }
};

// Hide alert function
const hideAlert = () => {
    alert.value.show = false;
};

// ✅ Form pre-filled with real invoice data
const form = useForm({
    invoice_number: props.invoice?.invoice_number || '',
    id_dnp: props.invoice?.id_dnp || '',
    customer_po_no: props.invoice?.customer_po_no || '',
    date_invoice: props.invoice?.date_invoice || '',
    payment_status: props.invoice?.payment_status || 'Always on time',
    date_payment: props.invoice?.date_payment || '',
    id_user: props.invoice?.id_user || '',
    currency: props.invoice?.currency || 'EUR',
    payment_terms: props.invoice?.payment_terms || 'Net 30',
    total_excl_vat: props.invoice?.total_excl_vat || '',
    vat_amount: props.invoice?.vat_amount || '',
    total_incl_vat: props.invoice?.total_incl_vat || '',
    supplier_vat_number: props.invoice?.supplier_vat_number || '',
    supplier_iso_certification: props.invoice?.supplier_iso_certification || '',
    supplier_contact_person: props.invoice?.supplier_contact_person || '',
    supplier_email: props.invoice?.supplier_email || '',
    supplier_phone: props.invoice?.supplier_phone || '',
    supplier_website: props.invoice?.supplier_website || '',
    customer_vat_number: props.invoice?.customer_vat_number || '',
    customer_contact_person: props.invoice?.customer_contact_person || '',
    customer_email: props.invoice?.customer_email || '',
    customer_phone: props.invoice?.customer_phone || '',
    needed_certifications: props.invoice?.needed_certifications || '',
    notes: props.invoice?.notes || ''
});

// Auto-calculate VAT and totals
const calculateTotals = () => {
    const excl = parseFloat(form.total_excl_vat) || 0;
    if (excl > 0) {
        const vat = excl * 0.21; // 21% VAT
        form.vat_amount = vat.toFixed(2);
        form.total_incl_vat = (excl + vat).toFixed(2);
        
        showAlert('info', 'VAT Calculated', `VAT (21%): ${vat.toFixed(2)} ${form.currency}`);
    } else {
        form.vat_amount = '';
        form.total_incl_vat = '';
    }
};

// ✅ Auto-fill customer data when delivery note is selected
const handleDeliveryNoteChange = () => {
    if (!form.id_dnp) {
        showAlert('warning', 'No Selection', 'Please select a delivery note');
        return;
    }

    const selectedDN = props.deliveryNotes.find(dn => dn.id_dnp == form.id_dnp);
    
    if (!selectedDN) {
        showAlert('error', 'Not Found', 'Selected delivery note not found');
        return;
    }

    const customer = selectedDN.aro?.purchase_order?.customer;
    
    if (customer) {
        form.customer_vat_number = customer.vat_number || '';
        form.customer_contact_person = customer.contact_name || '';
        form.customer_email = customer.email || '';
        form.customer_phone = customer.phone || '';
        
        showAlert('success', 'Customer Data Loaded', `Customer details loaded for ${customer.company_name}`);
    } else {
        showAlert('warning', 'No Customer Data', 'No customer information found for this delivery note');
    }
};

// Form validation before submit
const validateForm = () => {
    const errors = [];
    
    if (!form.invoice_number.trim()) {
        errors.push('Invoice number is required');
    }
    
    if (!form.id_dnp) {
        errors.push('Delivery note must be selected');
    }
    
    if (!form.date_invoice) {
        errors.push('Invoice date is required');
    }
    
    if (!form.id_user) {
        errors.push('User must be selected');
    }
    
    if (!form.currency) {
        errors.push('Currency is required');
    }
    
    if (!form.payment_status) {
        errors.push('Payment status is required');
    }
    
    // Date validation
    if (form.date_payment && form.date_invoice) {
        const invoiceDate = new Date(form.date_invoice);
        const paymentDate = new Date(form.date_payment);
        
        if (paymentDate < invoiceDate) {
            errors.push('Payment date cannot be before invoice date');
        }
    }
    
    // Amount validation
    if (form.total_excl_vat && parseFloat(form.total_excl_vat) < 0) {
        errors.push('Total amount cannot be negative');
    }
    
    if (errors.length > 0) {
        showAlert('error', 'Validation Errors', errors.join(', '));
        return false;
    }
    
    return true;
};

// ✅ Submit form to update invoice
const submit = () => {
    if (!validateForm()) {
        return;
    }
    
    form.put(`/invoices/${props.invoice.id_invoice}`, {
        onSuccess: () => {
            showAlert('success', 'Invoice Updated', 'Invoice has been updated successfully!');
            setTimeout(() => {
                window.location.href = '/invoices';
            }, 2000);
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            const errorMessages = Object.values(errors).flat().join(', ');
            showAlert('error', 'Update Failed', errorMessages);
        }
    });
};

const goBack = () => {
    if (form.isDirty) {
        if (confirm('You have unsaved changes. Are you sure you want to leave?')) {
            window.history.back();
        }
    } else {
        window.history.back();
    }
};

// ✅ Get customer name for display
const getCustomerName = () => {
    if (!props.invoice?.delivery_note?.aro?.purchase_order?.customer) {
        return 'Unknown Customer';
    }
    return props.invoice.delivery_note.aro.purchase_order.customer.company_name;
};

// ✅ Get delivery note display name
const getDeliveryNoteDisplay = (deliveryNote) => {
    if (!deliveryNote) return 'Unknown';
    const customerName = deliveryNote.aro?.purchase_order?.customer?.company_name || 'Unknown';
    return `${deliveryNote.dnp_number} - ${customerName}`;
};

// Show success message on mount
onMounted(() => {
    showAlert('info', 'Edit Mode', `Editing invoice ${props.invoice?.invoice_number || 'Unknown'} for ${getCustomerName()}`);
});
</script>

<template>
    <Layout>
        <div class="max-w-6xl mx-auto p-6 space-y-6">
            <!-- Alert Component -->
            <div v-if="alert.show" class="fixed top-4 right-4 z-50 max-w-md">
                <div 
                    :class="{
                        'bg-green-50 border-green-200 text-green-800': alert.type === 'success',
                        'bg-red-50 border-red-200 text-red-800': alert.type === 'error', 
                        'bg-yellow-50 border-yellow-200 text-yellow-800': alert.type === 'warning',
                        'bg-blue-50 border-blue-200 text-blue-800': alert.type === 'info'
                    }"
                    class="border rounded-lg p-4 shadow-lg animate-slide-in"
                >
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <CheckCircle v-if="alert.type === 'success'" class="w-5 h-5 text-green-600" />
                            <AlertTriangle v-else-if="alert.type === 'error'" class="w-5 h-5 text-red-600" />
                            <AlertTriangle v-else-if="alert.type === 'warning'" class="w-5 h-5 text-yellow-600" />
                            <Info v-else-if="alert.type === 'info'" class="w-5 h-5 text-blue-600" />
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium">{{ alert.title }}</h3>
                            <p class="mt-1 text-sm">{{ alert.message }}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button 
                                @click="hideAlert"
                                class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="{
                                    'text-green-500 hover:bg-green-100 focus:ring-green-600': alert.type === 'success',
                                    'text-red-500 hover:bg-red-100 focus:ring-red-600': alert.type === 'error',
                                    'text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-600': alert.type === 'warning',
                                    'text-blue-500 hover:bg-blue-100 focus:ring-blue-600': alert.type === 'info'
                                }"
                            >
                                <span class="sr-only">Dismiss</span>
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <Edit class="w-8 h-8 mr-3 text-orange-600" />
                        Edit Invoice
                    </h1>
                    <p class="text-gray-600 mt-1">Update invoice {{ invoice?.invoice_number }} for {{ getCustomerName() }}</p>
                </div>
                <Button variant="outline" @click="goBack" class="flex items-center">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>
            </div>

            <!-- Current Invoice Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <Info class="w-5 h-5 text-blue-600 mr-2" />
                    <div>
                        <h3 class="text-sm font-medium text-blue-800">Current Invoice Information</h3>
                        <div class="mt-1 text-sm text-blue-700 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div><strong>Invoice #:</strong> {{ invoice?.invoice_number }}</div>
                            <div><strong>Customer:</strong> {{ getCustomerName() }}</div>
                            <div><strong>Total:</strong> {{ invoice?.total_incl_vat }} {{ invoice?.currency }}</div>
                            <div><strong>Status:</strong> {{ invoice?.payment_status }}</div>
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
                                    <Receipt class="w-5 h-5 mr-2 text-blue-600" />
                                    Basic Information
                                </h3>
                                <p class="text-sm text-gray-600">Update the basic invoice details</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Invoice Number -->
                                <div class="space-y-2">
                                    <Label for="invoice_number" class="text-sm font-medium text-gray-700">
                                        Invoice Number *
                                    </Label>
                                    <Input
                                        id="invoice_number"
                                        v-model="form.invoice_number"
                                        type="text"
                                        placeholder="INV-20241201-001"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.invoice_number }"
                                        required
                                    />
                                    <div v-if="form.errors.invoice_number" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.invoice_number }}
                                    </div>
                                </div>

                                <!-- Delivery Note -->
                                <div class="space-y-2">
                                    <Label for="id_dnp" class="text-sm font-medium text-gray-700 flex items-center">
                                        <FileText class="w-4 h-4 mr-1 text-purple-600" />
                                        Delivery Note *
                                    </Label>
                                    <select 
                                        id="id_dnp"
                                        v-model="form.id_dnp" 
                                        @change="handleDeliveryNoteChange"
                                        required
                                        :class="{ 'border-red-500': form.errors.id_dnp }"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Select Delivery Note</option>
                                        <option v-for="dn in deliveryNotes" :key="dn.id_dnp" :value="dn.id_dnp">
                                            {{ getDeliveryNoteDisplay(dn) }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.id_dnp" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.id_dnp }}
                                    </div>
                                </div>

                                <!-- Customer PO Number -->
                                <div class="space-y-2">
                                    <Label for="customer_po_no" class="text-sm font-medium text-gray-700">
                                        Customer PO Number
                                    </Label>
                                    <Input
                                        id="customer_po_no"
                                        v-model="form.customer_po_no"
                                        type="text"
                                        placeholder="PO-123456"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.customer_po_no }"
                                    />
                                    <div v-if="form.errors.customer_po_no" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.customer_po_no }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date & User Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Calendar class="w-5 h-5 mr-2 text-green-600" />
                                    Date & User Information
                                </h3>
                                <p class="text-sm text-gray-600">Update dates and responsible user</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Invoice Date -->
                                <div class="space-y-2">
                                    <Label for="date_invoice" class="text-sm font-medium text-gray-700">
                                        Invoice Date *
                                    </Label>
                                    <Input
                                        id="date_invoice"
                                        v-model="form.date_invoice"
                                        type="date"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.date_invoice }"
                                        required
                                    />
                                    <div v-if="form.errors.date_invoice" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.date_invoice }}
                                    </div>
                                </div>

                                <!-- Payment Date -->
                                <div class="space-y-2">
                                    <Label for="date_payment" class="text-sm font-medium text-gray-700">
                                        Payment Date
                                    </Label>
                                    <Input
                                        id="date_payment"
                                        v-model="form.date_payment"
                                        type="date"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.date_payment }"
                                    />
                                    <div v-if="form.errors.date_payment" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.date_payment }}
                                    </div>
                                </div>

                                <!-- Created By -->
                                <div class="space-y-2">
                                    <Label for="id_user" class="text-sm font-medium text-gray-700 flex items-center">
                                        <User class="w-4 h-4 mr-1 text-indigo-600" />
                                        Created By *
                                    </Label>
                                    <select 
                                        id="id_user"
                                        v-model="form.id_user" 
                                        required
                                        :class="{ 'border-red-500': form.errors.id_user }"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Select User</option>
                                        <option v-for="user in users" :key="user.id_user" :value="user.id_user">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.id_user" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.id_user }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <DollarSign class="w-5 h-5 mr-2 text-emerald-600" />
                                    Financial Information
                                </h3>
                                <p class="text-sm text-gray-600">Update amounts, currency and payment terms</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <!-- Currency -->
                                <div class="space-y-2">
                                    <Label for="currency" class="text-sm font-medium text-gray-700">Currency *</Label>
                                    <select 
                                        id="currency"
                                        v-model="form.currency" 
                                        required
                                        :class="{ 'border-red-500': form.errors.currency }"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="EUR">EUR (€)</option>
                                        <option value="USD">USD ($)</option>
                                        <option value="GBP">GBP (£)</option>
                                    </select>
                                    <div v-if="form.errors.currency" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.currency }}
                                    </div>
                                </div>

                                <!-- Total Excl VAT -->
                                <div class="space-y-2">
                                    <Label for="total_excl_vat" class="text-sm font-medium text-gray-700">
                                        Total Excl. VAT
                                    </Label>
                                    <Input
                                        id="total_excl_vat"
                                        v-model="form.total_excl_vat"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.total_excl_vat }"
                                        @input="calculateTotals"
                                    />
                                    <div v-if="form.errors.total_excl_vat" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.total_excl_vat }}
                                    </div>
                                </div>

                                <!-- VAT Amount -->
                                <div class="space-y-2">
                                    <Label for="vat_amount" class="text-sm font-medium text-gray-700">
                                        VAT Amount (21%)
                                    </Label>
                                    <Input
                                        id="vat_amount"
                                        v-model="form.vat_amount"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full bg-gray-50"
                                        :class="{ 'border-red-500': form.errors.vat_amount }"
                                        readonly
                                    />
                                    <div v-if="form.errors.vat_amount" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.vat_amount }}
                                    </div>
                                </div>

                                <!-- Total Incl VAT -->
                                <div class="space-y-2">
                                    <Label for="total_incl_vat" class="text-sm font-medium text-gray-700">
                                        Total Incl. VAT
                                    </Label>
                                    <Input
                                        id="total_incl_vat"
                                        v-model="form.total_incl_vat"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full bg-gray-50 font-bold"
                                        :class="{ 'border-red-500': form.errors.total_incl_vat }"
                                        readonly
                                    />
                                    <div v-if="form.errors.total_incl_vat" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.total_incl_vat }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Payment Status -->
                                <div class="space-y-2">
                                    <Label for="payment_status" class="text-sm font-medium text-gray-700">Payment Status *</Label>
                                    <select 
                                        id="payment_status"
                                        v-model="form.payment_status" 
                                        required
                                        :class="{ 'border-red-500': form.errors.payment_status }"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="Always on time">🟢 Always on time</option>
                                        <option value="Small delays">🟡 Small delays</option>
                                        <option value="Frequent big delays">🟠 Frequent big delays</option>
                                        <option value="No payment">🔴 No payment</option>
                                    </select>
                                    <div v-if="form.errors.payment_status" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.payment_status }}
                                    </div>
                                </div>

                                <!-- Payment Terms -->
                                <div class="space-y-2">
                                    <Label for="payment_terms" class="text-sm font-medium text-gray-700">
                                        Payment Terms
                                    </Label>
                                    <Input
                                        id="payment_terms"
                                        v-model="form.payment_terms"
                                        type="text"
                                        placeholder="Net 30"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.payment_terms }"
                                    />
                                    <div v-if="form.errors.payment_terms" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.payment_terms }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Building2 class="w-5 h-5 mr-2 text-orange-600" />
                                    Customer Information
                                </h3>
                                <p class="text-sm text-gray-600">Customer contact and billing details</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Customer VAT -->
                                <div class="space-y-2">
                                    <Label for="customer_vat_number" class="text-sm font-medium text-gray-700">
                                        Customer VAT Number
                                    </Label>
                                    <Input
                                        id="customer_vat_number"
                                        v-model="form.customer_vat_number"
                                        type="text"
                                        placeholder="FR12345678901"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.customer_vat_number }"
                                    />
                                    <div v-if="form.errors.customer_vat_number" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.customer_vat_number }}
                                    </div>
                                </div>

                                <!-- Customer Contact Person -->
                                <div class="space-y-2">
                                    <Label for="customer_contact_person" class="text-sm font-medium text-gray-700">
                                        Customer Contact Person
                                    </Label>
                                    <Input
                                        id="customer_contact_person"
                                        v-model="form.customer_contact_person"
                                        type="text"
                                        placeholder="John Doe"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.customer_contact_person }"
                                    />
                                    <div v-if="form.errors.customer_contact_person" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.customer_contact_person }}
                                    </div>
                                </div>

                                <!-- Customer Email -->
                                <div class="space-y-2">
                                    <Label for="customer_email" class="text-sm font-medium text-gray-700">
                                        Customer Email
                                    </Label>
                                    <Input
                                        id="customer_email"
                                        v-model="form.customer_email"
                                        type="email"
                                        placeholder="customer@example.com"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.customer_email }"
                                    />
                                    <div v-if="form.errors.customer_email" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.customer_email }}
                                    </div>
                                </div>

                                <!-- Customer Phone -->
                                <div class="space-y-2">
                                    <Label for="customer_phone" class="text-sm font-medium text-gray-700">
                                        Customer Phone
                                    </Label>
                                    <Input
                                        id="customer_phone"
                                        v-model="form.customer_phone"
                                        type="text"
                                        placeholder="+33 1 23 45 67 89"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.customer_phone }"
                                    />
                                    <div v-if="form.errors.customer_phone" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.customer_phone }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                                <p class="text-sm text-gray-600">Supplier details and notes</p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Supplier VAT -->
                                <div class="space-y-2">
                                    <Label for="supplier_vat_number" class="text-sm font-medium text-gray-700">
                                        Supplier VAT Number
                                    </Label>
                                    <Input
                                        id="supplier_vat_number"
                                        v-model="form.supplier_vat_number"
                                        type="text"
                                        placeholder="FR98765432109"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.supplier_vat_number }"
                                    />
                                    <div v-if="form.errors.supplier_vat_number" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.supplier_vat_number }}
                                    </div>
                                </div>

                                <!-- Supplier ISO Certification -->
                                <div class="space-y-2">
                                    <Label for="supplier_iso_certification" class="text-sm font-medium text-gray-700">
                                        Supplier ISO Certification
                                    </Label>
                                    <Input
                                        id="supplier_iso_certification"
                                        v-model="form.supplier_iso_certification"
                                        type="text"
                                        placeholder="ISO 9001:2015"
                                        class="w-full"
                                        :class="{ 'border-red-500': form.errors.supplier_iso_certification }"
                                    />
                                    <div v-if="form.errors.supplier_iso_certification" class="text-red-500 text-sm flex items-center">
                                        <AlertTriangle class="w-4 h-4 mr-1" />
                                        {{ form.errors.supplier_iso_certification }}
                                    </div>
                                </div>
                            </div>

                            <!-- Notes (Full Width) -->
                            <div class="space-y-2">
                                <Label for="notes" class="text-sm font-medium text-gray-700">
                                    Notes
                                </Label>
                                <Textarea
                                    id="notes"
                                    v-model="form.notes"
                                    placeholder="Additional notes about this invoice..."
                                    rows="4"
                                    class="w-full"
                                    :class="{ 'border-red-500': form.errors.notes }"
                                />
                                <div v-if="form.errors.notes" class="text-red-500 text-sm flex items-center">
                                    <AlertTriangle class="w-4 h-4 mr-1" />
                                    {{ form.errors.notes }}
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
                                class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-2 flex items-center"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Updating Invoice...' : 'Update Invoice' }}
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
    ring: 2px;
    ring-color: #3b82f6;
    border-color: #3b82f6;
}

/* Loading state for submit button */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Alert animation */
@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}

/* Error state styles */
.border-red-500 {
    border-color: #ef4444 !important;
}
</style>