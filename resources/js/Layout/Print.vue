<![CDATA[<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        required: false,
        default: 'Print View'
    }
});
</script>

<template>
    <div>
        <Head :title="title" />
        <main>
            <slot></slot>
        </main>
    </div>
</template>]]>
