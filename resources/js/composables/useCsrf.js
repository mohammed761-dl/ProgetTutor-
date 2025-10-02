import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

/**
 * Composable for CSRF token handling
 */
export function useCsrf() {
    const page = usePage();

    // Get CSRF token from shared data or meta tag
    const csrfToken = computed(() => {
        return (
            page.props.csrf_token ||
            document.head.querySelector('meta[name="csrf-token"]')?.content
        );
    });

    // Get CSRF token for form submissions
    const getCsrfToken = () => {
        return csrfToken.value;
    };

    // Add CSRF token to form data
    const addCsrfToForm = (formData) => {
        if (formData instanceof FormData) {
            formData.append("_token", csrfToken.value);
        } else if (typeof formData === "object") {
            formData._token = csrfToken.value;
        }
        return formData;
    };

    // Create axios config with CSRF token
    const createAxiosConfig = (config = {}) => {
        return {
            ...config,
            headers: {
                "X-CSRF-TOKEN": csrfToken.value,
                "X-Requested-With": "XMLHttpRequest",
                ...config.headers,
            },
        };
    };

    return {
        csrfToken,
        getCsrfToken,
        addCsrfToForm,
        createAxiosConfig,
    };
}
