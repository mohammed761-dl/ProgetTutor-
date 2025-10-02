/**
 * Get CSRF token from meta tag
 */
export const getCsrfToken = () => {
    return document.head.querySelector('meta[name="csrf-token"]')?.content;
};

/**
 * Add CSRF token to form data
 */
export const addCsrfToFormData = (formData) => {
    const token = getCsrfToken();
    if (formData instanceof FormData) {
        formData.append("_token", token);
    } else if (typeof formData === "object") {
        formData._token = token;
    }
    return formData;
};
