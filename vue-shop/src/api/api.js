import axios from "axios";

const api = axios.create({
  baseURL: "http://localhost:8000/api"
});
// Send cookies with requests to support Sanctum cookie authentication
api.defaults.withCredentials = true;

// Add a request interceptor to include the auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add a response interceptor to handle 401 errors (unauthorized)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      // Token expired or invalid, clear auth data
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      // Optionally redirect to login
      if (window.location.pathname !== '/login') {
        // You can emit an event or use router here if you have one
      }
    }
    return Promise.reject(error);
  }
);

export default api;
