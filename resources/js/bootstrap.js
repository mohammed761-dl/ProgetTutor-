import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;

// Function to get fresh CSRF token
const getToken = () =>
    document.head.querySelector('meta[name="csrf-token"]')?.content;

// Add CSRF token to all requests
const token = getToken();
if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
    window.axios.defaults.headers.common["X-XSRF-TOKEN"] = token;
} else {
    console.error("CSRF token not found");
}

// Add response interceptor
window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 419) {
            // Get a fresh CSRF token without page reload
            const response = await fetch("/csrf-token");
            const { token } = await response.json();

            // Update the token in axios headers
            window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
            window.axios.defaults.headers.common["X-XSRF-TOKEN"] = token;

            // Update meta tag
            const metaToken = document.head.querySelector(
                'meta[name="csrf-token"]'
            );
            if (metaToken) metaToken.content = token;

            // Retry the original request
            const config = error.config;
            config.headers["X-CSRF-TOKEN"] = token;
            config.headers["X-XSRF-TOKEN"] = token;
            return window.axios.request(config);
        }
        if (error.response?.status === 401) {
            // Unauthorized, redirect to login
            window.location.href = "/serp-admin-login";
        }
        return Promise.reject(error);
    }
);
