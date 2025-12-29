<template>
  <aside class="sidebar" :class="{ open: isOpen }">
    <!-- Close Button (Mobile) -->
    <button v-if="isOpen" class="close-btn" @click="toggleSidebar">✕</button>

    <nav class="sidebar-nav">
      <!-- Navigation Items -->
      <div class="nav-section">
        <h3 class="nav-label">Main</h3>
        
        <!-- Regular User -->
        <RouterLink v-if="isUser" to="/" class="nav-item" @click="closeSidebar">
          <span class="icon">🏠</span>
          <span class="label">Products</span>
        </RouterLink>

        <RouterLink v-if="isUser" to="/cart" class="nav-item" @click="closeSidebar">
          <span class="icon">🛒</span>
          <span class="label">Cart</span>
        </RouterLink>

        <RouterLink v-if="isUser" to="/orders" class="nav-item" @click="closeSidebar">
          <span class="icon">📦</span>
          <span class="label">Orders</span>
        </RouterLink>
      </div>

      <!-- Admin Section -->
      <div v-if="isAdmin" class="nav-section">
        <h3 class="nav-label">Admin</h3>
        
        <RouterLink to="/admin/dashboard" class="nav-item" @click="closeSidebar">
          <span class="icon">📊</span>
          <span class="label">Dashboard</span>
        </RouterLink>

        <RouterLink to="/admin/seler_requests" class="nav-item" @click="closeSidebar">
          <span class="icon">✉️</span>
          <span class="label">Seller Requests</span>
        </RouterLink>

        <RouterLink to="/admin/categories" class="nav-item" @click="closeSidebar">
          <span class="icon">📁</span>
          <span class="label">Categories</span>
        </RouterLink>
      </div>

      <!-- Seller Section -->
      <div v-if="isSeller" class="nav-section">
        <h3 class="nav-label">Seller</h3>

        <RouterLink to="/seller/dashboard" class="nav-item" @click="closeSidebar">
          <span class="icon">📊</span>
          <span class="label">Dashboard</span>
        </RouterLink>

        <RouterLink to="/seller/products" class="nav-item" @click="closeSidebar">
          <span class="icon">📦</span>
          <span class="label">Products</span>
        </RouterLink>
      </div>

      <!-- Become Seller Button (for users not yet sellers) -->
      <div v-if="isUser && !isSeller" class="nav-section">
        <button class="nav-item" @click="becomeSeller">
          <span class="icon">🏪</span>
          <span class="label">Become Seller</span>
        </button>
      </div>

      <!-- Authentication Section -->
      <div class="nav-section auth-section">
        <h3 class="nav-label">Account</h3>
        
        <RouterLink v-if="!isAuthenticated" to="/login" class="nav-item" @click="closeSidebar">
          <span class="icon">🔐</span>
          <span class="label">Login</span>
        </RouterLink>

        <RouterLink v-if="!isAuthenticated" to="/register" class="nav-item" @click="closeSidebar">
          <span class="icon">✍️</span>
          <span class="label">Register</span>
        </RouterLink>

        <button v-if="isAuthenticated" class="nav-item logout-btn" @click="logout">
          <span class="icon">🚪</span>
          <span class="label">Logout</span>
        </button>
      </div>

      <!-- Divider -->
      <div class="sidebar-divider"></div>

      <!-- User Info -->
      <div v-if="isAuthenticated" class="user-info">
        <div class="user-icon">👤</div>
        <div class="user-name">{{ userName || 'User' }}</div>
      </div>
    </nav>
  </aside>

  <!-- Overlay (Mobile) -->
  <div v-if="isOpen" class="sidebar-overlay" @click="closeSidebar"></div>

  <!-- Toggle Button (Mobile) -->
  <button class="sidebar-toggle" @click="toggleSidebar">☰</button>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/api';

const router = useRouter();
const isOpen = ref(false);
const isAuthenticated = ref(false);
const userName = ref('');
const user = ref(null);

/* =========================
   Computed roles
========================= */
const isAdmin = computed(() => {
  if (!user.value) return false;
  return user.value.role === 'admin' || (Array.isArray(user.value.roles) && user.value.roles.some(r => r === 'admin' || r?.name === 'admin'));
});

const isSeller = computed(() => {
  if (!user.value) return false;
  return Array.isArray(user.value.roles) && user.value.roles.some(r => r === 'seller' || r?.name === 'seller');
});

const isUser = computed(() => {
  if (!user.value) return false;
  return Array.isArray(user.value.roles) && user.value.roles.some(r => r === 'user' || r?.name === 'user');
});

/* =========================
   Sidebar functions
========================= */
const toggleSidebar = () => { isOpen.value = !isOpen.value; };
const closeSidebar = () => { isOpen.value = false; };

const logout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  isAuthenticated.value = false;
  closeSidebar();
  router.push('/login');
};

async function becomeSeller() {
  try {
    await api.post('/seler_requests');
    localStorage.setItem('seller_request_sent', '1');
    alert('Seller request sent.');
  } catch (e) {
    console.error(e);
    alert('Failed to send seller request.');
  }
}

/* =========================
   Load user
========================= */
onMounted(() => {
  const token = localStorage.getItem('auth_token');
  isAuthenticated.value = !!token;

  const raw = localStorage.getItem('user');
  if (raw) {
    try {
      const userData = JSON.parse(raw);
      user.value = userData;
      userName.value = userData.name || userData.email || 'User';
    } catch {
      userName.value = 'User';
      user.value = null;
    }
  }

  async function fetchUser() {
    if (!isAuthenticated.value) return;
    try {
      const res = await api.get('/user');
      const data = res.data?.user || res.data;
      if (data) {
        user.value = data;
        userName.value = data.name || data.email || 'User';
        try { localStorage.setItem('user', JSON.stringify(user.value)); } catch {}
      }
    } catch {}
  }

  fetchUser();
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Desktop Sidebar */
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: 250px;
  height: 100vh;
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  border-right: 1px solid #374151;
  padding: 24px 0;
  z-index: 999;
  overflow-y: auto;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
}

.close-btn {
  display: none;
  position: absolute;
  top: 16px;
  right: 16px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  padding: 4px 8px;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: 100%;
}

.nav-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 0 12px;
}

.nav-label {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  color: #9ca3af;
  letter-spacing: 0.5px;
  padding: 0 16px;
  margin-top: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #e5e7eb;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
  background: none;
  font-size: 14px;
  font-weight: 500;
  font-family: inherit;
}

.nav-item:hover {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  transform: translateX(4px);
}

.nav-item.router-link-active {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  border-left: 3px solid #3b82f6;
  padding-left: 13px;
}

.icon {
  font-size: 18px;
  min-width: 24px;
  text-align: center;
}

.label {
  font-size: 14px;
}

.logout-btn {
  width: 100%;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.auth-section {
  margin-top: auto;
  order: 10;
}

.sidebar-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 12px 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 8px;
  margin: 0 12px;
  border-left: 3px solid #3b82f6;
}

.user-icon {
  font-size: 24px;
}

.user-name {
  font-size: 13px;
  color: #e5e7eb;
  font-weight: 500;
  word-break: break-word;
}

.sidebar-toggle {
  display: none;
  position: fixed;
  top: 20px;
  left: 20px;
  width: 40px;
  height: 40px;
  background: #1f2937;
  border: 1px solid #374151;
  color: white;
  font-size: 20px;
  border-radius: 6px;
  cursor: pointer;
  z-index: 1000;
  transition: all 0.3s ease;
}

.sidebar-toggle:hover {
  background: #374151;
}

.sidebar-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    width: 280px;
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar.open .close-btn {
    display: block;
  }

  .sidebar-toggle {
    display: block;
  }

  .sidebar-overlay {
    display: block;
  }

  .sidebar.open ~ .sidebar-overlay {
    display: block;
  }
}

/* Scrollbar Styling */
.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
  background: #374151;
  border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: #4b5563;
}
</style>
