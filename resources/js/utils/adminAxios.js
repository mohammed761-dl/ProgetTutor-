import axios from "axios";

const adminAxios = axios.create();

// Add a request interceptor
adminAxios.interceptors.request.use(
    (config) => {
        // Get the CSRF token from meta tag
        const token = document.head.querySelector(
            'meta[name="csrf-token"]'
        )?.content;
        if (token) {
            config.headers["X-CSRF-TOKEN"] = token;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add a response interceptor to handle CSRF token mismatches
adminAxios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 419) {
            // Get a fresh CSRF token without page reload
            const response = await fetch("/csrf-token");
            const { token } = await response.json();

            // Update the token in axios headers
            adminAxios.defaults.headers["X-CSRF-TOKEN"] = token;
            adminAxios.defaults.headers["X-XSRF-TOKEN"] = token;

            // Update meta tag
            const metaToken = document.head.querySelector(
                'meta[name="csrf-token"]'
            );
            if (metaToken) metaToken.content = token;

            // Retry the original request
            const config = error.config;
            config.headers["X-CSRF-TOKEN"] = token;
            config.headers["X-XSRF-TOKEN"] = token;
            return adminAxios.request(config);
        }
        if (error.response?.status === 401) {
            // Unauthorized, redirect to login
            window.location.href = "/serp-admin-login";
        }
        return Promise.reject(error);
    }
);

export default adminAxios;
