<template>
  <div class="products-page">
    <h1>Products</h1>

    <div class="grid">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../api/api";
import ProductCard from "../components/ProductCard.vue";

const products = ref([]);

async function loadProducts(searchQuery = "") {
  try {
    let res;
    if (searchQuery.trim() !== "") {
      res = await api.get(`/products?name=${encodeURIComponent(searchQuery)}`);
    } else {
      res = await api.get("/products");
    }
    products.value = res.data.data ?? res.data;
  } catch (error) {
    console.error("Failed to load products:", error);
    products.value = [];
  }
}

function handleSearch(event) {
  const searchQuery = event.detail;
  loadProducts(searchQuery);
}

onMounted(() => {
  loadProducts();
  window.addEventListener("search-products", handleSearch);
});
</script>

<style scoped>
.products-page {
  padding: 24px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

@media (max-width: 1024px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
