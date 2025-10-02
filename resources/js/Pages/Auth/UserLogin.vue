<script lang="ts">
// filepath: d:\01_Projects\senselix\Laravel-senselix-ERP\resources\js\Pages\Auth\UserLogin.vue
export const description = "User login page with minimalist design.";
</script>

<script setup lang="ts">
import { User, Lock, Eye, EyeOff } from "lucide-vue-next";
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
    form.post("/login", {
        onError: (error) => {
            errors.value = error;
        },
    });
};
</script>

<template>
    <div class="min-h-screen bg-background flex items-center justify-center p-4">
        <div class="w-full max-w-sm">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div  class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary">
                        <User class="h-5 w-5 text-primary-foreground" />
                    </div>
                    <div class="text-left">
                        <h1 class="text-xl font-semibold">Clarity OS</h1>
                    </div>
                </div>
            </div>

            <!-- Login Card -->
            <Card>
                <CardHeader class="text-center pb-4">
                    <CardTitle class="text-lg flex items-center justify-center gap-2">
                        <Lock class="h-4 w-4" />
                        Access to your account
                    </CardTitle>
                    <CardDescription>
                        Sign in to your account to continue
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
                                    placeholder="Enter your email"
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
                                class="w-full"
                                :disabled="form.processing"
                            >
                                <User class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Signing in...' : 'Sign In' }}
                            </Button>
                        </div>
                    </form>

                    <!-- Security Notice -->
                    <div class="mt-4 p-3 bg-muted/50 rounded-lg border">
                        <p class="text-xs text-muted-foreground text-center">
                            <Lock class="h-3 w-3 inline mr-1" />
                            Secure user portal
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>