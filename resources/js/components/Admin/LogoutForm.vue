<template>
    <form @submit.prevent="submit" class="inline">
        <button type="submit" class="text-red-600 hover:text-red-800">
            <slot>Logout</slot>
        </button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({});

const submit = () => {
    form.post('/admin-logout', {
        preserveScroll: true,
        onSuccess: () => {
            window.location.href = '/serp-admin-login';
        },
    });
};
</script>
