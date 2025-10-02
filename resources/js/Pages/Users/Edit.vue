<script setup>
// filepath: resources/js/Pages/Users/Edit.vue

import AdminApp from "../../Layout/AdminApp.vue";
import { useForm, Link } from "@inertiajs/vue3";
import Button from "@/components/ui/button/Button.vue";
import Input from "@/components/ui/input/Input.vue";
import Label from "@/components/ui/label/Label.vue";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import { ArrowLeft, Save } from "lucide-vue-next";

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    roles: {
        type: Array,
        default: () => ['CEO', 'Commercial', 'Viewer']
    }
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role
});

const submit = () => {
    // ✅ Fix: Use correct route pattern matching web.php
    form.put(`/User/${props.user.id_user}`, {
        onSuccess: () => {
            console.log('User updated successfully');
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
                    <h1 class="text-2xl font-bold text-gray-900">Edit User: {{ user.name }}</h1>
                    <p class="text-gray-600">Update user information and permissions</p>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Save class="w-5 h-5 mr-2" />
                            Update User Details
                        </CardTitle>
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
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-2">
                                <Label for="password" class="text-sm font-medium">
                                    New Password (leave blank to keep current)
                                </Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Enter new password"
                                    :disabled="form.processing"
                                    class="w-full"
                                />
                                <div v-if="form.errors.password" class="text-red-600 text-sm">
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <!-- Confirm Password Field -->
                            <div v-if="form.password" class="space-y-2">
                                <Label for="password_confirmation" class="text-sm font-medium">
                                    Confirm New Password
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Confirm new password"
                                    :disabled="form.processing"
                                    class="w-full"
                                />
                                <div v-if="form.errors.password_confirmation" class="text-red-600 text-sm">
                                    {{ form.errors.password_confirmation }}
                                </div>
                            </div>

                            <!-- Form Actions -->
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
                                    <Save class="w-4 h-4 mr-2" />
                                    {{ form.processing ? 'Updating User...' : 'Update User' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminApp>
</template>