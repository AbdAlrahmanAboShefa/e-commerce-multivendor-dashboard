<template>
  <div class="cart-page">
    <h1>Your Cart</h1>

    <div v-if="items.length === 0" class="empty">
      🛒 Your cart is empty
    </div>

    <div v-else class="cart-layout">
      <!-- ITEMS -->
      <div class="cart-items">
        <div class="cart-item" v-for="item in items" :key="item.id">
          <img :src="item.product.image || placeholder" />

          <div class="info">
            <h3>{{ item.product.name || 'Untitled product' }}</h3>
            <p>${{ item.product.priceFormatted }}</p>

            <div class="qty">
              <button @click="decrease(item)">−</button>
              <input
                type="number"
                min="1"
                v-model.number="item.quantity"
                @change="updateQuantity(item)"
                @wheel.prevent
              />
              <button @click="increase(item)">+</button>
            </div>
          </div>

          <div class="remove">
            <button @click="remove(item)">✕</button>
          </div>
        </div>
      </div>

      <!-- SUMMARY -->
      <div class="summary">
        <h2>Summary</h2>
        <div class="row">
          <span>Total</span>
          <strong>${{ total }}</strong>
        </div>
        <button class="checkout" @click="checkout">Checkout</button>
      </div>
    </div>
  </div>
</template>
<script setup>
  import { ref, computed, onMounted } from "vue";
  import { useRouter } from "vue-router";
  import api from "../api/api";
  
  const router = useRouter();
  // helper to try multiple endpoint variants (e.g. /cart and /carts)
  async function tryRequest(method, urls, payload) {
    let lastErr;
    for (const url of urls) {
      try {
        if (method === "get") return await api.get(url);
        if (method === "post") return await api.post(url, payload);
        if (method === "patch") return await api.patch(url, payload);
        if (method === "delete") return await api.delete(url);
      } catch (e) {
        lastErr = e;
        // try next
      }
    }
    throw lastErr;
  }
  
  const items = ref([]);
  const lastBody = ref(null);
  const placeholder =
    "https://dummyimage.com/100x100/eee/aaa";
  const apiTotal = ref(null);
  
  async function loadCart() {
    let res;
    try {
      res = await tryRequest("get", [ "/carts"]);
    } catch (e) {
      console.error("Cart fetch failed:", e);
      lastBody.value = { error: String(e) };
      items.value = [];
      return;
    }
    const body = res.data ?? {};
    lastBody.value = body;

    // support API returning an array like: [ { cart... items: [...] }, { total_price: 8.4 } ]
    let rawItems = null;
    apiTotal.value = null;
    if (Array.isArray(body) && body.length > 0) {
      if (body[0].items) {
        rawItems = body[0].items;
        apiTotal.value = body[1]?.total_price ?? body[0]?.total_price ?? null;
      } else {
        rawItems = body;
      }
    } else {
      rawItems = body.items ?? body.data?.items ?? body.data ?? body;
      apiTotal.value = body.total_price ?? null;
    }

    items.value = (Array.isArray(rawItems) ? rawItems : [])
      .map((it) => {
        const prodRaw = it.product ?? it.product_data ?? it.product ?? (it.product_id ? {
          id: it.product_id,
          name: it.name || it.title || "",
          price: it.price || 0,
          image: it.image || null
        } : it);

        // normalize name
        const name = prodRaw?.name || prodRaw?.title || prodRaw?.product_name || prodRaw?.label || "";

        // determine numeric price (try several possible keys and formats)
        function parsePrice(p) {
          if (p == null) return 0;
          if (typeof p === "number") return p;
          // price in cents
          if (/cents|cent/.test(String(p))) {
            const n = parseFloat(String(p).replace(/[^0-9.-]/g, ""));
            return isNaN(n) ? 0 : n / 100;
          }
          // price like 1999 (cents) or 19.99
          const cleaned = String(p).replace(/[^0-9.-]/g, "");
          const n = parseFloat(cleaned);
          if (isNaN(n)) return 0;
          // if looks like cents (>=100 && no decimal in original), treat as cents
          if (!String(p).includes('.') && n >= 100) return n / 100;
          return n;
        }

        const priceNumber = parsePrice(prodRaw?.price ?? prodRaw?.unit_price ?? prodRaw?.price_cents ?? prodRaw?.amount);
        const priceFormatted = priceNumber.toFixed(2);

        const product = {
          ...prodRaw,
          name,
          priceNumber,
          priceFormatted,
          // keep backwards-compatible `price` but not used for math
          price: prodRaw?.price
        };

        return {
          id: it.id ?? (product && product.id) ?? null,
          quantity: it.quantity ?? it.qty ?? 1,
          product
        };
      });
  }
  
  onMounted(loadCart);
  
  const total = computed(() => {
    if (apiTotal.value != null) return Number(apiTotal.value).toFixed(2);
    const sum = items.value.reduce((s, i) => {
      const qty = Number(i.quantity) || 0;
      const price = Number(i.product?.priceNumber ?? 0) || 0;
      return s + qty * price;
    }, 0);
    return sum.toFixed(2);
  });
  
  async function increase(item) {
    try {
      await tryRequest("post", [
        `/cart/items`,
        `/carts/items`
      ], {
        product_id: item.product.id,
        quantity: 1
      });
      window.dispatchEvent(new Event("cart-updated"));
      await loadCart();
    } catch (e) {
      console.error(e);
    }
  }

  // set explicit quantity from number input
  async function updateQuantity(item) {
    const qty = Number(item.quantity) || 1;
    if (qty < 1) {
      item.quantity = 1;
      return;
    }
    try {
      await tryRequest("patch", [
        `/cart/items/${item.id}`,
        `/carts/items/${item.id}`
      ], { quantity: qty });
      window.dispatchEvent(new Event("cart-updated"));
      await loadCart();
    } catch (e) {
      console.error(e);
    }
  }
  
  async function decrease(item) {
    if (item.quantity === 1) return;
    try {
      await tryRequest("patch", [
        `/cart/items/${item.id}`,
        `/carts/items/${item.id}`
      ], {
        quantity: item.quantity - 1
      });
      window.dispatchEvent(new Event("cart-updated"));
      await loadCart();
    } catch (e) {
      console.error(e);
    }
  }
  
  async function remove(item) {
    try {
      await tryRequest("delete", [
        `/cart/items/${item.id}`,
        `/carts/items/${item.id}`
      ]);
      window.dispatchEvent(new Event("cart-updated"));
      await loadCart();
    } catch (e) {
      console.error(e);
    }
  }

  async function checkout() {
    if (items.value.length === 0) {
      alert("Your cart is empty");
      return;
    }

    try {
      const orderData = {
        total_price: parseFloat(total.value),
        items: items.value.map(item => ({
          product_id: item.product.id,
          quantity: item.quantity,
          price: item.product.priceNumber
        }))
      };

      const response = await api.post("/orders", orderData);
      alert("Order created successfully!");
      
      // Clear cart and redirect to orders
      items.value = [];
      window.dispatchEvent(new Event("cart-updated"));
      
      // Redirect to orders page
      router.push("/orders");
    } catch (error) {
      console.error("Checkout failed:", error);
      alert("Failed to create order: " + (error.response?.data?.message || error.message));
    }
  }
  </script>
  <style scoped>
    .cart-page {
      max-width: 1200px;
      margin: auto;
    }
    
    h1 {
      margin-bottom: 20px;
    }
    
    .cart-layout {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 24px;
    }
    
    .cart-item {
      display: flex;
      gap: 16px;
      background: white;
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 12px;
    }
    
    .cart-item img {
      width: 100px;
      height: 100px;
      object-fit: cover;
    }
    
    .info {
      flex: 1;
    }
    
    .qty {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 10px;
    }
    
    .qty button {
      width: 32px;
      height: 32px;
      padding: 0;
    }
    
    .remove button {
      background: transparent;
      color: #dc2626;
      font-size: 20px;
    }
    
    .summary {
      background: white;
      padding: 20px;
      border-radius: 10px;
      height: fit-content;
    }
    
    .summary .row {
      display: flex;
      justify-content: space-between;
      margin: 16px 0;
    }
    
    .checkout {
      width: 100%;
      padding: 14px;
      background: #16a34a;
    }
    
    .empty {
      background: white;
      padding: 40px;
      text-align: center;
      border-radius: 10px;
    }
    </style>
    