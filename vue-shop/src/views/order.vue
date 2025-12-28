<template>
  <div class="orders-page">
    <h1>My Orders</h1>

    <div v-if="loading" class="loading">Loading orders...</div>

    <div v-else-if="orders.length === 0" class="empty">
      📦 No orders yet
    </div>

    <div v-else class="orders-list">
      <div class="order-card" v-for="order in orders" :key="order.id">
        <!-- Order Header -->
        <div class="order-header">
          <div>
            <h3>Order #{{ order.id }}</h3>
            <p class="date">{{ formatDate(order.created_at) }}</p>
          </div>
          <div class="status" :class="(order.status || '').toLowerCase()">
            {{ (order.status || '').toUpperCase() }}
          </div>
        </div>

        <!-- Order Items -->
        <div class="order-items">
          <div class="item" v-for="item in order.items" :key="item.id">
            <span>
              Product: {{ item.product?.name || item.product_name || item.name || ('#' + (item.product_id ?? 'N/A')) }}
            </span>
            <span>Qty: {{ item.quantity }}</span>
            <span>${{ parseFloat(item.price).toFixed(2) }}</span>
          </div>
        </div>

        <!-- Order Footer -->
        <div class="order-footer">
          <div class="total">
            Total: <strong>${{ parseFloat(order.total_price).toFixed(2) }}</strong>
          </div>
          <button class="delete-btn" @click="deleteOrder(order.id)">
            Delete Order
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../api/api";

const orders = ref([]);
const loading = ref(true);

async function loadOrders() {
  try {
    loading.value = true;
    const response = await api.get("/orders");
    orders.value = response.data.orders || response.data || [];
  } catch (error) {
    console.error("Failed to load orders:", error);
    orders.value = [];
  } finally {
    loading.value = false;
  }
}

async function deleteOrder(orderId) {
  if (!confirm("Are you sure you want to delete this order?")) return;

  try {
    await api.delete(`/orders/${orderId}`);
    await loadOrders();
  } catch (error) {
    console.error("Failed to delete order:", error);
    alert("Failed to delete order");
  }
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString() + " " + date.toLocaleTimeString();
}

onMounted(loadOrders);
</script>

<style scoped>
.orders-page {
  max-width: 1000px;
  margin: auto;
  padding: 20px;
}

h1 {
  margin-bottom: 30px;
}

.loading,
.empty {
  background: white;
  padding: 40px;
  text-align: center;
  border-radius: 10px;
  font-size: 18px;
}

.orders-list {
  display: grid;
  gap: 20px;
}

.order-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #f3f4f6;
}

.order-header h3 {
  margin: 0;
  font-size: 18px;
}

.date {
  color: #6b7280;
  font-size: 14px;
  margin: 5px 0 0 0;
}

.status {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status.pending {
  background: #fef3c7;
  color: #92400e;
}

.status.completed {
  background: #dcfce7;
  color: #166534;
}

.status.cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.order-items {
  margin-bottom: 15px;
  padding: 15px 0;
  border-bottom: 1px solid #f3f4f6;
}

.item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 14px;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total {
  font-size: 16px;
  font-weight: 500;
}

.delete-btn {
  background: #ef4444;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.delete-btn:hover {
  background: #dc2626;
}
</style>
