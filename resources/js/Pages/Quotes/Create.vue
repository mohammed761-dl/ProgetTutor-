<script setup>
/**
 * Quote Creation Page
 * Frontend only handles data input and display
 * All calculations performed by backend via API calls
 */
import Layout from "../../Layout/App.vue";
import { useForm, router } from "@inertiajs/vue3";
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
    Minus, 
    Package, 
    Calculator,
    Trash2
} from "lucide-vue-next";
import { ref, computed, onMounted, watch } from "vue";

const props = defineProps({
    customers: Array,
    users: Array,
    products: Array,
    currentUser: Object // Add current user prop
});

const autoQuoteNumber = ref('');

const calculateValidUntil = (quoteDate) => {
    if (!quoteDate) return '';
    const date = new Date(quoteDate);
    date.setDate(date.getDate() + 30);
    return date.toISOString().split('T')[0];
};

const form = useForm({
    id_customer: '',
    id_user: props.currentUser?.id_user || '',
    date_quote: new Date().toISOString().split('T')[0],
    valid_until: calculateValidUntil(new Date().toISOString().split('T')[0]),
    status: 'Sent same day',
    currency: 'EUR',
    payment_terms: '',
    delivery_terms: '',
    discount_notes: '',
    reduction: 0,
    vat_rate: 0.20, // Add VAT rate field to form
    total_amount: 0,
    vat: 0,
    total_ttc: 0,
    signature_name: '',
    signature_title: '',
    products: []
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

const vatRate = ref(20);

onMounted(async () => {
    try {
        const response = await axios.get('/quote/next-number');
        
        if (response.data && response.data.quote_number) {
            autoQuoteNumber.value = response.data.quote_number;
        } else {
            const year = new Date().getFullYear();
            autoQuoteNumber.value = `SNXQT${year}00001`;
        }
    } catch (error) {
        const year = new Date().getFullYear();
        autoQuoteNumber.value = `SNXQT${year}00001`;
    }
});

watch(() => form.date_quote, (newDate) => {
    if (newDate) {
        form.valid_until = calculateValidUntil(newDate);
    }
});

// Debug VAT rate changes and sync with form
watch(vatRate, (newValue) => {
    console.log('VAT Rate ref changed to:', newValue);
    // Handle empty or invalid values gracefully
    if (newValue !== null && newValue !== undefined && !isNaN(newValue)) {
        form.vat_rate = parseFloat(newValue) / 100; // Keep form field in sync
    }
});

watch([() => form.products, () => form.reduction, () => vatRate.value, () => form.currency], async () => {
    console.log('VAT Rate changed to:', vatRate.value);
    await calculateTotals();
}, { deep: true });

const addProduct = () => {
    if (!selectedProduct.value || selectedQuantity.value <= 0 || selectedUnitPrice.value <= 0) {
        return;
    }
    
    const product = props.products.find(p => p.id_product == selectedProduct.value);
    if (!product) return;
    
    const existingIndex = form.products.findIndex(p => p.id_product == selectedProduct.value);
    
    if (existingIndex !== -1) {
        form.products[existingIndex].quantity = selectedQuantity.value;
        form.products[existingIndex].unit_price = selectedUnitPrice.value;
    } else {
        form.products.push({
            id_product: parseInt(selectedProduct.value),
            name: product.name,
            quantity: selectedQuantity.value,
            unit_price: selectedUnitPrice.value,
            line_total: selectedQuantity.value * selectedUnitPrice.value
        });
    }
    
    selectedProduct.value = '';
    selectedQuantity.value = 1;
    selectedUnitPrice.value = 0;
};

const removeProduct = (index) => {
    form.products.splice(index, 1);
};

const onProductChange = () => {
    if (selectedProduct.value) {
        const product = props.products.find(p => p.id_product == selectedProduct.value);
        if (product) {
            selectedUnitPrice.value = parseFloat(product.unit_price || 0);
        }
    }
};

const calculateTotals = async () => {
    totals.value = {
        total_amount: 0,
        reduction: 0,
        vat: 0,
        total_ttc: 0
    };

    if (form.products.length === 0) {
        return;
    }

    form.products.forEach(product => {
        if (product.quantity && product.unit_price) {
            const lineTotal = parseFloat(product.quantity) * parseFloat(product.unit_price);
            product.line_total = lineTotal;
        }
    });

    try {
        const products = form.products.map(p => ({
            quantity: parseInt(p.quantity),
            unit_price: parseFloat(p.unit_price)
        }));
        
        const response = await axios.post('/Quote/calculate-totals', {
            products: products,
            reduction: parseFloat(form.reduction || 0),
            vat_rate: parseFloat(vatRate.value) / 100
        });
        
        totals.value = {
            total_amount: parseFloat(response.data.total_amount),
            reduction: parseFloat(response.data.reduction),
            vat: parseFloat(response.data.vat),
            total_ttc: parseFloat(response.data.total_ttc)
        };
    } catch (error) {
        totals.value = {
            total_amount: 0,
            reduction: 0,
            vat: 0,
            total_ttc: 0
        };
    }
};

const updateLineTotal = (product) => {
    product.line_total = product.quantity * product.unit_price;
};

const validateForm = () => {
    if (!form.id_customer) {
        throw new Error('Please select a customer');
    }

    if (form.products.length === 0) {
        throw new Error('Please add at least one product to the quote');
    }

    for (const product of form.products) {
        if (!product.quantity || !product.unit_price || product.quantity <= 0 || product.unit_price <= 0) {
            throw new Error('Please ensure all products have valid quantities and prices');
        }
    }

    if (!autoQuoteNumber.value) {
        throw new Error('No valid quote number generated. Please refresh the page.');
    }
};

const submit = async () => {
    try {
        const productsData = form.products.map(p => ({
            id_product: parseInt(p.id_product),
            quantity: parseInt(p.quantity),
            unit_price: parseFloat(p.unit_price)
        }));

        // Update form with current VAT rate
        form.vat_rate = parseFloat(vatRate.value) / 100;

        const formData = {
            id_customer: parseInt(form.id_customer),
            id_user: parseInt(form.id_user),
            date_quote: form.date_quote,
            valid_until: form.valid_until,
            status: form.status,
            currency: form.currency,
            payment_terms: form.payment_terms || null,
            delivery_terms: form.delivery_terms || null,
            discount_notes: form.discount_notes || null,
            reduction: parseFloat(form.reduction || 0),
            vat_rate: form.vat_rate, // Use form's VAT rate field
            signature_name: form.signature_name || null,
            signature_title: form.signature_title || null,
            quote_number: autoQuoteNumber.value,
            products: productsData
        };

        // Debug: Log what's being sent
        console.log('Submitting form with VAT rate:', vatRate.value, '->', formData.vat_rate);

        await form.post('/Quote', {
            preserveScroll: true,
            onSuccess: () => {
                router.visit('/Quote', {
                    method: 'get',
                    preserveState: false
                });
            },
            onError: (errors) => {
                console.error('Validation errors:', errors);
            }
        });
    } catch (error) {
        console.error('Error creating quote:', error);
    }
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Layout>
        <div class="max-w-6xl mx-auto p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <FileText class="w-8 h-8 mr-3 text-blue-600" />
                        Create New Quote
                    </h1>
                    <p class="text-gray-600 mt-1">Quote Number: <span class="font-mono text-blue-600">{{ autoQuoteNumber }}</span></p>
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
                        <CardDescription>Enter the basic quote details</CardDescription>
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Select Customer</option>
                                    <option v-for="customer in customers" :key="customer.id_customer" :value="customer.id_customer">
                                        {{ customer.company_name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_customer" class="text-red-500 text-sm">
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
                                        :value="props.currentUser?.name || 'Unknown User'"
                                        readonly
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm cursor-not-allowed text-gray-600"
                                        placeholder="Current User"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <User class="w-4 h-4 text-gray-400" />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">Automatically set to current logged-in user</p>
                                <!-- Hidden input for form submission -->
                                <input type="hidden" v-model="form.id_user" />
                                <div v-if="form.errors.id_user" class="text-red-500 text-sm">
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
                                    class="w-full"
                                    required
                                />
                                <div v-if="form.errors.date_quote" class="text-red-500 text-sm">
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
                                    class="w-full"
                                    required
                                />
                                <div v-if="form.errors.valid_until" class="text-red-500 text-sm">
                                    {{ form.errors.valid_until }}
                                </div>
                                <p class="text-xs text-gray-500">Auto-calculated as 30 days from quote date</p>
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

                <!-- Product Selection -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Package class="w-5 h-5 mr-2 text-orange-600" />
                            Add Products
                        </CardTitle>
                        <CardDescription>Select products and quantities for this quote</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                            <!-- Product Selection -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Product</Label>
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
                                />
                            </div>

                            <!-- Unit Price -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Unit Price</Label>
                                <div class="relative">
                                    <Input
                                        v-model.number="selectedUnitPrice"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full bg-gray-50 cursor-not-allowed"
                                        readonly
                                        placeholder="Select product first"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <DollarSign class="w-4 h-4 text-gray-400" />
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">💡 Price automatically set from product catalog</p>
                            </div>

                            <!-- Add Button -->
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">&nbsp;</Label>
                                <Button 
                                    type="button"
                                    @click="addProduct"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white"
                                >
                                    <Plus class="w-4 h-4 mr-2" />
                                    Add
                                </Button>
                            </div>
                        </div>

                        <!-- Products Table -->
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
                        <CardDescription>Optional terms and additional information</CardDescription>
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
                        <CardDescription>Authorized signatory information</CardDescription>
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
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-2 flex items-center"
                        :disabled="form.processing || form.products.length === 0"
                    >
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Creating Quote...' : 'Create Quote' }}
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