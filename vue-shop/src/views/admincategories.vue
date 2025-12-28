<template>
  <div class="admin-categories">
    <h2>Categories</h2>
    
    <!-- Add New Category -->
    <div class="add-category">
      <input
        v-model="newCategory"
        @keyup.enter="addCategory"
        placeholder="Type category name and press Enter"
        class="category-input"
        :disabled="isAdding"
      />
      <button 
        @click="addCategory" 
        :disabled="!newCategory.trim() || isAdding"
        class="add-button"
      >
        {{ isAdding ? 'Adding...' : 'Add' }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">
      <p>Loading categories...</p>
    </div>

    <!-- Error Message -->
    <div v-else-if="error" class="error-message">
      {{ error }}
      <button @click="fetchCategories" class="retry-button">Retry</button>
    </div>

    <!-- Categories List -->
    <div v-else>
      <ul v-if="categories.length > 0" class="categories-list">
        <li v-for="category in categories" :key="category.id" class="category-item">
          <span class="category-name">{{ category.name }}</span>
          <span class="category-date" v-if="category.created_at">
            Added on: {{ formatDate(category.created_at) }}
          </span>
          <button 
            @click="deleteCategory(category.id)" 
            class="delete-button"
            :disabled="isDeleting === category.id"
            title="Delete category"
          >
            {{ isDeleting === category.id ? 'Deleting...' : '×' }}
          </button>
        </li>
      </ul>
      <p v-else class="no-categories">No categories found. Add one above!</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// API Configuration
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
  }
});

// State
const categories = ref([]);
const newCategory = ref('');
const loading = ref(true);
const error = ref(null);
const isAdding = ref(false);
const isDeleting = ref(null);

// Format date for display
const formatDate = (dateString) => {
  if (!dateString) return 'Unknown date';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Fetch all categories
const fetchCategories = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await api.get('/admin/categories');
    categories.value = response.data.categories || [];
    
  } catch (err) {
    console.error('Fetch error:', err);
    error.value = err.response?.data?.message || 'Failed to load categories. Please try again.';
  } finally {
    loading.value = false;
  }
};

// Add new category
const addCategory = async () => {
  const categoryName = newCategory.value.trim();
  if (!categoryName || isAdding.value) return;
  
  try {
    isAdding.value = true;
    const response = await api.post('/admin/categories', {
      name: categoryName
    });
    
    categories.value = response.data.categories || [];
    newCategory.value = '';
    showNotification('Category added successfully!');
    
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'Failed to add category';
    console.error('Add error:', errorMsg);
    showNotification(errorMsg, 'error');
  } finally {
    isAdding.value = false;
  }
};

// Delete category
const deleteCategory = async (id) => {
  if (!confirm('Are you sure you want to delete this category?')) return;
  
  try {
    isDeleting.value = id;
    const response = await api.delete(`/admin/categories/${id}`);
    categories.value = response.data.categories || [];
    showNotification('Category deleted successfully!');
  } catch (err) {
    console.error('Delete error:', err);
    showNotification('Failed to delete category', 'error');
  } finally {
    isDeleting.value = null;
  }
};

// Show notification
const showNotification = (message, type = 'success') => {
  // You can replace this with your preferred notification system
  alert(`${type.toUpperCase()}: ${message}`);
};

// Lifecycle hook
onMounted(() => {
  const token = localStorage.getItem('auth_token');
  if (!token) {
    error.value = 'Please log in to manage categories.';
    loading.value = false;
    return;
  }
  fetchCategories();
});
</script>

<style scoped>
.admin-categories {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
}

h2 {
  color: #2c3e50;
  margin-bottom: 24px;
  text-align: center;
  font-size: 28px;
  font-weight: 600;
}

.add-category {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.category-input {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.2s;
}

.category-input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.add-button {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 0 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  white-space: nowrap;
}

.add-button:hover:not(:disabled) {
  background-color: #43a047;
  transform: translateY(-1px);
}

.add-button:active:not(:disabled) {
  transform: translateY(0);
}

.add-button:disabled {
  background-color: #a5d6a7;
  cursor: not-allowed;
  opacity: 0.8;
}

.loading, .error-message {
  text-align: center;
  padding: 24px;
  background-color: #f8f9fa;
  border-radius: 8px;
  margin: 20px 0;
  border: 1px solid #e2e8f0;
}

.error-message {
  background-color: #ffebee;
  color: #c62828;
  border-color: #ffcdd2;
}

.retry-button {
  margin-top: 12px;
  padding: 8px 20px;
  background-color: #f44336;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.retry-button:hover {
  background-color: #e53935;
}

.categories-list {
  list-style: none;
  padding: 0;
  margin: 0;
  max-width: 600px;
  margin: 0 auto;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background-color: white;
  border: 1px solid #e2e8f0;
  margin-bottom: 12px;
  border-radius: 8px;
  transition: all 0.2s;
  position: relative;
}

.category-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border-color: #cbd5e0;
}

.category-name {
  font-weight: 500;
  color: #2d3748;
  font-size: 16px;
}

.category-date {
  font-size: 13px;
  color: #718096;
  margin-left: 12px;
}

.delete-button {
  background: none;
  border: none;
  color: #e53e3e;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
  margin-left: 12px;
  flex-shrink: 0;
}

.delete-button:hover:not(:disabled) {
  background-color: #fff5f5;
  color: #c53030;
}

.delete-button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.no-categories {
  text-align: center;
  color: #718096;
  font-style: italic;
  margin-top: 40px;
  padding: 24px;
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 1px dashed #e2e8f0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .admin-categories {
    padding: 16px;
  }
  
  .add-category {
    flex-direction: column;
  }
  
  .add-button {
    width: 100%;
    padding: 12px;
  }
  
  .category-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .category-actions {
    margin-top: 8px;
    align-self: flex-end;
  }
}
</style>