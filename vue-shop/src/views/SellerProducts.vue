<template>
  <div class="seller-products">
    <!-- Header Section -->
    <div class="header">
      <h1>My Products</h1>
      <button class="btn btn-primary" @click="toggleForm">
        {{ showForm ? 'Hide Form' : 'Add New Product' }}
      </button>
    </div>

    <!-- Product Form -->
    <product-form
      v-if="showForm"
      :product="currentProduct"
      :categories="categories"
      :loading="formLoading"
      :errors="formErrors"
      @submit="handleSubmit"
      @cancel="resetForm"
    />

    <!-- Products List -->
    <div class="card mt-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">My Products</h2>
        <loading-spinner v-if="loading" />
      </div>
      
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>Image</th>
              <th>Name</th>
              <th>Category</th>
              <th class="text-center">Price</th>
              <th class="text-center">Stock</th>
              <th class="text-center">Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading && !products.length">
              <td colspan="7" class="text-center py-4">
                <loading-spinner />
              </td>
            </tr>
            <tr v-else-if="!loading && !products.length">
              <td colspan="7" class="text-center py-4">
                <p class="text-muted mb-0">No products found. Add your first product!</p>
              </td>
            </tr>
            <product-item
              v-for="product in products"
              :key="product.id"
              :product="product"
              @edit="editProduct"
              @delete="confirmDelete"
            />
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <pagination
        v-if="pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :from="pagination.from"
        :to="pagination.to"
        :total="pagination.total"
        @page-changed="changePage"
      />
    </div>

    <!-- Delete Confirmation Modal -->
    <confirmation-modal
      v-if="showDeleteModal"
      title="Confirm Delete"
      :message="`Are you sure you want to delete '${selectedProduct?.name}'?`"
      :loading="deleting"
      @confirm="deleteProduct"
      @cancel="closeModal"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import api from '../api/api';
import ProductForm from '../components/products/ProductForm.vue';
import ProductItem from '../components/products/ProductItem.vue';
import Pagination from '../components/common/Pagination.vue';
import ConfirmationModal from '../components/common/ConfirmationModal.vue';
import LoadingSpinner from '../components/common/LoadingSpinner.vue';

export default {
  name: 'SellerProducts',
  
  components: {
    ProductForm,
    ProductItem,
    Pagination,
    ConfirmationModal,
    LoadingSpinner
  },

  setup() {
    const toast = useToast();
    
    // State
    const products = ref([]);
    const categories = ref([]);
    const loading = ref(false);
    const formLoading = ref(false);
    const deleting = ref(false);
    const showForm = ref(false);
    const showDeleteModal = ref(false);
    const selectedProduct = ref(null);
    
    // Form state
    const currentProduct = ref(createEmptyProduct());
    const formErrors = ref({});
    
    // Pagination
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: 0,
      to: 0
    });
    
    // Add debug logging
    const logApiResponse = (endpoint, response) => {
      console.log(`API Response from ${endpoint}:`, {
        status: response.status,
        data: response.data,
        headers: response.headers
      });
    };

    // Computed
    const isEditMode = computed(() => !!currentProduct.value.id);

    // Methods
    const fetchProducts = async (page = 1) => {
      try {
        loading.value = true;
        console.log('Fetching products, page:', page);
        
        // Check for token
        const token = localStorage.getItem('token');
        console.log('Auth token exists:', !!token);
        
        const res = await api.get('/seller/products', {
          params: { page },
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        
        logApiResponse('/seller/products', res);
        
        // Handle both array and paginated responses
        if (Array.isArray(res.data)) {
          products.value = res.data;
          pagination.value = {
            current_page: 1,
            last_page: 1,
            per_page: res.data.length,
            total: res.data.length,
            from: 1,
            to: res.data.length
          };
        } else {
          products.value = res.data.data || [];
          if (res.data.meta) {
            pagination.value = {
              current_page: res.data.meta.current_page || 1,
              last_page: res.data.meta.last_page || 1,
              per_page: res.data.meta.per_page || 10,
              total: res.data.meta.total || 0,
              from: res.data.meta.from || 0,
              to: res.data.meta.to || 0
            };
          }
        }
        
        console.log('Products loaded:', products.value);
      } catch (error) {
        console.error('Error fetching products:', {
          message: error.message,
          response: error.response,
          config: error.config
        });
        
        if (error.response?.status === 401) {
          toast.error('Session expired. Please login again.');
          // Redirect to login or refresh token
        } else {
          toast.error(error.response?.data?.message || 'Failed to load products. Please try again.');
        }
      } finally {
        loading.value = false;
      }
    };

    const fetchCategories = async () => {
      try {
        console.log('Fetching categories...');
        const res = await api.get('/categories', {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        
        logApiResponse('/categories', res);
        
        // Handle different response formats
        if (Array.isArray(res.data)) {
          categories.value = res.data;
        } else if (res.data && Array.isArray(res.data.categories)) {
          categories.value = res.data.categories;
        } else {
          console.warn('Unexpected categories response format:', res.data);
          categories.value = [];
        }
        
        console.log('Categories loaded:', categories.value);
      } catch (error) {
        console.error('Error fetching categories:', {
          message: error.message,
          response: error.response,
          config: error.config
        });
        toast.error(error.response?.data?.message || 'Failed to load categories. Please try again.');
      }
    };

    const handleSubmit = async (formData) => {
      formLoading.value = true;
      formErrors.value = {};

      try {
        if (isEditMode.value) {
          await api.post(`/seller/products/${currentProduct.value.id}?_method=PUT`, formData);
          toast.success('Product updated successfully!');
        } else {
          await api.post('/seller/products', formData);
          toast.success('Product added successfully!');
        }
        
        await fetchProducts(pagination.value.current_page);
        resetForm();
      } catch (error) {
        console.error('Error submitting form:', {
          message: error.message,
          response: error.response,
          config: error.config
        });
        
        if (error.response?.status === 422) {
          formErrors.value = error.response.data.errors || {};
          toast.error('Please fix the validation errors.');
        } else {
          toast.error(error.response?.data?.message || 'Failed to save product. Please try again.');
        }
      } finally {
        formLoading.value = false;
      }
    };

    const editProduct = (product) => {
      currentProduct.value = { ...product };
      showForm.value = true;
      // Scroll to form
      setTimeout(() => {
        document.querySelector('.product-form')?.scrollIntoView({ behavior: 'smooth' });
      }, 100);
    };

    const confirmDelete = (product) => {
      selectedProduct.value = product;
      showDeleteModal.value = true;
    };

    const deleteProduct = async () => {
      if (!selectedProduct.value) return;
      
      try {
        deleting.value = true;
        await api.delete(`/seller/products/${selectedProduct.value.id}`);
        toast.success('Product deleted successfully!');
        await fetchProducts(pagination.value.current_page);
      } catch (error) {
        console.error('Error deleting product:', {
          message: error.message,
          response: error.response,
          config: error.config
        });
        toast.error(error.response?.data?.message || 'Failed to delete product. Please try again.');
      } finally {
        closeModal();
        deleting.value = false;
      }
    };

    const changePage = (page) => {
      if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) {
        return;
      }
      fetchProducts(page);
    };

    const toggleForm = () => {
      showForm.value = !showForm.value;
      if (!showForm.value) {
        resetForm();
      }
    };

    const resetForm = () => {
      currentProduct.value = createEmptyProduct();
      formErrors.value = {};
      showForm.value = false;
    };

    const closeModal = () => {
      showDeleteModal.value = false;
      selectedProduct.value = null;
    };

    // Helper function
    function createEmptyProduct() {
      return {
        id: null,
        name: '',
        description: '',
        category_id: '',
        price: null,
        stock: null,
        image: null,
        is_active: true
      };
    }

    // Lifecycle hooks
    onMounted(async () => {
      console.log('SellerProducts component mounted');
      try {
        await Promise.all([
          fetchProducts(),
          fetchCategories()
        ]);
      } catch (error) {
        console.error('Error in component mount:', error);
      }
    });

    return {
      // State
      products,
      categories,
      loading,
      formLoading,
      deleting,
      showForm,
      showDeleteModal,
      selectedProduct,
      currentProduct,
      formErrors,
      pagination,
      
      // Computed
      isEditMode,
      
      // Methods
      toggleForm,
      handleSubmit,
      editProduct,
      confirmDelete,
      deleteProduct,
      changePage,
      resetForm,
      closeModal
    };
  }
};
</script>

<style scoped>
.seller-products {
  padding: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header h1 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 600;
  color: #2d3748;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .header .btn {
    width: 100%;
    margin-top: 1rem;
  }
}
</style>