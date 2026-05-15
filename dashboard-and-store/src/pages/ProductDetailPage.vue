<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useProductStore } from '@/stores/products'
import DefaultLayout from '@/layouts/DefaultLayout.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const productStore = useProductStore()

const product = ref(null)
const loading = ref(true)
const error = ref(null)

const formatPrice = (price) => parseFloat(price).toFixed(2)

const addToCart = () => {
  if (!authStore.isAuthenticated) {
    router.push(`/customer/login?redirect=/products/${route.params.id}`)
    return
  }
  console.log('Add to cart:', product.value?.id)
}

onMounted(async () => {
  const result = await productStore.fetchProduct(route.params.id)
  if (result) {
    product.value = result.data || result
  } else {
    error.value = productStore.error || 'Product not found'
  }
  loading.value = false
})
</script>

<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <button
        @click="router.back()"
        class="text-theme-600 hover:text-theme-800 flex items-center gap-1 mb-6"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>

      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-theme-600"></div>
      </div>

      <div
        v-else-if="error"
        class="bg-red-50 border border-red-200 text-red-700 px-4 py-6 rounded-lg text-center"
      >
        {{ error }}
      </div>

      <div v-else-if="product" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
          <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
              <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>

          <div class="flex flex-col">
            <p v-if="product.category" class="text-sm text-theme-600 font-medium mb-2">
              {{ product.category.name }}
            </p>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>

            <div class="flex items-center gap-4 mb-6">
              <span class="text-3xl font-bold text-theme-600">
                ${{ formatPrice(product.price) }}
              </span>
              <span
                v-if="product.compare_price"
                class="text-lg text-gray-400 line-through"
              >
                ${{ formatPrice(product.compare_price) }}
              </span>
            </div>

            <p v-if="product.short_description" class="text-gray-600 mb-6">
              {{ product.short_description }}
            </p>
            <p v-if="product.description" class="text-gray-600 mb-6 leading-relaxed">
              {{ product.description }}
            </p>

            <div v-if="product.stock !== undefined" class="mb-6">
              <span
                :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                  product.stock > 0
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800',
                ]"
              >
                {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
              </span>
              <span v-if="product.sku" class="ml-4 text-sm text-gray-500">
                SKU: {{ product.sku }}
              </span>
            </div>

            <button
              @click="addToCart"
              class="w-full bg-theme-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-theme-700 transition-colors mt-auto"
            >
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
