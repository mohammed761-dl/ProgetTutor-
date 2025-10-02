<script setup>
import Layout from "../../Layout/App.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
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
import { Receipt, Save, ArrowLeft, Calendar, DollarSign, Building2, Plus, Minus, FileText, Truck } from "lucide-vue-next";

const props = defineProps({
    quotes: Array,
    users: Array
});

const selectedQuote = ref(null);
const availableProducts = ref([]);

// Form setup
const form = useForm({
    id_quote: '',
    customer_po_no: '',
    date_invoice: new Date().toISOString().split('T')[0],
    payment_status: 'Always on time',
    date_payment: '',
    id_user: '',
    currency: 'EUR',
    payment_terms: 'Net 30',
    total_excl_vat: '',
    vat_amount: '',
    total_incl_vat: '',
    customer_vat_number: '',
    customer_contact_person: '',
    customer_email: '',
    customer_phone: '',
    supplier_vat_number: '',
    supplier_iso_certification: '',
    notes: '',
    invoice_products: []
});

// Watch for quote selection
watch(() => form.id_quote, async (newValue) => {
    if (newValue) {
        selectedQuote.value = props.quotes.find(quote => quote.id_quote == newValue);
        
        // Auto-fill customer data from quote
        if (selectedQuote.value) {
            form.customer_vat_number = selectedQuote.value.customer_vat || '';
            form.customer_contact_person = selectedQuote.value.customer_name || '';
            form.customer_email = selectedQuote.value.customer_email || '';
            form.customer_phone = selectedQuote.value.customer_phone || '';
            form.currency = selectedQuote.value.currency || 'EUR';
            form.payment_terms = selectedQuote.value.payment_terms || 'Net 30';
        }
        
        // Fetch products for this quote
        try {
            const response = await fetch(`/api/quotes/${newValue}/products`);
            const data = await response.json();
            availableProducts.value = data.products;
            
            // Initialize invoice products from quote products
            form.invoice_products = data.products.map(product => ({
                quote_product_id: product.id,
                product_name: product.name,
                unit_price: product.unit_price,
                quantity_delivered: product.quantity, // Assume all delivered
                quantity_invoiced: product.quantity, // Default to full quantity
                reduction: 0,
                total_ht: product.unit_price * product.quantity,
                vat_amount: (product.unit_price * product.quantity) * 0.20, // 20% VAT
                line_total: (product.unit_price * product.quantity) * 1.20 // Including VAT
            }));
            
            // Calculate totals from quote products
            let totalExclVat = 0;
            data.products.forEach(product => {
                totalExclVat += product.unit_price * product.quantity;
            });
            
            form.total_excl_vat = totalExclVat.toFixed(2);
            
        } catch (error) {
            console.error('Error fetching quote products:', error);
        }
    } else {
        selectedQuote.value = null;
        availableProducts.value = [];
        form.invoice_products = [];
    }
});

// Calculate totals for a product line
const calculateLineTotal = (productIndex) => {
    const product = form.invoice_products[productIndex];
    if (!product) return;
    
    const totalHt = product.unit_price * product.quantity_invoiced;
    const vatAmount = totalHt * 0.20; // 20% VAT
    const lineTotal = totalHt + vatAmount - product.reduction;
    
    product.total_ht = totalHt;
    product.vat_amount = vatAmount;
    product.line_total = lineTotal;
};

// Calculate invoice totals
const invoiceTotals = computed(() => {
    const subTotal = form.invoice_products.reduce((sum, product) => sum + product.total_ht, 0);
    const taxTotal = form.invoice_products.reduce((sum, product) => sum + product.vat_amount, 0);
    const discountTotal = form.invoice_products.reduce((sum, product) => sum + product.reduction, 0);
    const grandTotal = subTotal + taxTotal - discountTotal;
    
    return {
        subTotal: subTotal.toFixed(2),
        taxTotal: taxTotal.toFixed(2),
        discountTotal: discountTotal.toFixed(2),
        grandTotal: grandTotal.toFixed(2)
    };
});

// Get default payment date (30 days from now)
const getDefaultPaymentDate = () => {
    const date = new Date();
    date.setDate(date.getDate() + 30);
    return date.toISOString().split('T')[0];
};

// Initialize payment date
form.date_payment = getDefaultPaymentDate();

// Submit form
const submit = () => {
    // Filter out products with 0 quantity
    const productsToInvoice = form.invoice_products.filter(product => product.quantity_invoiced > 0);
    
    if (productsToInvoice.length === 0) {
        alert('Please add at least one product to the invoice.');
        return;
    }
    
    form.invoice_products = productsToInvoice;
    form.post('/invoices');
};
</script>

<template>
    <Layout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center gap-3">
                        <Receipt class="w-8 h-8 text-blue-600" />
                        Create Invoice
                    </h1>
                    <p class="text-muted-foreground">Generate invoice from delivered products</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as="a" href="/invoices">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Invoices
                    </Button>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="w-5 h-5 text-blue-600" />
                            Invoice Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Quote Selection -->
                            <div class="space-y-2">
                                <Label for="id_quote">Quote *</Label>
                                <select 
                                    id="id_quote"
                                    v-model="form.id_quote" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Select Quote</option>
                                    <option v-for="quote in quotes" :key="quote.id_quote" :value="quote.id_quote">
                                        {{ quote.quote_number }} - {{ quote.customer_name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_quote" class="text-red-600 text-sm">
                                    {{ form.errors.id_quote }}
                                </div>
                            </div>

                            <!-- Invoice Date -->
                            <div class="space-y-2">
                                <Label for="date_invoice">Invoice Date *</Label>
                                <Input 
                                    id="date_invoice"
                                    v-model="form.date_invoice" 
                                    type="date" 
                                    required 
                                />
                                <div v-if="form.errors.date_invoice" class="text-red-600 text-sm">
                                    {{ form.errors.date_invoice }}
                                </div>
                            </div>

                            <!-- Currency -->
                            <div class="space-y-2">
                                <Label for="currency">Currency *</Label>
                                <select 
                                    id="currency"
                                    v-model="form.currency" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="MAD">MAD - Moroccan Dirham</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                </select>
                            </div>

                            <!-- Remarks -->
                            <div class="space-y-2">
                                <Label for="remarks">Remarks</Label>
                                <Textarea 
                                    id="remarks"
                                    v-model="form.remarks" 
                                    placeholder="Optional remarks or notes"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Customer Info (when Quote selected) -->
                <Card v-if="selectedQuote">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building2 class="w-5 h-5 text-orange-600" />
                            Customer Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-sm font-medium text-gray-600">Customer Name</Label>
                                <p class="text-lg font-semibold">{{ selectedQuote.customer_name }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-600">Quote Number</Label>
                                <p class="text-lg font-semibold flex items-center">
                                    <FileText class="w-4 h-4 mr-2 text-purple-600" />
                                    {{ selectedQuote.quote_number }}
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <Label class="text-sm font-medium text-gray-600">Address</Label>
                                <p class="text-sm">{{ selectedQuote.customer_address }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Products -->
                <Card v-if="form.invoice_products.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <DollarSign class="w-5 h-5 text-green-600" />
                            Invoice Products
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Unit Price</TableHead>
                                        <TableHead>Delivered</TableHead>
                                        <TableHead>Qty to Invoice</TableHead>
                                        <TableHead>Reduction</TableHead>
                                        <TableHead>Total HT</TableHead>
                                        <TableHead>VAT (20%)</TableHead>
                                        <TableHead>Line Total</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(product, index) in form.invoice_products" :key="product.quote_product_id">
                                        <TableCell>
                                            <div class="font-semibold">{{ product.product_name }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="font-mono">{{ product.unit_price }} {{ form.currency }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="text-blue-600 font-semibold">{{ product.quantity_delivered }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                v-model.number="product.quantity_invoiced" 
                                                type="number" 
                                                min="0" 
                                                :max="product.quantity_delivered"
                                                @input="calculateLineTotal(index)"
                                                class="w-20"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                v-model.number="product.reduction" 
                                                type="number" 
                                                step="0.01"
                                                min="0"
                                                @input="calculateLineTotal(index)"
                                                class="w-24"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div class="font-mono">{{ product.total_ht.toFixed(2) }} {{ form.currency }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="font-mono">{{ product.vat_amount.toFixed(2) }} {{ form.currency }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="font-mono font-semibold text-green-600">{{ product.line_total.toFixed(2) }} {{ form.currency }}</div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Totals Summary -->
                        <div class="mt-6 border-t pt-4">
                            <div class="flex justify-end">
                                <div class="w-80 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="font-medium">Subtotal (HT):</span>
                                        <span class="font-mono">{{ invoiceTotals.subTotal }} {{ form.currency }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">VAT Total:</span>
                                        <span class="font-mono">{{ invoiceTotals.taxTotal }} {{ form.currency }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">Discount Total:</span>
                                        <span class="font-mono">-{{ invoiceTotals.discountTotal }} {{ form.currency }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                                        <span>Grand Total:</span>
                                        <span class="font-mono text-green-600">{{ invoiceTotals.grandTotal }} {{ form.currency }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <Button 
                        type="submit" 
                        :disabled="form.processing || !form.id_quote"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Creating...' : 'Create Invoice' }}
                    </Button>
                </div>
            </form>
        </div>
    </Layout>
</template>