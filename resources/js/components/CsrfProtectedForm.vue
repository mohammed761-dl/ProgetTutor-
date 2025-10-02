<template>
    <form @submit.prevent="handleSubmit" v-bind="$attrs">
        <!-- CSRF Token (hidden input for traditional form submissions) -->
        <input type="hidden" name="_token" :value="csrfToken" />
        
        <!-- Form Method Override (if needed for PUT/PATCH/DELETE) -->
        <input v-if="method && method.toUpperCase() !== 'GET' && method.toUpperCase() !== 'POST'" 
               type="hidden" 
               name="_method" 
               :value="method.toUpperCase()" />
        
        <slot />
    </form>
</template>

<script setup>
import { useCsrf } from '@/composables/useCsrf';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    method: {
        type: String,
        default: 'POST'
    },
    action: {
        type: String,
        required: false
    },
    onSubmit: {
        type: Function,
        required: false
    }
});

const emit = defineEmits(['submit']);

const { csrfToken } = useCsrf();

const handleSubmit = (event) => {
    // Add CSRF token to form data if using traditional forms
    const formData = new FormData(event.target);
    formData.set('_token', csrfToken.value);
    
    if (props.method && props.method.toUpperCase() !== 'GET' && props.method.toUpperCase() !== 'POST') {
        formData.set('_method', props.method.toUpperCase());
    }
    
    // Emit the submit event with the form data
    emit('submit', { formData, event });
    
    // Call the onSubmit prop if provided
    if (props.onSubmit) {
        props.onSubmit({ formData, event });
    }
};
</script>

<script>
export default {
    name: 'CsrfProtectedForm',
    inheritAttrs: false
};
</script>
