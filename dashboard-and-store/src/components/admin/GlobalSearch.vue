<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const isOpen = ref(false)
const selectRef = ref(null)
const searchQuery = ref('')
const results = ref([])
const loading = ref(false)

watch(searchQuery, async (query) => {
  if (query.length >= 2) {
    loading.value = true
    isOpen.value = true
    try {
      const [productsRes, ordersRes, customersRes] = await Promise.allSettled([
        api.get('/products', { params: { search: query, per_page: 4 } }),
        api.get('/orders', { params: { search: query, per_page: 4 } }),
        api.get('/customers', { params: { search: query, per_page: 4 } }),
      ])

      results.value = []
      
      if (productsRes.status === 'fulfilled') {
        const products = productsRes.value.data.data || productsRes.value.data || []
        if (products.length) {
          results.value.push({ type: 'Product', items: products })
        }
      }
      
      if (ordersRes.status === 'fulfilled') {
        const orders = ordersRes.value.data.data || ordersRes.value.data || []
        if (orders.length) {
          results.value.push({ type: 'Order', items: orders })
        }
      }
      
      if (customersRes.status === 'fulfilled') {
        const customers = customersRes.value.data.data || customersRes.value.data || []
        if (customers.length) {
          results.value.push({ type: 'Customer', items: customers })
        }
      }
    } catch (err) {
      results.value = []
    } finally {
      loading.value = false
    }
  } else {
    results.value = []
    isOpen.value = false
  }
})

const goToResult = (item, type) => {
  const routes = {
    Product: `/dashboard/products/edit/${item.id}`,
    Order: `/dashboard/orders/${item.id}`,
    Customer: `/dashboard/customers/${item.id}`,
  }
  router.push(routes[type] || '/dashboard')
  isOpen.value = false
  searchQuery.value = ''
}

const handleClickOutside = (e) => {
  if (selectRef.value && !selectRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

const visibleSearchContent = () => {
  if (searchQuery.value.length >= 2) {
    isOpen.value = true
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div class="w-100 hidden text-gray-500 lg:flex lg:items-center lg:justify-center lg:relative" ref="selectRef">
    <div class="relative w-full max-w-md">
      <input 
        v-model="searchQuery"
        type="search" 
        class="w-full px-4 py-2 pl-10 bg-white border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-theme-500 focus:border-transparent" 
        placeholder="Search keyword" 
        @focus="visibleSearchContent"
      >
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>

    <Transition name="search-content">
      <div v-if="isOpen" class="absolute top-full left-0 right-0 min-h-50 max-h-100 bg-white border border-gray-300 z-50 overflow-y-auto p-5 rounded">
        <div v-if="loading" class="flex justify-center py-4">
          <svg class="animate-spin h-6 w-6 text-theme-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm8 8a8 8 0 01-8-8V0C5.373 8 8 5.373 8 12h4zm8-8a8 8 0 018 8V0c-4.627 0-8 5.373-8 12h4z"></path>
          </svg>
        </div>
        
        <ul v-else class="space-y-4">
          <li v-for="group in results" :key="group.type">
            <div class="text-gray-800 text-sm border-b border-gray-200 pb-1 mb-1 font-semibold">{{ group.type }}</div>
            <ul class="pl-3 text-sm">
              <li v-for="item in group.items" :key="item.id">
                <button 
                  @click="goToResult(item, group.type)"
                  class="text-blue-600 hover:text-blue-800 hover:underline"
                >
                  {{ item.name || item.title || item.code || item.email || '#' + item.id }}
                </button>
              </li>
            </ul>
          </li>
          
          <li v-if="results.length === 0 && searchQuery.length >= 2">
            <div class="text-gray-500 text-sm">No results found</div>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.search-content-enter-active,
.search-content-leave-active {
  transition: all 0.3s ease;
}

.search-content-enter-from,
.search-content-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>