<script setup>
// filepath: resources/js/Pages/AdminProfile/Index.vue

import AdminApp from "../../Layout/AdminApp.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import Textarea from "@/components/ui/textarea/Textarea.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardDescription from "@/components/ui/card/CardDescription.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import Badge from "@/components/ui/badge/Badge.vue";

// ShadCN Table components
import Table from "@/components/ui/table/Table.vue";
import TableHeader from "@/components/ui/table/TableHeader.vue";
import TableBody from "@/components/ui/table/TableBody.vue";
import TableRow from "@/components/ui/table/TableRow.vue";
import TableCell from "@/components/ui/table/TableCell.vue";
import TableHead from "@/components/ui/table/TableHead.vue";

import { 
    User, 
    Save, 
    Lock, 
    Mail, 
    Calendar, 
    Shield, 
    Building, 
    Globe, 
    Phone, 
    MapPin, 
    CreditCard, 
    FileText,
    Edit
} from "lucide-vue-next";

const props = defineProps({
    admin: {
        type: Object,
        required: true
    },
    companyInfo: {
        type: Object,
        required: true
    }
});

// Reactive display data
const displayData = ref({
    name: props.admin.name,
    email: props.admin.email,
    created_at: props.admin.created_at
});

const companyDisplayData = ref({ ...props.companyInfo });

// Profile update form
const profileForm = useForm({
    name: props.admin.name,
    email: props.admin.email,
});

// Company info form
const companyForm = useForm({
    name: props.companyInfo.name,
    address: props.companyInfo.address,
    phone: props.companyInfo.phone,
    email: props.companyInfo.email,
    website: props.companyInfo.website,
    warranty_duration: props.companyInfo.warranty_duration,
    support_info: props.companyInfo.support_info,
    bank_name: props.companyInfo.bank_name,
    swift_bic: props.companyInfo.swift_bic,
    account_number: props.companyInfo.account_number,
    iban: props.companyInfo.iban,
    authorized_name: props.companyInfo.authorized_name,
    authorized_title: props.companyInfo.authorized_title,
    signature_email: props.companyInfo.signature_email,
    signature_phone: props.companyInfo.signature_phone,
    general_conditions_url: props.companyInfo.general_conditions_url,
    vat_number: props.companyInfo.vat_number,
});

// Password change form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Edit mode for company info
const isEditingCompany = ref(false);

const updateProfile = () => {
    profileForm.put('/serp-admin/profile', {
        onSuccess: (page) => {
            console.log('Profile updated successfully');
            displayData.value.name = profileForm.name;
            displayData.value.email = profileForm.email;
        },
        onError: (errors) => {
            console.error('Profile update errors:', errors);
        }
    });
};

const updateCompanyInfo = () => {
    companyForm.put('/serp-admin/company-info', {
        onSuccess: (page) => {
            console.log('Company info updated successfully');
            companyDisplayData.value = { ...companyForm.data() };
            isEditingCompany.value = false;
        },
        onError: (errors) => {
            console.error('Company info update errors:', errors);
        }
    });
};

const changePassword = () => {
    passwordForm.put('/serp-admin/change-password', {
        onSuccess: () => {
            console.log('Password changed successfully');
            passwordForm.reset('current_password', 'password', 'password_confirmation');
        },
        onError: (errors) => {
            console.error('Password change errors:', errors);
        }
    });
};

// Check if form data has changes
const hasProfileChanges = computed(() => {
    return profileForm.name !== displayData.value.name || 
           profileForm.email !== displayData.value.email;
});

const hasCompanyChanges = computed(() => {
    return Object.keys(companyDisplayData.value).some(key => 
        companyForm[key] !== companyDisplayData.value[key]
    );
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return 'Invalid Date';
    }
};

// Reset forms
const resetForm = () => {
    profileForm.name = displayData.value.name;
    profileForm.email = displayData.value.email;
    profileForm.clearErrors();
};

const cancelCompanyEdit = () => {
    Object.keys(companyDisplayData.value).forEach(key => {
        companyForm[key] = companyDisplayData.value[key];
    });
    companyForm.clearErrors();
    isEditingCompany.value = false;
};

// Company info table data
const companyInfoRows = computed(() => [
    { label: 'Company Name', key: 'name', icon: Building, value: companyDisplayData.value.name },
    { label: 'Address', key: 'address', icon: MapPin, value: companyDisplayData.value.address },
    { label: 'Phone', key: 'phone', icon: Phone, value: companyDisplayData.value.phone },
    { label: 'Email', key: 'email', icon: Mail, value: companyDisplayData.value.email },
    { label: 'Website', key: 'website', icon: Globe, value: companyDisplayData.value.website || 'N/A' },
    { label: 'Warranty Duration', key: 'warranty_duration', icon: Shield, value: `${companyDisplayData.value.warranty_duration} months` },
    { label: 'Support Info', key: 'support_info', icon: FileText, value: companyDisplayData.value.support_info },
    { label: 'Bank Name', key: 'bank_name', icon: CreditCard, value: companyDisplayData.value.bank_name },
    { label: 'SWIFT/BIC', key: 'swift_bic', icon: CreditCard, value: companyDisplayData.value.swift_bic },
    { label: 'Account Number', key: 'account_number', icon: CreditCard, value: companyDisplayData.value.account_number },
    { label: 'IBAN', key: 'iban', icon: CreditCard, value: companyDisplayData.value.iban },
    { label: 'Authorized Name', key: 'authorized_name', icon: User, value: companyDisplayData.value.authorized_name },
    { label: 'Authorized Title', key: 'authorized_title', icon: User, value: companyDisplayData.value.authorized_title },
    { label: 'Signature Email', key: 'signature_email', icon: Mail, value: companyDisplayData.value.signature_email },
    { label: 'Signature Phone', key: 'signature_phone', icon: Phone, value: companyDisplayData.value.signature_phone },
    { label: 'General Conditions URL', key: 'general_conditions_url', icon: Globe, value: companyDisplayData.value.general_conditions_url || 'N/A' },
    { label: 'VAT Number', key: 'vat_number', icon: FileText, value: companyDisplayData.value.vat_number },
]);
</script>

<template>
    <AdminApp>
        <div class="pt-6 space-y-8">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Admin Profile & Company Settings</h1>
                <p class="text-gray-600">Manage your account settings, security, and company information</p>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Admin Info Card -->
                <div class="lg:col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center text-blue-600">
                                <User class="w-5 h-5 mr-2" />
                                Admin Information
                            </CardTitle>
                            <CardDescription>Your current account details</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Admin Avatar/Icon -->
                            <div class="flex justify-center">
                                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center">
                                    <Shield class="w-10 h-10 text-blue-600" />
                                </div>
                            </div>

                            <!-- Admin Details -->
                            <div class="space-y-3 text-center">
                                <div>
                                    <h3 class="font-semibold text-lg">{{ displayData.name }}</h3>
                                    <p class="text-gray-500 flex items-center justify-center">
                                        <Mail class="w-4 h-4 mr-1" />
                                        {{ displayData.email }}
                                    </p>
                                </div>

                                <!-- Status Badge -->
                                <div class="flex justify-center">
                                    <Badge class="bg-green-100 text-green-800">
                                        <Shield class="w-3 h-3 mr-1" />
                                        Administrator
                                    </Badge>
                                </div>

                                <!-- Account Created -->
                                <div class="text-sm text-gray-500 flex items-center justify-center">
                                    <Calendar class="w-4 h-4 mr-1" />
                                    Member since {{ formatDate(displayData.created_at) }}
                                </div>

                                <!-- Changes indicator -->
                                <div v-if="hasProfileChanges" class="mt-3">
                                    <Badge class="bg-orange-100 text-orange-800">
                                        <Save class="w-3 h-3 mr-1" />
                                        Unsaved Changes
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Forms Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Profile Update Form -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <User class="w-5 h-5 mr-2" />
                                Update Profile Information
                            </CardTitle>
                            <CardDescription>
                                Update your account's profile information and email address
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="updateProfile" class="space-y-6">
                                <!-- Name Field -->
                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium">
                                        Full Name <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="name"
                                        v-model="profileForm.name"
                                        type="text"
                                        required
                                        :disabled="profileForm.processing"
                                        class="w-full"
                                        :class="{ 'border-orange-300 bg-orange-50': profileForm.name !== displayData.name }"
                                    />
                                    <div v-if="profileForm.errors.name" class="text-red-600 text-sm">
                                        {{ profileForm.errors.name }}
                                    </div>
                                    <div v-if="profileForm.name !== displayData.name" class="text-orange-600 text-xs">
                                        Changed from: "{{ displayData.name }}"
                                    </div>
                                </div>

                                <!-- Email Field -->
                                <div class="space-y-2">
                                    <Label for="email" class="text-sm font-medium">
                                        Email Address <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="email"
                                        v-model="profileForm.email"
                                        type="email"
                                        required
                                        :disabled="profileForm.processing"
                                        class="w-full"
                                        :class="{ 'border-orange-300 bg-orange-50': profileForm.email !== displayData.email }"
                                    />
                                    <div v-if="profileForm.errors.email" class="text-red-600 text-sm">
                                        {{ profileForm.errors.email }}
                                    </div>
                                    <div v-if="profileForm.email !== displayData.email" class="text-orange-600 text-xs">
                                        Changed from: "{{ displayData.email }}"
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="flex justify-between">
                                    <Button 
                                        v-if="hasProfileChanges"
                                        type="button"
                                        variant="outline"
                                        @click="resetForm"
                                        :disabled="profileForm.processing"
                                    >
                                        Cancel Changes
                                    </Button>
                                    <div v-else></div>
                                    
                                    <Button 
                                        type="submit" 
                                        :disabled="profileForm.processing || !hasProfileChanges"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6"
                                        :class="{ 'opacity-50 cursor-not-allowed': !hasProfileChanges }"
                                    >
                                        <Save class="w-4 h-4 mr-2" />
                                        {{ profileForm.processing ? 'Updating...' : 'Update Profile' }}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <!-- Password Change Form -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Lock class="w-5 h-5 mr-2" />
                                Change Password
                            </CardTitle>
                            <CardDescription>
                                Ensure your account is using a long, random password to stay secure
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="changePassword" class="space-y-6">
                                <!-- Current Password -->
                                <div class="space-y-2">
                                    <Label for="current_password" class="text-sm font-medium">
                                        Current Password <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="current_password"
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        required
                                        :disabled="passwordForm.processing"
                                        class="w-full"
                                        placeholder="Enter your current password"
                                    />
                                    <div v-if="passwordForm.errors.current_password" class="text-red-600 text-sm">
                                        {{ passwordForm.errors.current_password }}
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div class="space-y-2">
                                    <Label for="password" class="text-sm font-medium">
                                        New Password <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="password"
                                        v-model="passwordForm.password"
                                        type="password"
                                        required
                                        :disabled="passwordForm.processing"
                                        class="w-full"
                                        placeholder="Enter your new password"
                                        :class="{ 'border-green-300 bg-green-50': passwordForm.password.length >= 8 }"
                                    />
                                    <div v-if="passwordForm.errors.password" class="text-red-600 text-sm">
                                        {{ passwordForm.errors.password }}
                                    </div>
                                    <div v-if="passwordForm.password" class="text-xs">
                                        <span :class="passwordForm.password.length >= 8 ? 'text-green-600' : 'text-orange-600'">
                                            Password length: {{ passwordForm.password.length }}/8 minimum
                                        </span>
                                    </div>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="space-y-2">
                                    <Label for="password_confirmation" class="text-sm font-medium">
                                        Confirm New Password <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="password_confirmation"
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        required
                                        :disabled="passwordForm.processing"
                                        class="w-full"
                                        placeholder="Confirm your new password"
                                        :class="{ 
                                            'border-green-300 bg-green-50': passwordForm.password && passwordForm.password_confirmation && passwordForm.password === passwordForm.password_confirmation,
                                            'border-red-300 bg-red-50': passwordForm.password && passwordForm.password_confirmation && passwordForm.password !== passwordForm.password_confirmation
                                        }"
                                    />
                                    <div v-if="passwordForm.errors.password_confirmation" class="text-red-600 text-sm">
                                        {{ passwordForm.errors.password_confirmation }}
                                    </div>
                                    <div v-if="passwordForm.password && passwordForm.password_confirmation" class="text-xs">
                                        <span v-if="passwordForm.password === passwordForm.password_confirmation" class="text-green-600">
                                            ✓ Passwords match
                                        </span>
                                        <span v-else class="text-red-600">
                                            ✗ Passwords do not match
                                        </span>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end">
                                    <Button 
                                        type="submit" 
                                        :disabled="passwordForm.processing || !passwordForm.current_password || !passwordForm.password || !passwordForm.password_confirmation || passwordForm.password !== passwordForm.password_confirmation"
                                        class="bg-red-600 hover:bg-red-700 text-white px-6"
                                        :class="{ 
                                            'opacity-50 cursor-not-allowed': !passwordForm.current_password || !passwordForm.password || !passwordForm.password_confirmation || passwordForm.password !== passwordForm.password_confirmation
                                        }"
                                    >
                                        <Lock class="w-4 h-4 mr-2" />
                                        {{ passwordForm.processing ? 'Changing...' : 'Change Password' }}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Company Information Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center text-green-600">
                                <Building class="w-5 h-5 mr-2" />
                                Company Information
                            </CardTitle>
                            <CardDescription>
                                Manage your company details and business information
                            </CardDescription>
                        </div>
                        <Button 
                            v-if="!isEditingCompany"
                            @click="isEditingCompany = true"
                            class="bg-green-600 hover:bg-green-700 text-white"
                        >
                            <Edit class="w-4 h-4 mr-2" />
                            Edit Company Info
                        </Button>
                        <div v-else class="flex gap-2">
                            <Button 
                                variant="outline"
                                @click="cancelCompanyEdit"
                                :disabled="companyForm.processing"
                            >
                                Cancel
                            </Button>
                            <Button 
                                @click="updateCompanyInfo"
                                :disabled="companyForm.processing || !hasCompanyChanges"
                                class="bg-green-600 hover:bg-green-700 text-white"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ companyForm.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Display Mode -->
                    <div v-if="!isEditingCompany">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[300px]">Field</TableHead>
                                    <TableHead>Value</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="row in companyInfoRows" 
                                    :key="row.key"
                                    class="hover:bg-gray-50"
                                >
                                    <TableCell class="font-medium">
                                        <div class="flex items-center">
                                            <component :is="row.icon" class="w-4 h-4 mr-2 text-gray-500" />
                                            {{ row.label }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="row.key === 'website' || row.key === 'general_conditions_url'" class="text-blue-600 hover:underline">
                                            <a v-if="row.value !== 'N/A'" :href="row.value" target="_blank">{{ row.value }}</a>
                                            <span v-else>{{ row.value }}</span>
                                        </span>
                                        <span v-else-if="row.key === 'email' || row.key === 'signature_email'" class="text-blue-600">
                                            <a :href="`mailto:${row.value}`">{{ row.value }}</a>
                                        </span>
                                        <span v-else-if="row.key === 'phone' || row.key === 'signature_phone'" class="text-blue-600">
                                            <a :href="`tel:${row.value}`">{{ row.value }}</a>
                                        </span>
                                        <span v-else>{{ row.value }}</span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Edit Mode -->
                    <form v-else @submit.prevent="updateCompanyInfo" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Company Name -->
                            <div class="space-y-2">
                                <Label for="company_name" class="text-sm font-medium">
                                    Company Name <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="company_name"
                                    v-model="companyForm.name"
                                    type="text"
                                    required
                                    :disabled="companyForm.processing"
                                    class="w-full"
                                />
                                <div v-if="companyForm.errors.name" class="text-red-600 text-sm">
                                    {{ companyForm.errors.name }}
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <Label for="company_phone" class="text-sm font-medium">
                                    Phone Number <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="company_phone"
                                    v-model="companyForm.phone"
                                    type="text"
                                    required
                                    :disabled="companyForm.processing"
                                    class="w-full"
                                />
                                <div v-if="companyForm.errors.phone" class="text-red-600 text-sm">
                                    {{ companyForm.errors.phone }}
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <Label for="company_address" class="text-sm font-medium">
                                Company Address <span class="text-red-500">*</span>
                            </Label>
                            <Textarea
                                id="company_address"
                                v-model="companyForm.address"
                                required
                                :disabled="companyForm.processing"
                                class="w-full min-h-[80px]"
                            />
                            <div v-if="companyForm.errors.address" class="text-red-600 text-sm">
                                {{ companyForm.errors.address }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="company_email" class="text-sm font-medium">
                                    Company Email <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="company_email"
                                    v-model="companyForm.email"
                                    type="email"
                                    required
                                    :disabled="companyForm.processing"
                                    class="w-full"
                                />
                                <div v-if="companyForm.errors.email" class="text-red-600 text-sm">
                                    {{ companyForm.errors.email }}
                                </div>
                            </div>

                            <!-- Website -->
                            <div class="space-y-2">
                                <Label for="company_website" class="text-sm font-medium">
                                    Website
                                </Label>
                                <Input
                                    id="company_website"
                                    v-model="companyForm.website"
                                    type="url"
                                    :disabled="companyForm.processing"
                                    class="w-full"
                                />
                                <div v-if="companyForm.errors.website" class="text-red-600 text-sm">
                                    {{ companyForm.errors.website }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Warranty Duration -->
                            <div class="space-y-2">
                                <Label for="warranty_duration" class="text-sm font-medium">
                                    Warranty Duration (months) <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="warranty_duration"
                                    v-model="companyForm.warranty_duration"
                                    type="number"
                                    min="1"
                                    max="120"
                                    required
                                    :disabled="companyForm.processing"
                                    class="w-full"
                                />
                                <div v-if="companyForm.errors.warranty_duration" class="text-red-600 text-sm">
                                    {{ companyForm.errors.warranty_duration }}
                                </div>
                            </div>

                            <!-- VAT Number -->
                            <div class="space-y-2">
                                <Label for="vat_number" class="text-sm font-medium">
                                    VAT Number <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="vat_number"
                                    v-model="companyForm.vat_number"
                                    type="text"
                                    required
                                    :disabled="companyForm.processing"
                                    class="w-full"
                                />
                                <div v-if="companyForm.errors.vat_number" class="text-red-600 text-sm">
                                    {{ companyForm.errors.vat_number }}
                                </div>
                            </div>
                        </div>

                        <!-- Support Info -->
                        <div class="space-y-2">
                            <Label for="support_info" class="text-sm font-medium">
                                Support Information <span class="text-red-500">*</span>
                            </Label>
                            <Textarea
                                id="support_info"
                                v-model="companyForm.support_info"
                                required
                                :disabled="companyForm.processing"
                                class="w-full min-h-[80px]"
                            />
                            <div v-if="companyForm.errors.support_info" class="text-red-600 text-sm">
                                {{ companyForm.errors.support_info }}
                            </div>
                        </div>

                        <!-- Banking Information -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <CreditCard class="w-5 h-5 mr-2" />
                                Banking Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Bank Name -->
                                <div class="space-y-2">
                                    <Label for="bank_name" class="text-sm font-medium">
                                        Bank Name <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="bank_name"
                                        v-model="companyForm.bank_name"
                                        type="text"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.bank_name" class="text-red-600 text-sm">
                                        {{ companyForm.errors.bank_name }}
                                    </div>
                                </div>

                                <!-- SWIFT/BIC -->
                                <div class="space-y-2">
                                    <Label for="swift_bic" class="text-sm font-medium">
                                        SWIFT/BIC <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="swift_bic"
                                        v-model="companyForm.swift_bic"
                                        type="text"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.swift_bic" class="text-red-600 text-sm">
                                        {{ companyForm.errors.swift_bic }}
                                    </div>
                                </div>

                                <!-- Account Number -->
                                <div class="space-y-2">
                                    <Label for="account_number" class="text-sm font-medium">
                                        Account Number <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="account_number"
                                        v-model="companyForm.account_number"
                                        type="text"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.account_number" class="text-red-600 text-sm">
                                        {{ companyForm.errors.account_number }}
                                    </div>
                                </div>

                                <!-- IBAN -->
                                <div class="space-y-2">
                                    <Label for="iban" class="text-sm font-medium">
                                        IBAN <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="iban"
                                        v-model="companyForm.iban"
                                        type="text"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.iban" class="text-red-600 text-sm">
                                        {{ companyForm.errors.iban }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Authorized Signatory -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <User class="w-5 h-5 mr-2" />
                                Authorized Signatory
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Authorized Name -->
                                <div class="space-y-2">
                                    <Label for="authorized_name" class="text-sm font-medium">
                                        Authorized Name <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="authorized_name"
                                        v-model="companyForm.authorized_name"
                                        type="text"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.authorized_name" class="text-red-600 text-sm">
                                        {{ companyForm.errors.authorized_name }}
                                    </div>
                                </div>

                                <!-- Authorized Title -->
                                <div class="space-y-2">
                                    <Label for="authorized_title" class="text-sm font-medium">
                                        Authorized Title <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="authorized_title"
                                        v-model="companyForm.authorized_title"
                                        type="text"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.authorized_title" class="text-red-600 text-sm">
                                        {{ companyForm.errors.authorized_title }}
                                    </div>
                                </div>

                                <!-- Signature Email -->
                                <div class="space-y-2">
                                    <Label for="signature_email" class="text-sm font-medium">
                                        Signature Email <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="signature_email"
                                        v-model="companyForm.signature_email"
                                        type="email"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.signature_email" class="text-red-600 text-sm">
                                        {{ companyForm.errors.signature_email }}
                                    </div>
                                </div>

                                <!-- Signature Phone -->
                                <div class="space-y-2">
                                    <Label for="signature_phone" class="text-sm font-medium">
                                        Signature Phone <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="signature_phone"
                                        v-model="companyForm.signature_phone"
                                        type="text"
                                        required
                                        :disabled="companyForm.processing"
                                        class="w-full"
                                    />
                                    <div v-if="companyForm.errors.signature_phone" class="text-red-600 text-sm">
                                        {{ companyForm.errors.signature_phone }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Conditions URL -->
                        <div class="space-y-2">
                            <Label for="general_conditions_url" class="text-sm font-medium">
                                General Conditions URL
                            </Label>
                            <Input
                                id="general_conditions_url"
                                v-model="companyForm.general_conditions_url"
                                type="url"
                                :disabled="companyForm.processing"
                                class="w-full"
                            />
                            <div v-if="companyForm.errors.general_conditions_url" class="text-red-600 text-sm">
                                {{ companyForm.errors.general_conditions_url }}
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AdminApp>
</template>