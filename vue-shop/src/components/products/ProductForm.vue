<template>
  <div class="card product-form mb-4">
    <div class="card-header">
      <h2 class="h5 mb-0">{{ isEditMode ? 'Edit Product' : 'Add New Product' }}</h2>
    </div>
    <div class="card-body">
      <form @submit.prevent="$emit('submit', formData)">
        <div class="row">
          <div class="col-md-8">
            <div class="mb-3">
              <label for="productName" class="form-label">
                Product Name <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control"
                id="productName"
                v-model="formData.name"
                :class="{ 'is-invalid': errors.name }"
                required
              >
              <div v-if="errors.name" class="invalid-feedback">
                {{ errors.name[0] }}
              </div>
            </div>

            <div class="mb-3">
              <label for="productDescription" class="form-label">
                Description
              </label>
              <textarea
                class="form-control"
                id="productDescription"
                rows="3"
                v-model="formData.description"
                :class="{ 'is-invalid': errors.description }"
              ></textarea>
              <div v-if="errors.description" class="invalid-feedback">
                {{ errors.description[0] }}
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="mb-3">
              <label for="productCategory" class="form-label">
                Category <span class="text-danger">*</span>
              </label>
              <select
                class="form-select"
                id="productCategory"
                v-model="formData.category_id"
                :class="{ 'is-invalid': errors.category_id }"
                required
              >
                <option value="" disabled>Select a category</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
              <div v-if="errors.category_id" class="invalid-feedback">
                {{ errors.category_id[0] }}
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="productPrice" class="form-label">
                    Price ($) <span class="text-danger">*</span>
                  </label>
                  <input
                    type="number"
                    class="form-control"
                    id="productPrice"
                    v-model.number="formData.price"
                    min="0"
                    step="0.01"
                    :class="{ 'is-invalid': errors.price }"
                    required
                  >
                  <div v-if="errors.price" class="invalid-feedback">
                    {{ errors.price[0] }}
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="productStock" class="form-label">
                    Stock <span class="text-danger">*</span>
                  </label>
                  <input
                    type="number"
                    class="form-control"
                    id="productStock"
                    v-model.number="formData.stock"
                    min="0"
                    :class="{ 'is-invalid': errors.stock }"
                    required
                  >
                  <div v-if="errors.stock" class="invalid-feedback">
                    {{ errors.stock[0] }}
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="productImage" class="form-label">
                Product Image
              </label>
              <input
                type="file"
                class="form-control"
                id="productImage"
                accept="image/*"
                @change="handleImageUpload"
                :class="{ 'is-invalid': errors.image }"
              >
              <div v-if="errors.image" class="invalid-feedback">
                {{ errors.image[0] }}
              </div>
              <div class="form-text">
                Recommended size: 800x800px. Max file size: 2MB.
              </div>
            </div>

            <div v-if="imagePreview" class="mb-3">
              <div class="image-preview-container">
                <img :src="imagePreview" alt="Preview" class="img-thumbnail">
              </div>
            </div>

            <div class="form-check form-switch mb-4">
              <input
                class="form-check-input"
                type="checkbox"
                id="productStatus"
                v-model="formData.is_active"
              >
              <label class="form-check-label" for="productStatus">
                {{ formData.is_active ? 'Active' : 'Inactive' }}
              </label>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button
            type="button"
            class="btn btn-secondary"
            @click="$emit('cancel')"
            :disabled="loading"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
            {{ isEditMode ? 'Update Product' : 'Add Product' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue';

export default {
  name: 'ProductForm',
  props: {
    product: {
      type: Object,
      default: () => ({})
    },
    categories: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['submit', 'cancel'],
  setup(props) {
    const formData = ref({
      name: '',
      description: '',
      category_id: '',
      price: '',
      stock: '',
      is_active: true,
      image: null
    });

    const imagePreview = ref(null);

    const isEditMode = ref(false);

    // Watch for changes in the product prop
    watch(() => props.product, (newProduct) => {
      if (newProduct && Object.keys(newProduct).length > 0) {
        isEditMode.value = true;
        formData.value = {
          ...newProduct,
          // Ensure we have all required fields with defaults
          name: newProduct.name || '',
          description: newProduct.description || '',
          category_id: newProduct.category_id || '',
          price: newProduct.price || '',
          stock: newProduct.stock || '',
          is_active: Boolean(newProduct.is_active),
          image: null
        };
        
        if (newProduct.image_url) {
          imagePreview.value = newProduct.image_url;
        }
      } else {
        isEditMode.value = false;
        formData.value = {
          name: '',
          description: '',
          category_id: '',
          price: '',
          stock: '',
          is_active: true,
          image: null
        };
        imagePreview.value = null;
      }
    }, { immediate: true });

    const handleImageUpload = (event) => {
      const file = event.target.files[0];
      if (!file) return;

      // Validate file type
      if (!file.type.match('image.*')) {
        alert('Please select a valid image file (JPEG, PNG, etc.)');
        return;
      }

      // Validate file size (2MB max)
      if (file.size > 2 * 1024 * 1024) {
        alert('Image size should be less than 2MB');
        return;
      }

      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreview.value = e.target.result;
      };
      reader.readAsDataURL(file);

      // Update form data
      formData.value.image = file;
    };

    return {
      formData,
      imagePreview,
      isEditMode,
      handleImageUpload
    };
  }
};
</script>

<style scoped>
.product-form {
  border: 1px solid rgba(0, 0, 0, 0.125);
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
  padding: 1rem 1.25rem;
}

.card-body {
  padding: 1.5rem;
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: inline-block;
}

.form-control,
.form-select {
  padding: 0.5rem 0.75rem;
  font-size: 0.9375rem;
  line-height: 1.5;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus,
.form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #dc3545;
}

.is-invalid {
  border-color: #dc3545 !important;
  padding-right: calc(1.5em + 0.75rem);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(0.375em + 0.1875rem) center;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-text {
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #6c757d;
}

.image-preview-container {
  max-width: 200px;
  margin-top: 0.5rem;
}

.img-thumbnail {
  max-width: 100%;
  height: auto;
  border: 1px solid #dee2e6;
  border-radius: 0.25rem;
  padding: 0.25rem;
  background-color: #fff;
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn {
  padding: 0.5rem 1rem;
  font-size: 0.9375rem;
  line-height: 1.5;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.15s ease-in-out;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background-color: #5c636a;
  border-color: #565e64;
}

.btn:disabled {
  opacity: 0.65;
}

.spinner-border {
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}

.me-1 {
  margin-right: 0.25rem !important;
}

.gap-2 {
  gap: 0.5rem !important;
}
</style>