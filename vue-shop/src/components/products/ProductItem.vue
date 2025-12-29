<template>
  <tr>
    <td>
      <div class="product-image">
        <img 
          v-if="product.image_url" 
          :src="product.image_url" 
          :alt="product.name"
          @error="handleImageError"
        >
        <div v-else class="no-image">
          <i class="fas fa-image"></i>
        </div>
      </div>
    </td>
    <td>
      <div class="product-name">{{ product.name }}</div>
      <div class="product-description text-muted">
        {{ truncate(product.description, 50) }}
      </div>
    </td>
    <td>{{ product.category?.name || 'N/A' }}</td>
    <td class="text-center">${{ formatPrice(product.price) }}</td>
    <td class="text-center">
      <span :class="getStockBadgeClass(product.stock)">
        {{ product.stock }} in stock
      </span>
    </td>
    <td class="text-center">
      <span :class="getStatusBadgeClass(product.is_active)">
        {{ product.is_active ? 'Active' : 'Inactive' }}
      </span>
    </td>
    <td class="text-end">
      <button 
        class="btn btn-sm btn-outline-primary me-1" 
        @click="$emit('edit', product)"
        title="Edit"
      >
        <i class="fas fa-edit"></i>
      </button>
      <button 
        class="btn btn-sm btn-outline-danger" 
        @click="$emit('delete', product)"
        title="Delete"
      >
        <i class="fas fa-trash"></i>
      </button>
    </td>
  </tr>
</template>

<script>
export default {
  name: 'ProductItem',
  props: {
    product: {
      type: Object,
      required: true
    }
  },
  methods: {
    formatPrice(price) {
      return Number(price).toFixed(2);
    },
    truncate(text, length) {
      if (!text) return '';
      if (text.length <= length) return text;
      return text.substring(0, length) + '...';
    },
    getStockBadgeClass(stock) {
      return {
        'badge': true,
        'bg-success': stock > 5,
        'bg-warning': stock > 0 && stock <= 5,
        'bg-danger': stock === 0
      };
    },
    getStatusBadgeClass(isActive) {
      return {
        'badge': true,
        'bg-success': isActive,
        'bg-secondary': !isActive
      };
    },
    handleImageError(event) {
      const img = event.target;
      img.style.display = 'none';
      const noImage = img.nextElementSibling;
      if (noImage) {
        noImage.style.display = 'flex';
      }
    }
  }
}
</script>

<style scoped>
.product-image {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  border-radius: 0.25rem;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  background-color: #f8f9fa;
}

.product-name {
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.product-description {
  font-size: 0.875rem;
  line-height: 1.3;
}

.badge {
  display: inline-block;
  padding: 0.35em 0.65em;
  font-size: 0.75em;
  font-weight: 500;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.25rem;
}

.bg-success {
  background-color: #198754 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: #000 !important;
}

.bg-danger {
  background-color: #dc3545 !important;
}

.bg-secondary {
  background-color: #6c757d !important;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  line-height: 1.5;
  border-radius: 0.2rem;
}

.btn-outline-primary {
  color: #0d6efd;
  border-color: #0d6efd;
}

.btn-outline-primary:hover {
  color: #fff;
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}

.btn-outline-danger:hover {
  color: #fff;
  background-color: #dc3545;
  border-color: #dc3545;
}

.text-muted {
  color: #6c757d !important;
}

.text-end {
  text-align: right !important;
}

.text-center {
  text-align: center !important;
}

.me-1 {
  margin-right: 0.25rem !important;
}
</style>