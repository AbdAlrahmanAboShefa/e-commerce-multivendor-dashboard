<template>
  <div class="app-container">
    <Navbar />
    <Sidebar v-if="!isAuthPage" />
    <main class="main-content" :class="{ 'with-sidebar': !isAuthPage, 'auth-no-padding': isAuthPage }">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import Navbar from "./components/Navbar.vue";
import Sidebar from "./components/sidebar.vue";

const route = useRoute();

const isAuthPage = computed(() =>
  route.path === "/login" || route.path === "/register"
);
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  background: #f9fafb;
}

.app-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  padding: 20px;
  margin-top: 64px;
}

.main-content.with-sidebar {
  margin-left: 250px;
}

.main-content.auth-no-padding {
  padding: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .main-content.with-sidebar {
    margin-left: 0;
    margin-top: 64px;
    padding: 16px;
  }
}
</style>
