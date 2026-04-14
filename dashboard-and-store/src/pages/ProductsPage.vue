<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col lg:flex-row gap-8">
        <aside class="w-full lg:w-64 flex-shrink-0">
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Categories</h2>
              <button
                v-if="selectedCategory"
                @click="setCategory(null)"
                class="text-sm text-indigo-600 hover:text-indigo-800"
              >
                Clear
              </button>
            </div>
            <ul class="space-y-2">
              <li>
                <button
                  @click="setCategory(null)"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-md transition-colors',
                    !selectedCategory
                      ? 'bg-indigo-100 text-indigo-700 font-medium'
                      : 'text-gray-600 hover:bg-gray-100',
                  ]"
                >
                  <span class="flex items-center justify-between">
                    <span>All Products</span>
                    <span class="text-sm bg-gray-200 px-2 py-0.5 rounded-full">
                      {{ pagination.total }}
                    </span>
                  </span>
                </button>
              </li>
              <li v-for="category in categories" :key="category.id">
                <button
                  @click="setCategory(category.id)"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-md transition-colors',
                    selectedCategory === category.id
                      ? 'bg-indigo-100 text-indigo-700 font-medium'
                      : 'text-gray-600 hover:bg-gray-100',
                  ]"
                >
                  <span class="flex items-center justify-between">
                    <span class="truncate">{{ category.name }}</span>
                    <span class="text-sm bg-gray-200 px-2 py-0.5 rounded-full">
                      {{ category.products_count || category.productsCount || 0 }}
                    </span>
                  </span>
                </button>
              </li>
            </ul>
          </div>
        </aside>

        <main class="flex-1">
          <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1">
                <div class="relative">
                  <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Search products..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    @keyup.enter="setSearch(searchInput)"
                  />
                  <svg
                    class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                  </svg>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Sort by:</label>
                <select
                  :value="sort"
                  @change="setSort($event.target.value)"
                  class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
                  <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
            </div>

            <div v-if="hasActiveFilters" class="mt-4 flex items-center gap-2">
              <span class="text-sm text-gray-600">Active filters:</span>
              <span
                v-if="search"
                class="inline-flex items-center px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm"
              >
                Search: {{ search }}
              </span>
              <span
                v-if="selectedCategory"
                class="inline-flex items-center px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm"
              >
                {{ categories.find((c) => c.id === selectedCategory)?.name }}
              </span>
              <button @click="clearFilters" class="text-sm text-red-600 hover:text-red-800">
                Clear all
              </button>
            </div>
          </div>

          <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
          </div>

          <div
            v-else-if="error"
            class="bg-red-50 border border-red-200 text-red-700 px-4 py-6 rounded-lg text-center"
          >
            {{ error }}
          </div>

          <div
            v-else-if="products.length === 0"
            class="bg-white rounded-lg shadow-md p-12 text-center"
          >
            <svg
              class="w-16 h-16 mx-auto text-gray-400 mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
              />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
            <p class="text-gray-500 mb-4">Try adjusting your search or filter criteria</p>
            <button @click="clearFilters" class="text-indigo-600 hover:text-indigo-800 font-medium">
              Clear all filters
            </button>
          </div>

          <div v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <ProductCard v-for="product in products" :key="product.id" :product="product" />
            </div>

            <div v-if="pagination.lastPage > 1" class="mt-8 flex justify-center">
              <nav class="flex items-center gap-2">
                <button
                  @click="setPage(pagination.currentPage - 1)"
                  :disabled="pagination.currentPage === 1"
                  class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 19l-7-7 7-7"
                    />
                  </svg>
                </button>

                <template v-for="page in visiblePages" :key="page">
                  <button
                    v-if="page !== '...'"
                    @click="setPage(page)"
                    :class="[
                      'px-4 py-2 rounded-md',
                      page === pagination.currentPage
                        ? 'bg-indigo-600 text-white'
                        : 'border border-gray-300 hover:bg-gray-50',
                    ]"
                  >
                    {{ page }}
                  </button>
                  <span v-else class="px-2 py-2 text-gray-500">...</span>
                </template>

                <button
                  @click="setPage(pagination.currentPage + 1)"
                  :disabled="pagination.currentPage === pagination.lastPage"
                  class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"
                    />
                  </svg>
                </button>
              </nav>
            </div>

            <div class="mt-4 text-center text-sm text-gray-500">
              Showing {{ (pagination.currentPage - 1) * pagination.perPage + 1 }} to
              {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of
              {{ pagination.total }} products
            </div>
          </div>
        </main>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import ProductCard from '@/components/ProductCard.vue'
import { useProductStore } from '@/stores/products'

const route = useRoute()
const productStore = useProductStore()

const {
  products,
  categories,
  loading,
  error,
  pagination,
  search,
  sort,
  selectedCategory,
  hasActiveFilters,
} = storeToRefs(productStore)

const sortOptions = productStore.sortOptions

const {
  fetchProducts,
  fetchCategories,
  setPage,
  setSort,
  setSearch,
  setCategory,
  clearFilters,
  syncFromQuery,
} = productStore

const searchInput = ref(search.value)

const visiblePages = computed(() => {
  const current = pagination.value.currentPage
  const last = pagination.value.lastPage
  const delta = 2
  const range = []
  const rangeWithDots = []

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
      range.push(i)
    }
  }

  let prev = 0
  for (const i of range) {
    if (prev && i - prev > 1) {
      rangeWithDots.push('...')
    }
    rangeWithDots.push(i)
    prev = i
  }

  return rangeWithDots
})

watch(
  () => route.query,
  () => {
    syncFromQuery()
    fetchProducts()
  },
  { immediate: false },
)

onMounted(() => {
  syncFromQuery()
  fetchCategories()
  fetchProducts()
})
</script>
