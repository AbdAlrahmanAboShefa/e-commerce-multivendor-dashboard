<template>
  <div class="card">
    <img v-if="imageUrl" :src="imageUrl" :alt="product.name" class="product-img" />
    <h3>{{ product.name }}</h3>
    <p>{{ product.price }} €</p>

    <button :disabled="loading" @click="addToCart" class="add-btn">
      {{ loading ? "Adding..." : "Add to Cart" }}
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import api from "../api/api";

const props = defineProps({
  product: Object
});

const loading = ref(false);

// Base storage URL used when backend returns only a filename/path
const baseStorage = "http://127.0.0.1:8000/storage/products/";

const imageUrl = computed(() => {
  const img = props.product.image_url || props.product.image || props.product.photo || props.product.thumbnail;
  if (!img) return null;
  if (typeof img !== "string") return null;

  const trimmed = img.trim();
  if (trimmed.startsWith("http")) return trimmed;

  // Normalize paths: remove leading slashes and possible "storage/" or "products/" prefixes
  const cleaned = trimmed.replace(/^\/+/, "").replace(/^(storage\/)?(products\/)?/, "");
  return baseStorage + cleaned;
});

async function addToCart() {
  loading.value = true;

  try {
    await api.post("/cart/items", {
      product_id: props.product.id,
      quantity: 1
    });

    // notify parent / navbar
    window.dispatchEvent(new Event("cart-updated"));

    alert("Added to cart ✅");
  } catch (e) {
    alert("Please login first");
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.card {
  border: 1px solid #eee;
  padding: 16px;
  border-radius: 10px;
}

.product-img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 8px;
}

.add-btn {
  width: 100%;
  margin-top: 10px;
}
</style>
  