<script setup>
// filepath: resources/js/Pages/Products/Create.vue

import AdminApp from "../../Layout/AdminApp.vue";
import { useForm, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import { ArrowLeft, Package, Plus, AlertTriangle, CheckCircle, X, Loader2, AlertCircle } from "lucide-vue-next";

const props = defineProps({
    statuses: {
        type: Array,
        default: () => ['Active', 'EOL', 'Archived']
    }
});

// Alert and notification states
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('error'); // 'success', 'error', 'warning', 'info'
const alertDetails = ref([]);
const isSubmitting = ref(false);

// Show alert function
const showNotification = (message, type = 'error', details = [], duration = 5000) => {
    alertMessage.value = message;
    alertType.value = type;
    alertDetails.value = details;
    showAlert.value = true;
    
    if (duration > 0) {
        setTimeout(() => {
            showAlert.value = false;
        }, duration);
    }
};

// Close alert function
const closeAlert = () => {
    showAlert.value = false;
    alertMessage.value = '';
    alertDetails.value = [];
};

const form = useForm({
    product_code: '',
    name: '',
    description: '',
    technical_specs: '',
    commercial_terms: '',
    payment_terms: '',
    image_url: '',
    min_delivery_day: '',
    max_delivery_day: '',
    availability_yrs: '',
    status: 'Active',
    unit_price: 0.00 // Add unit_price field
});

// Alert icon computed property
const alertIcon = computed(() => {
    switch (alertType.value) {
        case 'success':
            return CheckCircle;
        case 'error':
            return AlertCircle;
        case 'warning':
            return AlertTriangle;
        case 'info':
            return AlertCircle;
        default:
            return AlertCircle;
    }
});

// Alert classes computed property
const alertClasses = computed(() => {
    const baseClasses = 'rounded-lg border p-4 mb-4';
    switch (alertType.value) {
        case 'success':
            return `${baseClasses} border-green-200 bg-green-50 text-green-800`;
        case 'error':
            return `${baseClasses} border-red-200 bg-red-50 text-red-800`;
        case 'warning':
            return `${baseClasses} border-yellow-200 bg-yellow-50 text-yellow-800`;
        case 'info':
            return `${baseClasses} border-blue-200 bg-blue-50 text-blue-800`;
        default:
            return `${baseClasses} border-gray-200 bg-gray-50 text-gray-800`;
    }
});

const submit = () => {
    if (isSubmitting.value) return;
    
    isSubmitting.value = true;
    closeAlert(); // Clear any existing alerts
    
    form.post(route('products.store'), {
        onSuccess: () => {
            isSubmitting.value = false;
            showNotification('Product created successfully!', 'success', [], 3000);
            
            // Optional: Reset form after successful creation
            setTimeout(() => {
                form.reset();
            }, 1000);
        },
        onError: (errors) => {
            isSubmitting.value = false;
            const errorList = Object.values(errors).flat();
            showNotification(
                'Please correct the following errors:',
                'error',
                errorList,
                0 // Don't auto-hide error messages
            );
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <AdminApp>
        <div class="pt-6 space-y-6">
            <!-- Alert Notification -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform -translate-y-2"
                enter-to-class="opacity-100 transform translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 transform translate-y-0"
                leave-to-class="opacity-0 transform -translate-y-2"
            >
                <div v-if="showAlert" :class="alertClasses">
                    <div class="flex items-start">
                        <component :is="alertIcon" class="h-5 w-5 mt-0.5 mr-3 flex-shrink-0" />
                        <div class="flex-1">
                            <p class="font-medium">{{ alertMessage }}</p>
                            <ul v-if="alertDetails.length > 0" class="mt-2 text-sm space-y-1">
                                <li v-for="(detail, index) in alertDetails" :key="index" class="flex items-start">
                                    <span class="w-1 h-1 bg-current rounded-full mt-2 mr-2 flex-shrink-0"></span>
                                    {{ detail }}
                                </li>
                            </ul>
                        </div>
                        <button @click="closeAlert" class="ml-3 flex-shrink-0">
                            <X class="h-4 w-4 hover:opacity-70" />
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Header with Back Button -->
            <div class="flex items-center space-x-4">
                <Link :href="route('products.index')">
                    <Button variant="outline" size="sm" class="flex items-center">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Products
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add New Product</h1>
                    <p class="text-gray-600">Enter product information to add it to your catalog</p>
                </div>
            </div>

            <!-- Create Form -->
            <div class="max-w-4xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Package class="w-5 h-5 mr-2" />
                            Product Information
                        </CardTitle>
                        <CardDescription>
                            Enter the details for the new product
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Product Code & Name -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Product Code -->
                                <div class="space-y-2">
                                    <Label for="product_code" class="text-sm font-medium">
                                        Product Code <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="product_code"
                                        v-model="form.product_code"
                                        type="text"
                                        placeholder="e.g., PROD-001"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.product_code" class="text-red-600 text-sm">
                                        {{ form.errors.product_code }}
                                    </div>
                                </div>

                                <!-- Product Name -->
                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium">
                                        Product Name <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Enter product name"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.name" class="text-red-600 text-sm">
                                        {{ form.errors.name }}
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description" class="text-sm font-medium">
                                    Description <span class="text-red-500">*</span>
                                </Label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Enter product description"
                                    required
                                    :disabled="form.processing"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    rows="3"
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-600 text-sm">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Technical Specs -->
                            <div class="space-y-2">
                                <Label for="technical_specs" class="text-sm font-medium">
                                    Technical Specifications <span class="text-red-500">*</span>
                                </Label>
                                <textarea
                                    id="technical_specs"
                                    v-model="form.technical_specs"
                                    placeholder="Enter technical specifications"
                                    required
                                    :disabled="form.processing"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    rows="3"
                                ></textarea>
                                <div v-if="form.errors.technical_specs" class="text-red-600 text-sm">
                                    {{ form.errors.technical_specs }}
                                </div>
                            </div>

                            <!-- Commercial Terms & Payment Terms -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Commercial Terms -->
                                <div class="space-y-2">
                                   
                                    
                                    <Label for="commercial_terms" class="text-sm font-medium">
                                        Commercial Terms <span class="text-red-500">*</span>
                                    </Label>
                                    <textarea
                                        id="commercial_terms"
                                        v-model="form.commercial_terms"
                                        placeholder="Enter commercial terms"
                                        required
                                        :disabled="form.processing"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        rows="3"
                                    ></textarea>
                                    <div v-if="form.errors.commercial_terms" class="text-red-600 text-sm">
                                        {{ form.errors.commercial_terms }}
                                    </div>
                                </div>

                                <!-- Payment Terms -->
                                <div class="space-y-2">
                                    <Label for="payment_terms" class="text-sm font-medium">
                                        Payment Terms <span class="text-red-500">*</span>
                                    </Label>
                                    <textarea
                                        id="payment_terms"
                                        v-model="form.payment_terms"
                                        placeholder="Enter payment terms"
                                        required
                                        :disabled="form.processing"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        rows="3"
                                    ></textarea>
                                    <div v-if="form.errors.payment_terms" class="text-red-600 text-sm">
                                        {{ form.errors.payment_terms }}
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery & Availability -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Min Delivery Days -->
                                <div class="space-y-2">
                                    <Label for="min_delivery_day" class="text-sm font-medium">
                                        Min Delivery Days <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="min_delivery_day"
                                        v-model="form.min_delivery_day"
                                        type="number"
                                        min="1"
                                        placeholder="5"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.min_delivery_day" class="text-red-600 text-sm">
                                        {{ form.errors.min_delivery_day }}
                                    </div>
                                </div>

                                <!-- Max Delivery Days -->
                                <div class="space-y-2">
                                    <Label for="max_delivery_day" class="text-sm font-medium">
                                        Max Delivery Days <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="max_delivery_day"
                                        v-model="form.max_delivery_day"
                                        type="number"
                                        min="1"
                                        placeholder="15"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.max_delivery_day" class="text-red-600 text-sm">
                                        {{ form.errors.max_delivery_day }}
                                    </div>
                                </div>

                                <!-- Availability Years -->
                                <div class="space-y-2">
                                    <Label for="availability_yrs" class="text-sm font-medium">
                                        Availability (Years) <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="availability_yrs"
                                        v-model="form.availability_yrs"
                                        type="number"
                                        min="1"
                                        placeholder="5"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.availability_yrs" class="text-red-600 text-sm">
                                        {{ form.errors.availability_yrs }}
                                    </div>
                                </div>
                            </div>

                            <!-- Unit Price -->
                            <div class="space-y-2">
                                <Label for="unit_price" class="text-sm font-medium">
                                    Unit Price (EUR) <span class="text-red-500">*</span>
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="unit_price"
                                        v-model.number="form.unit_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        :disabled="form.processing"
                                        class="w-full pr-10"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 text-sm">€</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">💡 This price will be used as default for new quotes</p>
                                <div v-if="form.errors.unit_price" class="text-red-600 text-sm">
                                    {{ form.errors.unit_price }}
                                </div>
                            </div>

                            <!-- Image URL & Status -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Image URL -->
                                <div class="space-y-2">
                                    <Label for="image_url" class="text-sm font-medium">
                                        Image URL
                                    </Label>
                                    <Input
                                        id="image_url"
                                        v-model="form.image_url"
                                        type="url"
                                        placeholder="https://example.com/image.jpg"
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.image_url" class="text-red-600 text-sm">
                                        {{ form.errors.image_url }}
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <Label for="status" class="text-sm font-medium">
                                        Status <span class="text-red-500">*</span>
                                    </Label>
                                    <select 
                                        id="status"
                                        v-model="form.status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :disabled="form.processing"
                                    >
                                        <option v-for="status in statuses" :key="status" :value="status">
                                            {{ status }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-600 text-sm">
                                        {{ form.errors.status }}
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Actions -->
                            <div class="flex items-center justify-between pt-6 border-t">
                                <Link href="/Product">
                                    <Button variant="outline" type="button" :disabled="form.processing">
                                        Cancel
                                    </Button>
                                </Link>
                                
                                <Button 
                                    type="submit" 
                                    :disabled="form.processing || isSubmitting"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                                    <Plus v-else class="w-4 h-4 mr-2" />
                                    {{ isSubmitting ? 'Adding Product...' : 'Add Product' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>