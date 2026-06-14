import axios from 'axios';

// Get base URL from localStorage or use default
const getBaseUrl = () => {
    return localStorage.getItem('apiBaseUrl') || 'http://localhost:8000';
};

const api = axios.create({
    baseURL: getBaseUrl() + '/api',
    withCredentials: true, // Send cookies across domains
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Request Interceptor to dynamically set baseURL if it changes
api.interceptors.request.use(config => {
    config.baseURL = getBaseUrl() + '/api';
    return config;
});

// Response Interceptor to handle global errors (e.g., 401 Unauthorized)
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Redirect to login or emit event
            window.dispatchEvent(new CustomEvent('api:unauthorized'));
        }
        return Promise.reject(error);
    }
);

export default api;
