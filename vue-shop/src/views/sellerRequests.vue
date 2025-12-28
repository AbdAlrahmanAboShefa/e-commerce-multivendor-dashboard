<template>
  <div class="seller-requests">
    <h2>Seller Requests</h2>
    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else>
      <div v-for="request in sellerRequests" :key="request.id" class="request">
        <div class="user-info">
          <h3>{{ request.user.name }}</h3>
          <p>{{ request.user.email }}</p>
          <p>Status: {{ request.status }}</p>
        </div>
        <div v-if="request.status === 'pending'" class="actions">
          <button @click="handleApprove(request.id)" :disabled="processing">Approve</button>
          <button @click="handleReject(request.id)" :disabled="processing">Reject</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const sellerRequests = ref([]);
const loading = ref(true);
const error = ref(null);
const processing = ref(false);

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
  }
});

const fetchSellerRequests = async () => {
  try {
    loading.value = true;
    const response = await api.get('/admin/seler_requests');
    sellerRequests.value = response.data.seler_requests;
    error.value = null;
  } catch (err) {
    error.value = 'Failed to fetch seller requests. Please try again.';
    console.error('Error fetching seller requests:', err);
    if (err.response?.status === 401) {
      error.value = 'Please login to view seller requests';
    }
  } finally {
    loading.value = false;
  }
};

const handleApprove = async (id) => {
  await updateRequestStatus(id, 'approve');
};

const handleReject = async (id) => {
  await updateRequestStatus(id, 'reject');
};

const updateRequestStatus = async (id, action) => {
  if (processing.value) return;
  
  try {
    processing.value = true;
    await api.post(`/admin/seler_requests/${id}/${action}`);
    await fetchSellerRequests();
  } catch (err) {
    error.value = `Failed to ${action} request. Please try again.`;
    console.error(`Error ${action}ing request:`, err);
    if (err.response?.status === 401) {
      error.value = 'Session expired. Please login again.';
    }
  } finally {
    processing.value = false;
  }
};

onMounted(() => {
  fetchSellerRequests();
});
</script>

<style scoped>
.seller-requests {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.request {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-info {
  flex: 1;
}

.actions {
  display: flex;
  gap: 10px;
}

button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

button:first-child {
  background-color: #4caf50;
  color: white;
}

button:last-child {
  background-color: #f44336;
  color: white;
}

.error {
  color: #f44336;
  padding: 10px;
  background-color: #ffebee;
  border-radius: 4px;
  margin: 10px 0;
}
</style>