<template>
  <nav class="navbar">
    <div class="logo">VueShop</div>

    <div class="search-container" v-if="!isAuthPage">
      <input
        v-model="searchQuery"
        @keyup.enter="performSearch"
        type="text"
        placeholder="Search products..."
        class="search-input"
      />
      <button @click="performSearch" class="search-btn">🔍</button>
    </div>

    <div class="links"></div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../api/api";

const router = useRouter();
const route = useRoute();

const isAuthenticated = ref(!!localStorage.getItem("auth_token"));
const searchQuery = ref("");
const isAuthPage = computed(() => route.path === "/login" || route.path === "/register");

const user = ref(null);
try {
  user.value = JSON.parse(localStorage.getItem("user") || "null");
} catch (e) {
  user.value = null;
}

async function fetchUser() {
  try {
    const res = await api.get('/user');
    // if request succeeds, we are authenticated (either via token or cookie)
    isAuthenticated.value = true;
    // API may return data in different shapes; adapt accordingly
    user.value = res.data || res.data.user || user.value;
    // persist to localStorage to keep prior behavior
    try { localStorage.setItem('user', JSON.stringify(user.value)); } catch(e){}
    try {
      // eslint-disable-next-line no-console
      console.debug('navbar fetchUser ->', { user: user.value, isAuthenticated: isAuthenticated.value, hasSpatieUserRole: hasSpatieUserRole.value, requestSent: requestSent.value });
    } catch (e) {}
    // update showBecomeSeller based on returned roles
    try { showBecomeSeller.value = hasSpatieUserRole.value; } catch(e){}
  } catch (e) {
    // not authenticated or fetch failed
    console.debug('navbar fetchUser failed', e && e.response ? e.response.status : e);
    // keep isAuthenticated if token exists locally
    isAuthenticated.value = !!localStorage.getItem('auth_token');
  }
}
  try {
    const raw = localStorage.getItem('user');
    if (raw) {
      const parsed = JSON.parse(raw);
      user.value = parsed;
      showBecomeSeller.value = checkSpatieUser(parsed);
    }
  } catch (e) {}

  // Always attempt to fetch current user from API (supports cookie or token auth)
  fetchUser();
  try {
    // eslint-disable-next-line no-console
    console.debug('navbar mount ->', { user: user.value, isAuthenticated: isAuthenticated.value, hasSpatieUserRole: hasSpatieUserRole.value, showBecomeSeller: showBecomeSeller.value, requestSent: requestSent.value });
  } catch (e) {}
const hasSpatieUserRole = computed(() => {
  const u = user.value;
  if (!u) return false;
  if (Array.isArray(u.roles)) {
    return u.roles.some(r => r && typeof r.name === 'string' && r.name.toLowerCase() === 'user');
  }
  return false;
});

function logout() {
  localStorage.removeItem("auth_token");
  localStorage.removeItem("user");
  isAuthenticated.value = false;
  router.push("/login");
}

onMounted(() => {
  fetchUser();
});
</script>
<style scoped>
.navbar {
  height: 64px;
  background: #111827;
  color: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  gap: 20px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

.logo {
  font-size: 22px;
  font-weight: bold;
  min-width: fit-content;
}

.search-container {
  display: flex;
  gap: 8px;
  flex: 1;
  max-width: 400px;
}

.search-input {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  background: #1f2937;
  color: white;
}

.search-input::placeholder {
  color: #9ca3af;
}

.search-input:focus {
  outline: none;
  background: #374151;
}

.search-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
}

.search-btn:hover {
  background: #2563eb;
}

.links {
  display: flex;
  gap: 20px;
  align-items: center;
  min-width: fit-content;
}

.links a {
  color: #e5e7eb;
  text-decoration: none;
}

.links a:hover {
  color: white;
}

.logout {
  background: #ef4444;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}

.logout:hover {
  background: #dc2626;
}
</style>
  