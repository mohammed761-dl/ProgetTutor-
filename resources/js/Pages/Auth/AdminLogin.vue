<script lang="ts">
// filepath: d:\01_Projects\senselix\Laravel-senselix-ERP\resources\js\Pages\Auth\AdminLogin.vue
export const description = "Admin login page with clean design.";
</script>

<script setup lang="ts">
import { Shield, Eye, EyeOff } from "lucide-vue-next";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

const form = useForm({
    email: "",
    password: "",
});

const showPassword = ref(false);
const errors = ref<any>({});

const submit = () => {
    form.post("/serp-admin-login", {
        onError: (error) => {
            errors.value = error;
        },
    });
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="w-full max-w-sm">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                        <Shield class="h-5 w-5 text-white" />
                    </div>
                    <div class="text-left">
                        <h1 class="text-xl font-semibold text-gray-900">SenseLix ERP</h1>
                        <p class="text-sm text-gray-600">Admin Portal</p>
                    </div>
                </div>
            </div>

            <!-- Login Card -->
            <Card class="shadow-lg">
                <CardHeader class="text-center pb-4">
                    <CardTitle class="text-lg flex items-center justify-center gap-2">
                        <Shield class="h-4 w-4" />
                        Welcome Back
                    </CardTitle>
                    <CardDescription>
                        Enter your credentials to access the admin panel
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <form @submit.prevent="submit">
                        <div class="space-y-4">
                            <!-- Email Field -->
                            <div class="space-y-2">
                                <Label for="email">Email Address</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="admin@example.com"
                                    :class="{ 'border-destructive': errors.email }"
                                    required
                                />
                                <span v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</span>
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-2">
                                <Label for="password">Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Enter your password"
                                        :class="{ 'border-destructive': errors.password }"
                                        required
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground hover:text-foreground"
                                    >
                                        <Eye v-if="!showPassword" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <span v-if="errors.password" class="text-sm text-destructive">{{ errors.password }}</span>
                            </div>

                            <!-- Submit Button -->
                            <Button 
                                type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700"
                                :disabled="form.processing"
                            >
                                <Shield class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Authenticating...' : 'Access Admin Panel' }}
                            </Button>
                        </div>
                    </form>

                    <!-- Help Text -->
                    <div class="text-center">
                        <p class="text-sm text-muted-foreground">
                            Secure administrative portal access
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>