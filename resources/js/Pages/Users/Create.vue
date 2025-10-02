<script setup>
// filepath: resources/js/Pages/Users/Create.vue

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
import { ArrowLeft, UserPlus } from "lucide-vue-next";

const props = defineProps({
    roles: {
        type: Array,
        default: () => ['CEO', 'Commercial', 'Viewer']
    }
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'Viewer'
});

const submit = () => {
    form.post('/User', {
        onSuccess: () => {
            console.log('User created successfully');
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
                <Link href="/User">
                    <Button variant="outline" size="sm" class="flex items-center">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Users
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New User</h1>
                    <p class="text-gray-600">Add a new user to the system</p>
                </div>
            </div>

            <!-- Create Form -->
            <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <UserPlus class="w-5 h-5 mr-2" />
                            User Information
                        </CardTitle>
                        <CardDescription>
                            Enter the details for the new user account
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name Field -->
                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-medium">
                                    Full Name <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Enter full name"
                                    required
                                    :disabled="form.processing"
                                    class="w-full"
                                />
                                <div v-if="form.errors.name" class="text-red-600 text-sm">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Email Field -->
                            <div class="space-y-2">
                                <Label for="email" class="text-sm font-medium">
                                    Email Address <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Enter email address"
                                    required
                                    :disabled="form.processing"
                                    class="w-full"
                                />
                                <div v-if="form.errors.email" class="text-red-600 text-sm">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Role Field -->
                            <div class="space-y-2">
                                <Label for="role" class="text-sm font-medium">
                                    User Role <span class="text-red-500">*</span>
                                </Label>
                                <select 
                                    id="role"
                                    v-model="form.role"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :disabled="form.processing"
                                >
                                    <option v-for="role in roles" :key="role" :value="role">
                                        {{ role }}
                                    </option>
                                </select>
                                <div v-if="form.errors.role" class="text-red-600 text-sm">
                                    {{ form.errors.role }}
                                </div>
                                <p class="text-xs text-gray-500">
                                    Choose the appropriate role for this user
                                </p>
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-2">
                                <Label for="password" class="text-sm font-medium">
                                    Password <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Enter password (minimum 8 characters)"
                                    required
                                    :disabled="form.processing"
                                    class="w-full"
                                />
                                <div v-if="form.errors.password" class="text-red-600 text-sm">
                                    {{ form.errors.password }}
                                </div>
                                <p class="text-xs text-gray-500">
                                    Password must be at least 8 characters long
                                </p>
                            </div>

                            <!-- Confirm Password Field -->
                            <div class="space-y-2">
                                <Label for="password_confirmation" class="text-sm font-medium">
                                    Confirm Password <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Confirm password"
                                    required
                                    :disabled="form.processing"
                                    class="w-full"
                                />
                                <div v-if="form.errors.password_confirmation" class="text-red-600 text-sm">
                                    {{ form.errors.password_confirmation }}
                                </div>
                            </div>

                            <!-- Submit Actions -->
                            <div class="flex items-center justify-between pt-6 border-t">
                                <Link href="/User">
                                    <Button variant="outline" type="button" :disabled="form.processing">
                                        Cancel
                                    </Button>
                                </Link>
                                
                                <Button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6"
                                >
                                    <UserPlus class="w-4 h-4 mr-2" />
                                    {{ form.processing ? 'Creating User...' : 'Create User' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Role Information Card -->
            <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Role Descriptions</CardTitle>
                        <CardDescription>
                            Learn about the different user roles and their permissions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                                <div>
                                    <h4 class="font-medium text-sm">CEO</h4>
                                    <p class="text-sm text-gray-600">Full system access and administration rights</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                <div>
                                    <h4 class="font-medium text-sm">Commercial</h4>
                                    <p class="text-sm text-gray-600">Access to sales and commercial features</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div>
                                    <h4 class="font-medium text-sm">Viewer</h4>
                                    <p class="text-sm text-gray-600">Read-only access to system information</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>