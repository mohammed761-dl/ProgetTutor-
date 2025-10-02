<script setup>
// filepath: resources/js/Pages/Products/Edit.vue

import AdminApp from "../../Layout/AdminApp.vue";
import { useForm, Link } from "@inertiajs/vue3";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import { ArrowLeft, Package, Save } from "lucide-vue-next";

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    statuses: {
        type: Array,
        default: () => ['Active', 'EOL', 'Archived']
    }
});

const form = useForm({
    product_code: props.product.product_code,
    name: props.product.name,
    description: props.product.description,
    technical_specs: props.product.technical_specs,
    commercial_terms: props.product.commercial_terms,
    payment_terms: props.product.payment_terms,
    image_url: props.product.image_url || '',
    min_delivery_day: props.product.min_delivery_day,
    max_delivery_day: props.product.max_delivery_day,
    availability_yrs: props.product.availability_yrs,
    status: props.product.status,
    unit_price: props.product.unit_price || 0.00 // Add unit_price field
});

const submit = () => {
    form.put(route('products.update', props.product.id_product), {
        onSuccess: () => {
            console.log('Product updated successfully');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};
</script>

<template>
    <AdminApp>
        <div class="pt-6 space-y-6">
            <!-- Header with Back Button -->
            <div class="flex items-center space-x-4">
                <Link :href="route('products.index')">
                    <Button variant="outline" size="sm" class="flex items-center">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Products
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Product: {{ product.name }}</h1>
                    <p class="text-gray-600">Update product information</p>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="max-w-4xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Package class="w-5 h-5 mr-2" />
                            Update Product Information
                        </CardTitle>
                        <CardDescription>
                            Modify the product details below
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
                                <p class="text-xs text-gray-500">💡 Changes to price will only affect new quotes created after this update</p>
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
                                <Link :href="route('products.index')">
                                    <Button variant="outline" type="button" :disabled="form.processing">
                                        Cancel
                                    </Button>
                                </Link>
                                
                                <Button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6"
                                >
                                    <Save class="w-4 h-4 mr-2" />
                                    {{ form.processing ? 'Updating Product...' : 'Update Product' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>