<template>
  <div class="pagination-container">
    <div class="pagination-info text-muted">
      Showing {{ from || 0 }} to {{ to || 0 }} of {{ total }} entries
    </div>
    <nav>
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="changePage(1)" :disabled="currentPage === 1">
            «
          </button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button 
            class="page-link" 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage === 1"
          >
            ‹
          </button>
        </li>
        
        <li 
          v-for="page in visiblePages" 
          :key="page" 
          class="page-item" 
          :class="{ active: page === currentPage }"
        >
          <button class="page-link" @click="changePage(page)">
            {{ page }}
          </button>
        </li>
        
        <li class="page-item" :class="{ disabled: currentPage === lastPage }">
          <button 
            class="page-link" 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage === lastPage"
          >
            ›
          </button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === lastPage }">
          <button 
            class="page-link" 
            @click="changePage(lastPage)" 
            :disabled="currentPage === lastPage"
          >
            »
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'Pagination',
  props: {
    currentPage: {
      type: Number,
      required: true
    },
    lastPage: {
      type: Number,
      required: true
    },
    from: {
      type: Number,
      default: 0
    },
    to: {
      type: Number,
      default: 0
    },
    total: {
      type: Number,
      required: true
    }
  },
  emits: ['page-changed'],
  setup(props, { emit }) {
    const visiblePages = computed(() => {
      const range = [];
      const maxVisible = 5;
      let start = Math.max(1, props.currentPage - Math.floor(maxVisible / 2));
      const end = Math.min(start + maxVisible - 1, props.lastPage);
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1);
      }
      
      for (let i = start; i <= end; i++) {
        range.push(i);
      }
      
      return range;
    });

    const changePage = (page) => {
      if (page >= 1 && page <= props.lastPage && page !== props.currentPage) {
        emit('page-changed', page);
      }
    };

    return {
      visiblePages,
      changePage
    };
  }
}
</script>

<style scoped>
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination {
  display: flex;
  padding-left: 0;
  list-style: none;
  margin: 0;
  gap: 0.25rem;
}

.page-item {
  margin: 0;
}

.page-link {
  position: relative;
  display: block;
  padding: 0.5rem 0.75rem;
  border: 1px solid #dee2e6;
  background-color: #fff;
  color: #0d6efd;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 0.25rem;
  min-width: 2.5rem;
  text-align: center;
}

.page-link:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #dee2e6;
  color: #0a58ca;
}

.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.page-item.disabled .page-link {
  color: #6c757d;
  pointer-events: none;
  background-color: #fff;
  border-color: #dee2e6;
}

@media (max-width: 576px) {
  .pagination-container {
    flex-direction: column;
    align-items: stretch;
  }
  
  .pagination {
    justify-content: center;
    flex-wrap: wrap;
  }
}
</style>