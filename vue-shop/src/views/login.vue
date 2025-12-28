<template>
  <div class="auth-page">
    <div class="auth-card">
      <h2>Login</h2>
      <p class="subtitle">Welcome back 👋</p>

      <form @submit.prevent="login">
        <input v-model="email" type="email" placeholder="Email" required />
        <input v-model="password" type="password" placeholder="Password" required />

        <button :disabled="loading">
          {{ loading ? "Logging in..." : "Login" }}
        </button>

        <p class="switch">
          Don't have an account?
          <router-link to="/register">Register</router-link>
        </p>

        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "../api/api";

const router = useRouter();

const email = ref("");
const password = ref("");
const loading = ref(false);
const error = ref(null);

async function login() {
  loading.value = true;
  error.value = null;

  try {
    const res = await api.post("/login", {
      email: email.value,
      password: password.value
    });

    localStorage.setItem("auth_token", res.data.token);
    localStorage.setItem("user", JSON.stringify(res.data.user));

    router.push("/");
  } catch (e) {
    error.value = "Invalid email or password";
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f5f6, #1e40af);
  display: flex;
  justify-content: center;
  align-items: center;
}

.auth-card {
  background: #fff;
  width: 500px;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0,0,0,.2);
}

h2 {
  text-align: center;
  margin-bottom: 5px;
}

.subtitle {
  text-align: center;
  color: #6b7280;
  margin-bottom: 20px;
}

input {
  width: 100%;
  padding: 12px;
  margin-bottom: 12px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
}

button {
  width: 100%;
  padding: 12px;
  background: #2563eb;
  color: white;
  border-radius: 8px;
  font-weight: bold;
}

.switch {
  text-align: center;
  margin-top: 12px;
}

.switch a {
  color: #2563eb;
}

.error {
  margin-top: 10px;
  color: #dc2626;
  text-align: center;
}
</style>
