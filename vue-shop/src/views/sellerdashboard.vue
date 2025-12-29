<template>
  <div class="seller-dashboard">
    <h1>Seller Dashboard</h1>

    <!-- Stats -->
    <div class="stats">
      <div class="stat-card">
        <h3>Products</h3>
        <p>{{ stats.products }}</p>
      </div>

      <div class="stat-card">
        <h3>Delivered Orders</h3>
        <p>{{ stats.deliveredOrders }}</p>
      </div>

      <div class="stat-card">
        <h3>Earnings</h3>
        <p>${{ stats.earnings }}</p>
      </div>
    </div>

    <!-- Orders Table -->
    <h2>Orders</h2>

    <table v-if="orders.length">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Product</th>
          <th>Qty</th>
          <th>Total</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="o in orders" :key="o.id">
          <td>#{{ o.order_id }}</td>
          <td>{{ o.product?.name || 'Product #' + o.product_id }}</td>
          <td>{{ o.quantity }}</td>
          <td>${{ (parseFloat(o.price) * o.quantity).toFixed(2) }}</td>
          <td>
            <span :class="['status', o.status]">{{ o.status }}</span>
          </td>
          <td>
            <button
              v-if="o.status !== 'delivered'"
              @click="updateStatus(o.id, 'delivered')"
            >
              Update Status
            </button>
            <span v-else>✔</span>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>No orders yet.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/api';

const stats = ref({
  products: 0,
  deliveredOrders: 0,
  earnings: '0.00'
});

const orders = ref([]);

/* =====================
   Fetch Dashboard Data
===================== */
const fetchDashboard = async () => {
  try {
    // 1️⃣ Dashboard stats (SOURCE OF TRUTH)
    const resStats = await api.get('/seller/dashboard');
    stats.value.products = Number(resStats.data.product_count);
    stats.value.deliveredOrders = Number(resStats.data.order_count);
    stats.value.earnings = resStats.data.total_earning;

    // 2️⃣ Orders list (for table)
    const resOrders = await api.get('/seller/order/items');
    orders.value = Array.isArray(resOrders.data) ? resOrders.data : [];
  } catch (e) {
    console.error('Failed to fetch seller dashboard:', e);
    orders.value = [];
  }
};

/* =====================
   Update Order Status
===================== */
const updateStatus = async (orderItemId, newStatus) => {
  try {
    await api.patch(`/seller/order/items/${orderItemId}/status`, {
      status: newStatus
    });
    fetchDashboard();
  } catch (e) {
    console.error('Failed to update order status:', e);
    alert('Failed to update order status');
  }
};

onMounted(fetchDashboard);
</script>

<style scoped>
.seller-dashboard {
  padding: 24px;
}

.stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: #111827;
  color: #fff;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
}

.stat-card h3 {
  font-size: 14px;
  opacity: 0.8;
}

.stat-card p {
  font-size: 28px;
  font-weight: bold;
  margin-top: 10px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
}

.status {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
}

.status.pending {
  background: #fde68a;
}

.status.delivered {
  background: #bbf7d0;
}

button {
  padding: 6px 10px;
  cursor: pointer;
  border: none;
  background: #3b82f6;
  color: #fff;
  border-radius: 6px;
  transition: all 0.2s;
}

button:hover {
  background: #2563eb;
}
</style>
