<script setup lang="ts">
import { ref, computed } from "vue";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Eye, EyeOff } from "lucide-vue-next";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const errors = ref<any>({});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post("/signup", {
        onError: (error) => {
            errors.value = error;
        },
    });
};
//---------form-------------
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-muted p-6 md:p-10"
    >
        <Card class="w-full max-w-sm">
            <CardHeader class="text-center">
                <CardTitle class="text-xl">Create Admin</CardTitle>
                <CardDescription>
                    Enter your information to create your account
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            :class="{ 'is-invalid': errors.name }"
                            type="text"
                            placeholder="John Doe"
                            required
                        />
                        <span v-if="errors.name">{{ errors.name }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            :class="{ 'is-invalid': errors.email }"
                            type="email"
                            placeholder="m@example.com"
                            required
                        />
                        <span v-if="errors.email">{{ errors.email }}</span>
                    </div>
                    <div class="flex flex-col gap-2 relative">
                        <Label for="password">Password</Label>
                        <div style="position: relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                :class="{ 'is-invalid': errors.password }"
                                placeholder="Enter your password"
                                required
                                style="padding-right: 2.5rem"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                tabindex="-1"
                                style="
                                    position: absolute;
                                    right: 0.5rem;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    background: none;
                                    border: none;
                                    padding: 0;
                                    cursor: pointer;
                                "
                            >
                                <component
                                    :is="showPassword ? EyeOff : Eye"
                                    class="w-4 h-4"
                                />
                            </button>
                        </div>
                        <span
                            v-if="errors.password"
                            style="color: red; font-size: 0.9em"
                            >{{ errors.password }}</span
                        >
                    </div>
                    <div class="flex flex-col gap-2 relative">
                        <Label for="password_confirmation">Confirm password</Label>
                        <div style="position: relative">
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="
                                    showConfirmPassword ? 'text' : 'password'
                                "
                                :class="{
                                    'is-invalid': errors.password_confirmation,
                                }"
                                placeholder="Confirm your password"
                                required
                                style="padding-right: 2.5rem"
                            />
                            <button
                                type="button"
                                @click="
                                    showConfirmPassword = !showConfirmPassword
                                "
                                tabindex="-1"
                                style="
                                    position: absolute;
                                    right: 0.5rem;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    background: none;
                                    border: none;
                                    padding: 0;
                                    cursor: pointer;
                                "
                            >
                                <component
                                    :is="showConfirmPassword ? EyeOff : Eye"
                                    class="w-4 h-4"
                                />
                            </button>
                        </div>
                        <span
                            v-if="errors.password_confirmation"
                            style="color: red; font-size: 0.9em"
                            >{{ errors.password_confirmation }}</span
                        >
                    </div>
                    <Button type="submit" class="w-full">Create account</Button>
                </form>
                <div class="text-center text-sm mt-4">
                    Already have an account?
                    <a
                        href="/login"
                        class="underline underline-offset-4 hover:text-primary"
                        >Sign in</a
                    >
                </div>
            </CardContent>
        </Card>
    </div>
</template>
