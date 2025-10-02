<script setup>
// filepath: resources/js/Pages/Customers/Edit.vue

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
import { ArrowLeft, Building2, Save } from "lucide-vue-next";

const props = defineProps({
    customer: {
        type: Object,
        required: true
    },
    performanceFlags: {
        type: Array,
        default: () => ['Always on time', 'Small delays', 'Frequent big delays', 'No payment']
    }
});

const form = useForm({
    company_name: props.customer.company_name,
    contact_name: props.customer.contact_name,
    email: props.customer.email,
    phone: props.customer.phone || '',
    address: props.customer.address || '',
    performance_flag: props.customer.performance_flag || 'Always on time',
    vat_number: props.customer.vat_number || ''
});

const submit = () => {
    // ✅ Fix: Use the manual URL construction since your routes use {customer} parameter
    const updateUrl = `/Customer/${props.customer.id_customer}`;
    console.log('Updating customer with URL:', updateUrl);
    console.log('Customer ID:', props.customer.id_customer);
    
    form.put(updateUrl, {
        onSuccess: () => {
            console.log('Customer updated successfully');
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
                <Link href="/Customer">
                    <Button variant="outline" size="sm" class="flex items-center">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Customers
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Customer: {{ customer.company_name }}</h1>
                    <p class="text-gray-600">Update customer information</p>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="max-w-4xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Building2 class="w-5 h-5 mr-2" />
                            Update Customer Information
                        </CardTitle>
                        <CardDescription>
                            Modify the customer details below
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Company & Contact Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Company Name -->
                                <div class="space-y-2">
                                    <Label for="company_name" class="text-sm font-medium">
                                        Company Name <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="company_name"
                                        v-model="form.company_name"
                                        type="text"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.company_name" class="text-red-600 text-sm">
                                        {{ form.errors.company_name }}
                                    </div>
                                </div>

                                <!-- Contact Name -->
                                <div class="space-y-2">
                                    <Label for="contact_name" class="text-sm font-medium">
                                        Contact Person <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="contact_name"
                                        v-model="form.contact_name"
                                        type="text"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.contact_name" class="text-red-600 text-sm">
                                        {{ form.errors.contact_name }}
                                    </div>
                                </div>
                            </div>

                            <!-- Email & Phone -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Email -->
                                <div class="space-y-2">
                                    <Label for="email" class="text-sm font-medium">
                                        Email Address <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.email" class="text-red-600 text-sm">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="space-y-2">
                                    <Label for="phone" class="text-sm font-medium">
                                        Phone Number <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.phone" class="text-red-600 text-sm">
                                        {{ form.errors.phone }}
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="space-y-2">
                                <Label for="address" class="text-sm font-medium">
                                    Address <span class="text-red-500">*</span>
                                </Label>
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Enter full address"
                                    required
                                    :disabled="form.processing"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    rows="3"
                                ></textarea>
                                <div v-if="form.errors.address" class="text-red-600 text-sm">
                                    {{ form.errors.address }}
                                </div>
                            </div>

                            <!-- Performance Flag & VAT Number -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Performance Flag -->
                                <div class="space-y-2">
                                    <Label for="performance_flag" class="text-sm font-medium">
                                        Performance Rating <span class="text-red-500">*</span>
                                    </Label>
                                    <select 
                                        id="performance_flag"
                                        v-model="form.performance_flag"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :disabled="form.processing"
                                    >
                                        <option v-for="flag in performanceFlags" :key="flag" :value="flag">
                                            {{ flag }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.performance_flag" class="text-red-600 text-sm">
                                        {{ form.errors.performance_flag }}
                                    </div>
                                </div>

                                <!-- VAT Number -->
                                <div class="space-y-2">
                                    <Label for="vat_number" class="text-sm font-medium">
                                        VAT Number <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="vat_number"
                                        v-model="form.vat_number"
                                        type="text"
                                        placeholder="Enter VAT number"
                                        required
                                        :disabled="form.processing"
                                        class="w-full"
                                    />
                                    <div v-if="form.errors.vat_number" class="text-red-600 text-sm">
                                        {{ form.errors.vat_number }}
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Actions -->
                            <div class="flex items-center justify-between pt-6 border-t">
                                <Link href="/Customer">
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
                                    {{ form.processing ? 'Updating Customer...' : 'Update Customer' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>