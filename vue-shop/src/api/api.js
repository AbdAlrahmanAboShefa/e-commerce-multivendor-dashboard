import axios from 'axios';
import { useRouter } from 'vue-router';
import { toast } from 'vue-toastification';

// Create axios instance with default config
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  timeout: 30000, // 30 seconds
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true, // Important for cookies, sessions
});

// Request interceptor to add auth token to requests
api.interceptors.request.use(
  (config) => {
    // Get token from localStorage
    const token = localStorage.getItem('token') || localStorage.getItem('auth_token');
    
    // If token exists, add it to the headers
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Log request for debugging
    console.log(`[API Request] ${config.method.toUpperCase()} ${config.url}`, {
      params: config.params,
      data: config.data,
      headers: config.headers,
    });
    
    return config;
  },
  (error) => {
    console.error('[API Request Error]', error);
    return Promise.reject(error);
  }
);

// Response interceptor to handle common errors
api.interceptors.response.use(
  (response) => {
    // Log successful responses for debugging
    console.log(`[API Response] ${response.config.method.toUpperCase()} ${response.config.url}`, {
      status: response.status,
      data: response.data,
    });
    
    // Handle empty responses
    if (!response.data) {
      console.warn('Empty API response received');
    }
    
    return response;
  },
  async (error) => {
    const originalRequest = error.config;
    
    // Log error for debugging
    console.error('[API Error]', {
      url: originalRequest?.url,
      status: error.response?.status,
      data: error.response?.data,
      config: error.config,
    });
    
    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      // Clear auth data
      localStorage.removeItem('token');
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      
      // Show error message
      toast.error('Your session has expired. Please log in again.');
      
      // Redirect to login if not already there
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }
    
    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      toast.error('You do not have permission to perform this action.');
    }
    
    // Handle 404 Not Found
    if (error.response?.status === 404) {
      toast.error('The requested resource was not found.');
    }
    
    // Handle 422 Validation Errors
    if (error.response?.status === 422) {
      // Let the component handle validation errors
      console.log('Validation errors:', error.response.data.errors);
    }
    
    // Handle 500 Server Error
    if (error.response?.status >= 500) {
      toast.error('A server error occurred. Please try again later.');
    }
    
    // Handle network errors
    if (!error.response) {
      toast.error('Network error. Please check your connection.');
    }
    
    return Promise.reject(error);
  }
);

// Helper function to set auth token
export const setAuthToken = (token) => {
  if (token) {
    localStorage.setItem('token', token);
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common['Authorization'];
  }
};

// Helper function to clear auth data
export const clearAuth = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  delete api.defaults.headers.common['Authorization'];
};

export default api;
