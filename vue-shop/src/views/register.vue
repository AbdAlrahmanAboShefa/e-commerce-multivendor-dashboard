<template>
  <div class="auth-page">
    <div class="auth-card">
      <h2>Create Account</h2>
      <p class="subtitle">Join our shop today 🛒</p>

      <form @submit.prevent="register">
        <input v-model="form.name" placeholder="Full Name" required />
        <input v-model="form.email" type="email" placeholder="Email" required />
        <input v-model="form.password" type="password" placeholder="Password" required />
        <input
          v-model="form.password_confirmation"
          type="password"
          placeholder="Confirm Password"
          required
        />

        <button :disabled="loading">
          {{ loading ? "Creating..." : "Register" }}
        </button>

        <p class="switch">
          Already have an account?
          <router-link to="/login">Login</router-link>
        </p>

        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "../api/api";

const router = useRouter();
const loading = ref(false);
const error = ref(null);

const form = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: ""
});

async function register() {
  loading.value = true;
  error.value = null;

  try {
    const res = await api.post("/register", form);

    localStorage.setItem("auth_token", res.data.token);
    localStorage.setItem("user", JSON.stringify(res.data.user));

    router.push("/");
  } catch (e) {
    error.value = "Registration failed";
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
/* نفس ستايل Login */
.auth-page {
  height: 100vh;
  width: 100%;
  background: linear-gradient(135deg, #eef2f9, #1e40af);
  display: flex;
  justify-content: center;
  align-items: center;
}


.auth-card {
  background: #fff;
  width: 380px;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0,0,0,.2);
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
