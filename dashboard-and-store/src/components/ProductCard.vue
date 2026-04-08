<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    <router-link :to="`/products/${product.id}`" class="block">
      <div class="aspect-square bg-gray-100 overflow-hidden">
        <img
          v-if="product.image"
          :src="product.image"
          :alt="product.name"
          class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
        />
        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
          <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>
    </router-link>
    <div class="p-4">
      <router-link :to="`/products/${product.id}`">
        <h3 class="font-semibold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-2">
          {{ product.name }}
        </h3>
      </router-link>
      <p v-if="product.category" class="text-sm text-gray-500 mt-1">
        {{ product.category.name }}
      </p>
      <div class="flex items-center justify-between mt-3">
        <span class="text-lg font-bold text-indigo-600">
          ${{ formatPrice(product.price) }}
        </span>
        <button
          @click.prevent="addToCart"
          class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors"
          title="Add to cart"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  product: {
    type: Object,
    required: true,
  },
})

const formatPrice = (price) => {
  return parseFloat(price).toFixed(2)
}

const addToCart = () => {
  console.log('Add to cart:', defineProps.product?.id)
}
</script>
