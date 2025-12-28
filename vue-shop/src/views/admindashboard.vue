<template>
  <div class="admin-dashboard">
    <h1>Dashboard Overview</h1>
    <div v-if="loading">Loading dashboard statistics...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value">{{ stats.product_count || 0 }}</div>
        <div class="stat-label">Products</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value">{{ stats.user_count || 0 }}</div>
        <div class="stat-label">Users</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-value">{{ stats.order_count || 0 }}</div>
        <div class="stat-label">Orders</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
  product_count: 0,
  user_count: 0,
  order_count: 0
});
const loading = ref(true);
const error = ref(null);

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
  }
});

const fetchDashboardStats = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    // First ensure we have a CSRF cookie
    await axios.get('http://127.0.0.1:8000/sanctum/csrf-cookie', {
      withCredentials: true
    });

    const response = await api.get('/admin/dashboard');
    stats.value = response.data;
  } catch (err) {
    console.error('API Error:', err);
    if (err.response?.status === 401) {
      error.value = 'Please login to view dashboard';
    } else {
      error.value = 'Failed to fetch dashboard statistics. Please try again.';
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardStats();
});
</script>

<style scoped>
.admin-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  font-size: 1.8rem;
  color: #2c3e50;
  margin-bottom: 2rem;
  font-weight: 600;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  text-align: center;
  border: 1px solid #eaeaea;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
}

.stat-value {
  font-size: 2.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0.5rem 0;
}

.stat-label {
  font-size: 1rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 500;
}

.error {
  color: #ef4444;
  background-color: #fef2f2;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  margin: 1rem 0;
  border: 1px solid #fecaca;
}
</style>