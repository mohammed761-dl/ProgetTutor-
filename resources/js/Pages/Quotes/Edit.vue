<script setup>
/**
 * Quote Edit Page
 * Frontend only handles data input and display
 * All calculations performed by backend via API calls
 */
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
import Textarea from "@/components/ui/textarea/Textarea.vue";
import Table from "@/components/ui/table/Table.vue";
import TableHeader from "@/components/ui/table/TableHeader.vue";
import TableBody from "@/components/ui/table/TableBody.vue";
import TableRow from "@/components/ui/table/TableRow.vue";
import TableCell from "@/components/ui/table/TableCell.vue";
import TableHead from "@/components/ui/table/TableHead.vue";
import { 
    FileText, 
    Save, 
    ArrowLeft, 
    Calendar, 
    User, 
    Building2, 
    DollarSign, 
    Plus, 
    Package, 
    Calculator,
    Trash2,
    AlertTriangle,
    CheckCircle,
    X,
    Loader2
} from "lucide-vue-next";
import { ref, computed, onMounted, watch } from "vue";

const props = defineProps({
    quote: Object,
    customers: Array,
    users: Array,
    products: Array,
    currentUser: Object // Add current user prop
});

const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('error');
const isSubmitting = ref(false);
const validationErrors = ref([]);

const showNotification = (message, type = 'info', duration = 5000) => {
    alertMessage.value = message;
    alertType.value = type;
    showAlert.value = true;
    
    if (duration > 0) {
        setTimeout(() => {
            showAlert.value = false;
        }, duration);
    }
};

const closeAlert = () => {
    showAlert.value = false;
};

const calculateValidUntil = (quoteDate) => {
    if (!quoteDate) return '';
    const date = new Date(quoteDate);
    date.setDate(date.getDate() + 30);
    return date.toISOString().split('T')[0];
};

const existingProducts = props.quote.products ? props.quote.products.map(product => ({
    id_product: product.id_product,
    name: product.name,
    quantity: product.pivot.quantity,
    unit_price: parseFloat(product.pivot.unit_price),
    line_total: product.pivot.quantity * parseFloat(product.pivot.unit_price)
})) : [];

const form = useForm({
    id_customer: props.quote.id_customer || '',
    id_user: props.quote.id_user || '',
    date_quote: props.quote.date_quote || '',
    valid_until: props.quote.valid_until || '',
    status: props.quote.status || 'Sent same day',
    currency: props.quote.currency || 'EUR',
    payment_terms: props.quote.payment_terms || '',
    delivery_terms: props.quote.delivery_terms || '',
    discount_notes: props.quote.discount_notes || '',
    reduction: props.quote.reduction || 0,
    vat_rate: props.quote.vat_rate || 0.20,
    signature_name: props.quote.signature_name || '',
    signature_title: props.quote.signature_title || '',
    products: existingProducts
});

const selectedProduct = ref('');
const selectedQuantity = ref(1);
const selectedUnitPrice = ref(0);

const totals = ref({
    total_amount: 0,
    reduction: 0,
    vat: 0,
    total_ttc: 0
});

const vatRate = ref(props.quote.vat_rate ? (props.quote.vat_rate * 100) : 20);

onMounted(async () => {
    if (form.products.length > 0) {
        await calculateTotals();
    }
});

watch(() => form.date_quote, (newDate) => {
    if (newDate) {
        form.valid_until = calculateValidUntil(newDate);
    }
});

watch([() => form.products, () => form.reduction, () => vatRate.value, () => form.currency], async () => {
    await calculateTotals();
}, { deep: true });

// Add product to quote
const addProduct = () => {
    // Clear previous validation errors
    validationErrors.value = [];
    
    // Validate product selection
    if (!selectedProduct.value) {
        showNotification('Please select a product from the dropdown', 'error');
        return;
    }
    
    if (selectedQuantity.value <= 0) {
        showNotification('Quantity must be greater than 0', 'error');
        return;
    }
    
    if (selectedUnitPrice.value <= 0) {
        showNotification('Unit price must be greater than 0', 'error');
        return;
    }
    
    const product = props.products.find(p => p.id_product == selectedProduct.value);
    if (!product) {
        showNotification('Selected product not found. Please refresh the page and try again.', 'error');
        return;
    }
    
    // Check if product already exists
    const existingIndex = form.products.findIndex(p => p.id_product == selectedProduct.value);
    
    if (existingIndex !== -1) {
        // Update existing product
        form.products[existingIndex].quantity = selectedQuantity.value;
        // 🎯 SNAPSHOT: Preserve original unit_price, don't update from current product price
        form.products[existingIndex].unit_price = selectedUnitPrice.value;
        form.products[existingIndex].line_total = selectedQuantity.value * selectedUnitPrice.value;
        showNotification(`Updated ${product.name} in the quote`, 'success', 3000);
    } else {
        // Add new product
        form.products.push({
            id_product: parseInt(selectedProduct.value),
            name: product.name,
            quantity: selectedQuantity.value,
            // 🎯 SNAPSHOT: Capture current unit_price from product at addition time
            unit_price: selectedUnitPrice.value,
            line_total: selectedQuantity.value * selectedUnitPrice.value
        });
        showNotification(`Added ${product.name} to the quote`, 'success', 3000);
    }
    
    // Reset selection
    selectedProduct.value = '';
    selectedQuantity.value = 1;
    selectedUnitPrice.value = 0;
};

// Remove product from quote
const removeProduct = (index) => {
    if (index >= 0 && index < form.products.length) {
        const productName = form.products[index].name;
        form.products.splice(index, 1);
        showNotification(`Removed ${productName} from the quote`, 'info', 3000);
    }
};

// Update product when selected from dropdown
const onProductChange = () => {
    if (selectedProduct.value) {
        const product = props.products.find(p => p.id_product == selectedProduct.value);
        if (product) {
            // 🎯 SNAPSHOT LOGIC: Use current unit price from product (for new additions only)
            selectedUnitPrice.value = parseFloat(product.unit_price || 0);
        }
    }
};

// Validate product inputs in real-time
const validateProductInputs = () => {
    // This function can be used for real-time validation feedback
    // Currently handled by reactive validation in template
};

// Calculate financial totals - Frontend only calculates line totals, backend does the rest
const calculateTotals = async () => {
    // Reset totals first
    totals.value = {
        total_amount: 0,
        reduction: 0,
        vat: 0,
        total_ttc: 0
    };

    if (form.products.length === 0) {
        return;
    }

    // Frontend only calculates line totals for display
    form.products.forEach(product => {
        if (product.quantity && product.unit_price) {
            const lineTotal = parseFloat(product.quantity) * parseFloat(product.unit_price);
            product.line_total = lineTotal;
        }
    });

    try {
        // Send data to backend for calculation
        const products = form.products.map(p => ({
            quantity: parseInt(p.quantity),
            unit_price: parseFloat(p.unit_price)
        }));
        
        const response = await axios.post('/Quote/calculate-totals', {
            products: products,
            reduction: parseFloat(form.reduction || 0),
            vat_rate: parseFloat(vatRate.value) / 100
        });
        
        // Use backend calculation results
        totals.value = {
            total_amount: parseFloat(response.data.total_amount),
            reduction: parseFloat(response.data.reduction),
            vat: parseFloat(response.data.vat),
            total_ttc: parseFloat(response.data.total_ttc)
        };
    } catch (error) {
        console.error('Failed to calculate totals:', error);
        showNotification('Failed to calculate totals. Please try again.', 'error', 4000);
        // Show error to user but don't calculate fallback
        totals.value = {
            total_amount: 0,
            reduction: 0,
            vat: 0,
            total_ttc: 0
        };
    }
};

// Computed properties for line totals
const updateLineTotal = (product) => {
    product.line_total = product.quantity * product.unit_price;
};

// Computed property to check if form is valid
const isFormValid = computed(() => {
    // Basic required fields
    const hasRequiredFields = form.id_customer && 
                             form.id_user && 
                             form.date_quote && 
                             form.valid_until && 
                             form.status && 
                             form.currency;
    
    // Has products
    const hasProducts = form.products.length > 0;
    
    // All products have valid data
    const productsValid = form.products.every(product => 
        product.quantity > 0 && product.unit_price > 0
    );
    
    // Date validation
    let datesValid = true;
    if (form.date_quote && form.valid_until) {
        const quoteDate = new Date(form.date_quote);
        const validUntilDate = new Date(form.valid_until);
        datesValid = validUntilDate > quoteDate;
    }
    
    // Reduction validation
    const reductionValid = form.reduction >= 0;
    const totalHT = form.products.reduce((sum, p) => sum + (p.quantity * p.unit_price), 0);
    const reductionNotTooHigh = form.reduction <= totalHT;
    
    return hasRequiredFields && hasProducts && productsValid && datesValid && reductionValid && reductionNotTooHigh;
});

// Form validation function
const validateForm = () => {
    const errors = [];
    
    // Required field validation
    if (!form.id_customer) {
        errors.push('Customer is required');
    }
    
    if (!form.id_user) {
        errors.push('Created By user is required');
    }
    
    if (!form.date_quote) {
        errors.push('Quote date is required');
    }
    
    if (!form.valid_until) {
        errors.push('Valid until date is required');
    }
    
    if (!form.status) {
        errors.push('Status is required');
    }
    
    if (!form.currency) {
        errors.push('Currency is required');
    }
    
    // Products validation
    if (form.products.length === 0) {
        errors.push('At least one product is required');
    }
    
    // Product line validation
    form.products.forEach((product, index) => {
        if (!product.quantity || product.quantity <= 0) {
            errors.push(`Product "${product.name}" must have a quantity greater than 0`);
        }
        if (!product.unit_price || product.unit_price <= 0) {
            errors.push(`Product "${product.name}" must have a unit price greater than 0`);
        }
    });
    
    // Date validation
    if (form.date_quote && form.valid_until) {
        const quoteDate = new Date(form.date_quote);
        const validUntilDate = new Date(form.valid_until);
        
        if (validUntilDate <= quoteDate) {
            errors.push('Valid until date must be after the quote date');
        }
    }
    
    // Reduction validation
    if (form.reduction < 0) {
        errors.push('Reduction cannot be negative');
    }
    
    const totalHT = form.products.reduce((sum, p) => sum + (p.quantity * p.unit_price), 0);
    if (form.reduction > totalHT) {
        errors.push('Reduction cannot be greater than the subtotal');
    }
    
    validationErrors.value = errors;
    return errors.length === 0;
};

const submit = () => {
    closeAlert();
    validationErrors.value = [];
    
    if (!validateForm()) {
        showNotification(`Please fix the following errors:\n• ${validationErrors.value.join('\n• ')}`, 'error', 0);
        return;
    }
    
    isSubmitting.value = true;
    showNotification('Updating quote...', 'info', 0);
    
    form.vat_rate = parseFloat(vatRate.value) / 100;
    
    form.put(`/Quote/${props.quote.id_quote}`, {
        onStart: () => {
            isSubmitting.value = true;
        },
        onSuccess: (page) => {
            isSubmitting.value = false;
            showNotification('Quote updated successfully!', 'success', 3000);
            // The redirect will be handled by the controller
        },
        onError: (errors) => {
            isSubmitting.value = false;
            console.error('Validation errors:', errors);
            
            // Handle different types of errors
            if (errors.error) {
                // Single error message (user-friendly from controller)
                showNotification(errors.error, 'error', 0);
            } else if (errors.message) {
                // Laravel error message
                showNotification(`Error: ${errors.message}`, 'error', 0);
            } else {
                // Multiple validation errors
                const errorMessages = Object.values(errors).flat();
                if (errorMessages.length > 0) {
                    showNotification(`Please fix the following:\n• ${errorMessages.join('\n• ')}`, 'error', 0);
                } else {
                    showNotification('An unexpected error occurred. Please try again.', 'error', 0);
                }
            }
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Layout>
        <div class="max-w-6xl mx-auto p-6 space-y-6">
            <!-- Alert Component -->
            <div v-if="showAlert" class="fixed top-4 right-4 z-50 max-w-md w-full">
                <div :class="[
                    'p-4 rounded-lg shadow-lg border-l-4 flex items-start space-x-3',
                    alertType === 'success' ? 'bg-green-50 border-green-500 text-green-800' : '',
                    alertType === 'error' ? 'bg-red-50 border-red-500 text-red-800' : '',
                    alertType === 'warning' ? 'bg-yellow-50 border-yellow-500 text-yellow-800' : '',
                    alertType === 'info' ? 'bg-blue-50 border-blue-500 text-blue-800' : ''
                ]">
                    <!-- Alert Icon -->
                    <div class="flex-shrink-0">
                        <CheckCircle v-if="alertType === 'success'" class="w-5 h-5" />
                        <AlertTriangle v-else-if="alertType === 'error'" class="w-5 h-5" />
                        <AlertTriangle v-else-if="alertType === 'warning'" class="w-5 h-5" />
                        <FileText v-else class="w-5 h-5" />
                    </div>
                    
                    <!-- Alert Message -->
                    <div class="flex-1">
                        <p class="text-sm font-medium whitespace-pre-line">{{ alertMessage }}</p>
                    </div>
                    
                    <!-- Close Button -->
                    <button @click="closeAlert" class="flex-shrink-0">
                        <X class="w-4 h-4 hover:opacity-70" />
                    </button>
                </div>
            </div>

            <!-- Validation Errors Display -->
            <div v-if="validationErrors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <AlertTriangle class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" />
                    <div>
                        <h3 class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</h3>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li v-for="error in validationErrors" :key="error" class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>{{ error }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <FileText class="w-8 h-8 mr-3 text-blue-600" />
                        Edit Quote: {{ props.quote.quote_number }}
                    </h1>
                    <p class="text-gray-600 mt-1">Update customer quotation details</p>
                </div>
                <Button variant="outline" @click="goBack" class="flex items-center">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <FileText class="w-5 h-5 mr-2 text-blue-600" />
                            Basic Information
                        </CardTitle>
                        <CardDescription>Update the basic quote details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Customer -->
                            <div class="space-y-2">
                                <Label for="id_customer" class="text-sm font-medium text-gray-700 flex items-center">
                                    <Building2 class="w-4 h-4 mr-1 text-purple-600" />
                                    Customer *
                                </Label>
                                <select 
                                    id="id_customer"
                                    v-model="form.id_customer" 
                                    required
                                    :class="[
                                        'w-full px-3 py-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                                        form.errors.id_customer ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                    ]"
                                >
                                    <option value="">Select Customer</option>
                                    <option v-for="customer in customers" :key="customer.id_customer" :value="customer.id_customer">
                                        {{ customer.company_name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_customer" class="text-red-500 text-sm flex items-center">
                                    <AlertTriangle class="w-4 h-4 mr-1" />
                                    {{ form.errors.id_customer }}
                                </div>
                            </div>

                            <!-- Created By -->
                            <div class="space-y-2">
                                <Label for="id_user" class="text-sm font-medium text-gray-700 flex items-center">
                                    <User class="w-4 h-4 mr-1 text-indigo-600" />
                                    Created By *
                                </Label>
                                <div class="relative">
                                    <Input 
                                        id="id_user"
                                        :value="props.quote.user?.name || 'Unknown User'"
                                        readonly
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm cursor-not-allowed text-gray-600"
                                        placeholder="Quote Creator"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <User class="w-4 h-4 text-gray-400" />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">Quote creator cannot be changed during editing</p>
                                <!-- Hidden input for form submission -->
                                <input type="hidden" v-model="form.id_user" />
                                <div v-if="form.errors.id_user" class="text-red-500 text-sm flex items-center">
                                    <AlertTriangle class="w-4 h-4 mr-1" />
                                    {{ form.errors.id_user }}
                                </div>
                            </div>

                            <!-- Quote Date -->
                            <div class="space-y-2">
                                <Label for="date_quote" class="text-sm font-medium text-gray-700 flex items-center">
                                    <Calendar class="w-4 h-4 mr-1 text-green-600" />
                                    Quote Date *
                                </Label>
                                <Input
                                    id="date_quote"
                                    v-model="form.date_quote"
                                    type="date"
                                    :class="[
                                        'w-full',
                                        form.errors.date_quote ? 'border-red-500 bg-red-50' : ''
                                    ]"
                                    required
                                />
                                <div v-if="form.errors.date_quote" class="text-red-500 text-sm flex items-center">
                                    <AlertTriangle class="w-4 h-4 mr-1" />
                                    {{ form.errors.date_quote }}
                                </div>
                            </div>

                            <!-- Valid Until -->
                            <div class="space-y-2">
                                <Label for="valid_until" class="text-sm font-medium text-gray-700">
                                    Valid Until *
                                </Label>
                                <Input
                                    id="valid_until"
                                    v-model="form.valid_until"
                                    type="date"
                                    :class="[
                                        'w-full',
                                        form.errors.valid_until ? 'border-red-500 bg-red-50' : ''
                                    ]"
                                    required
                                />
                                <div v-if="form.errors.valid_until" class="text-red-500 text-sm flex items-center">
                                    <AlertTriangle class="w-4 h-4 mr-1" />
                                    {{ form.errors.valid_until }}
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
                                    <option value="Sent same day">🟢 Sent same day</option>
                                    <option value="Sent within 2-3 days">🟡 Sent within 2-3 days</option>
                                    <option value="Sent after 4+ days">🔴 Sent after 4+ days</option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-sm">
                                    {{ form.errors.status }}
                                </div>
                            </div>

                            <!-- Currency -->
                            <div class="space-y-2">
                                <Label for="currency" class="text-sm font-medium text-gray-700">Currency *</Label>
                                <select 
                                    id="currency"
                                    v-model="form.currency" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="EUR">🇪🇺 EUR - Euro</option>
                                    <option value="USD">🇺🇸 USD - US Dollar</option>
                                    <option value="MAD">🇲🇦 MAD - Moroccan Dirham</option>
                                </select>
                                <div v-if="form.errors.currency" class="text-red-500 text-sm">
                                    {{ form.errors.currency }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Product Management -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Package class="w-5 h-5 mr-2 text-orange-600" />
                            Manage Products
                        </CardTitle>
                        <CardDescription>Update products and quantities for this quote</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Add New Product Section -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                            <!-- Product Selection -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Add Product</Label>
                                <select 
                                    v-model="selectedProduct"
                                    @change="onProductChange"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Select Product</option>
                                    <option v-for="product in products" :key="product.id_product" :value="product.id_product">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Quantity</Label>
                                <Input
                                    v-model.number="selectedQuantity"
                                    type="number"
                                    min="1"
                                    class="w-full"
                                    @input="validateProductInputs"
                                />
                                <div v-if="selectedQuantity <= 0 && selectedProduct" class="text-red-500 text-xs">
                                    Quantity must be greater than 0
                                </div>
                            </div>

                            <!-- Unit Price -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Unit Price</Label>
                                <Input
                                    v-model.number="selectedUnitPrice"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full"
                                    @input="validateProductInputs"
                                />
                                <div v-if="selectedUnitPrice <= 0 && selectedProduct" class="text-red-500 text-xs">
                                    Price must be greater than 0
                                </div>
                            </div>

                            <!-- Add Button -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">&nbsp;</Label>
                                <Button 
                                    type="button"
                                    @click="addProduct"
                                    :disabled="!selectedProduct || selectedQuantity <= 0 || selectedUnitPrice <= 0"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <Plus class="w-4 h-4 mr-2" />
                                    Add
                                </Button>
                            </div>
                        </div>

                        <!-- Current Products Table -->
                        <div v-if="form.products.length > 0" class="border rounded-lg overflow-hidden">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Quantity</TableHead>
                                        <TableHead>Unit Price</TableHead>
                                        <TableHead>Line Total</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(product, index) in form.products" :key="index">
                                        <TableCell>{{ product.name }}</TableCell>
                                        <TableCell>
                                            <Input
                                                v-model.number="product.quantity"
                                                @input="updateLineTotal(product)"
                                                type="number"
                                                min="1"
                                                class="w-20"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                v-model.number="product.unit_price"
                                                @input="updateLineTotal(product)"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="w-24"
                                            />
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            {{ (Number(product.quantity || 0) * Number(product.unit_price || 0)).toFixed(2) }} {{ form.currency }}
                                        </TableCell>
                                        <TableCell>
                                            <Button 
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                @click="removeProduct(index)"
                                                class="text-red-600 hover:text-red-700"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div v-else class="text-center py-8 text-gray-500">
                            No products added yet. Add products using the form above.
                        </div>

                        <div v-if="form.errors.products" class="text-red-500 text-sm">
                            {{ form.errors.products }}
                        </div>
                    </CardContent>
                </Card>

                <!-- Financial Summary -->
                <Card v-if="form.products.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Calculator class="w-5 h-5 mr-2 text-emerald-600" />
                            Financial Summary
                        </CardTitle>
                        <CardDescription>Quote totals and calculations</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Reduction and VAT Rate Inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="reduction" class="text-sm font-medium text-gray-700">
                                    Discount/Reduction ({{ form.currency }})
                                </Label>
                                <Input
                                    id="reduction"
                                    v-model.number="form.reduction"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full"
                                />
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="vat_rate" class="text-sm font-medium text-gray-700">
                                    VAT Rate (%)
                                </Label>
                                <Input
                                    id="vat_rate"
                                    v-model.number="vatRate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="w-full"
                                    placeholder="20"
                                />
                                <p class="text-xs text-gray-500">Default: 20%</p>
                            </div>
                        </div>

                        <!-- Financial Breakdown -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal (HT):</span>
                                <span class="font-medium">{{ totals.total_amount.toFixed(2) }} {{ form.currency }}</span>
                            </div>
                            <div class="flex justify-between" v-if="form.reduction > 0">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-medium text-red-600">-{{ totals.reduction.toFixed(2) }} {{ form.currency }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">VAT ({{ vatRate.toFixed(0) }}%):</span>
                                <span class="font-medium">{{ totals.vat.toFixed(2) }} {{ form.currency }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total (TTC):</span>
                                <span class="text-green-600">{{ totals.total_ttc.toFixed(2) }} {{ form.currency }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Terms & Conditions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Terms & Conditions</CardTitle>
                        <CardDescription>Update terms and additional information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Payment Terms -->
                            <div class="space-y-2">
                                <Label for="payment_terms" class="text-sm font-medium text-gray-700">
                                    Payment Terms
                                </Label>
                                <Textarea
                                    id="payment_terms"
                                    v-model="form.payment_terms"
                                    placeholder="e.g., Net 30 days from invoice date"
                                    rows="3"
                                    class="w-full"
                                />
                            </div>

                            <!-- Delivery Terms -->
                            <div class="space-y-2">
                                <Label for="delivery_terms" class="text-sm font-medium text-gray-700">
                                    Delivery Terms
                                </Label>
                                <Textarea
                                    id="delivery_terms"
                                    v-model="form.delivery_terms"
                                    placeholder="e.g., FOB shipping point, 5-7 business days"
                                    rows="3"
                                    class="w-full"
                                />
                            </div>

                            <!-- Discount Notes -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="discount_notes" class="text-sm font-medium text-gray-700">
                                    Discount Notes
                                </Label>
                                <Textarea
                                    id="discount_notes"
                                    v-model="form.discount_notes"
                                    placeholder="e.g., Volume discount: 5% for orders over $10,000"
                                    rows="2"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Signature Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>Authorization</CardTitle>
                        <CardDescription>Update authorized signatory information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Signature Name -->
                            <div class="space-y-2">
                                <Label for="signature_name" class="text-sm font-medium text-gray-700">
                                    Authorized Signatory Name
                                </Label>
                                <Input
                                    id="signature_name"
                                    v-model="form.signature_name"
                                    type="text"
                                    placeholder="e.g., John Smith"
                                    class="w-full"
                                />
                            </div>

                            <!-- Signature Title -->
                            <div class="space-y-2">
                                <Label for="signature_title" class="text-sm font-medium text-gray-700">
                                    Title/Position
                                </Label>
                                <Input
                                    id="signature_title"
                                    v-model="form.signature_title"
                                    type="text"
                                    placeholder="e.g., Sales Manager"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <Button type="button" variant="outline" @click="goBack" class="flex items-center">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Cancel
                    </Button>
                    
                    <Button 
                        type="submit" 
                        :class="[
                            'px-8 py-2 flex items-center transition-all duration-200 font-medium',
                            (isSubmitting || form.processing)
                                ? 'bg-gray-400 cursor-not-allowed text-white' 
                                : 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                        ]"
                        :disabled="isSubmitting || form.processing"
                    >
                        <Save v-if="!isSubmitting && !form.processing" class="w-4 h-4 mr-2" />
                        <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
                        {{ isSubmitting || form.processing ? 'Updating Quote...' : 'Update Quote' }}
                    </Button>
                </div>
            </form>
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