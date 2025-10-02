<template>
    <Layout>
        <div class="max-w-4xl mx-auto p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <FileText class="w-8 h-8 mr-3 text-blue-600" />
                        Create Purchase Order
                    </h1>
                    <p class="text-gray-600 mt-1">Create a new customer purchase order</p>
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
                        <!-- Section: Basic Information -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <FileText class="w-5 h-5 mr-2 text-blue-600" />
                                    Basic Information
                                </h3>
                                <p class="text-sm text-gray-600">PO number will be auto-generated upon creation</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                            {{ customer.company_name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.id_customer" class="text-sm text-red-600 mt-1">{{ form.errors.id_customer }}</p>
                                </div>

                                <!-- Quote Reference -->
                                <div class="space-y-2">
                                    <Label for="id_quote" class="text-sm font-medium text-gray-700">
                                        Quote Reference
                                    </Label>
                                    <select
                                        id="id_quote"
                                        v-model="form.id_quote"
                                        @change="handleQuoteChange"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    >
                                        <option value="">Select a quote (optional)</option>
                                        <option v-for="quote in quotes" :key="quote.id_quote" :value="quote.id_quote">
                                            {{ quote.quote_number }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Status & Delivery -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <Calendar class="w-5 h-5 mr-2 text-green-600" />
                                    Status & Delivery Information
                                </h3>
                                <p class="text-sm text-gray-600">Set status and delivery dates</p>
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
                                    <p v-if="form.errors.status" class="text-sm text-red-600 mt-1">{{ form.errors.status }}</p>
                                </div>

                                <!-- Planned Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="planned_delivery_date" class="text-sm font-medium text-gray-700">
                                        Planned Delivery Date
                                    </Label>
                                    <input
                                        type="date"
                                        id="planned_delivery_date"
                                        v-model="form.planned_delivery_date"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    />
                                    <p v-if="form.errors.planned_delivery_date" class="text-sm text-red-600 mt-1">{{ form.errors.planned_delivery_date }}</p>
                                </div>

                                <!-- Actual Delivery Date -->
                                <div class="space-y-2">
                                    <Label for="actual_delivery_date" class="text-sm font-medium text-gray-700">
                                        Actual Delivery Date
                                    </Label>
                                    <input
                                        type="date"
                                        id="actual_delivery_date"
                                        v-model="form.actual_delivery_date"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    />
                                    <p v-if="form.errors.actual_delivery_date" class="text-sm text-red-600 mt-1">{{ form.errors.actual_delivery_date }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Remarks & PDF -->
                        <div class="space-y-4">
                            <div class="border-b pb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <FileText class="w-5 h-5 mr-2 text-purple-600" />
                                    Remarks & PDF Upload
                                </h3>
                                <p class="text-sm text-gray-600">Add remarks and upload the PO PDF</p>
                            </div>
                            <!-- Remarks (full width) -->
                            <div class="space-y-2">
                                <Label for="remarks" class="text-sm font-medium text-gray-700">
                                    Remarks
                                </Label>
                                <textarea
                                    id="remarks"
                                    v-model="form.remarks"
                                    rows="3"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    placeholder="Add any additional notes here..."
                                ></textarea>
                                <p v-if="form.errors.remarks" class="text-sm text-red-600 mt-1">{{ form.errors.remarks }}</p>
                            </div>
                            <!-- PDF Upload (full width, below remarks) -->
                            <div class="space-y-2">
                                <Label for="pdf-upload" class="text-sm font-medium text-gray-700">
                                    PO PDF *
                                </Label>
                                <div 
                                    @dragover="handleDragOver"
                                    @drop="handleDrop"
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors"
                                >
                                    <input 
                                        type="file" 
                                        @change="handleFileUpload"
                                        accept=".pdf"
                                        class="hidden"
                                        id="pdf-upload"
                                    />
                                    <label 
                                        for="pdf-upload" 
                                        class="cursor-pointer"
                                    >
                                        <FileText class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                                        <p class="text-lg font-medium text-gray-700 mb-2">Drop your PDF here or click to browse</p>
                                        <p v-if="form.po_pdf" class="text-sm text-green-600">
                                            Selected: {{ form.po_pdf.name }}
                                        </p>
                                        <p v-if="form.errors.po_pdf" class="text-sm text-red-600 mt-2">
                                            {{ form.errors.po_pdf }}
                                        </p>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between pt-6 border-t">
                            <Button type="button" variant="outline" @click="goBack" class="flex items-center">
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 flex items-center">
                                <FileText class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Purchase Order' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </Layout>
</template>

<script setup>
import Layout from "../../Layout/App.vue";
import { useForm, router } from "@inertiajs/vue3";
import Card from "@/components/ui/card/Card.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Button from "@/components/ui/button/Button.vue";
import Label from "@/components/ui/label/Label.vue";
import { FileText, ArrowLeft, Calendar } from "lucide-vue-next";

const props = defineProps({
    customers: Array,
    quotes: Array,
    currentUser: Object
});

// Ensure current user is available


const form = useForm({
    id_customer: '',
    id_quote: '',
    po_pdf: null,
    created_by: props.currentUser.id, // Required field
    status: 'Pending',
    planned_delivery_date: '',
    actual_delivery_date: '',
    remarks: ''
});

const handleQuoteChange = () => {
    if (form.id_quote) {
        const selectedQuote = props.quotes.find(q => q.id_quote == form.id_quote);
        if (selectedQuote) {
            form.id_customer = selectedQuote.id_customer;
        }
    }
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file && file.type === 'application/pdf') {
        form.po_pdf = file;
    } else {
        event.target.value = '';
    }
};

const handleDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'copy';
};

const handleDrop = (event) => {
    event.preventDefault();
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        if (file.type === 'application/pdf') {
            form.po_pdf = file;
        }
    }
};

const submit = () => {
    if (!form.po_pdf) {
        return;
    }

    form.post('/po', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            window.location.href = '/po';
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

<style scoped>
.hidden {
    display: none;
}

input:focus, select:focus {
    outline: none;
    border-color: #3b82f6;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
