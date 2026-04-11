<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProductStore } from '@/stores/products'
import TiptapEditor from '@/components/TiptapEditor.vue'

const router = useRouter()
const store = useProductStore()

const form = ref({
  name: '',
  description: '',
  price: '',
  sale_price: '',
  category_id: null,
  brand_id: null,
  stock: '',
  sku: '',
  status: 'active',
})

const categories = ref([])
const brands = ref([])
const loading = ref(false)
const errors = ref({})

const loadFormData = async () => {
  categories.value = await store.fetchCategories()
  brands.value = await store.fetchBrands()
}

const validateForm = () => {
  errors.value = {}
  
  if (!form.value.name.trim()) {
    errors.value.name = 'Product name is required'
  }
  if (!form.value.price || form.value.price <= 0) {
    errors.value.price = 'Price is required and must be greater than 0'
  }
  if (!form.value.stock || form.value.stock < 0) {
    errors.value.stock = 'Stock is required and must be 0 or greater'
  }
  if (!form.value.sku.trim()) {
    errors.value.sku = 'SKU is required'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) return
  
  loading.value = true
  try {
    await store.createProduct({
      ...form.value,
      price: parseFloat(form.value.price),
      sale_price: form.value.sale_price ? parseFloat(form.value.sale_price) : null,
      stock: parseInt(form.value.stock),
      category_id: form.value.category_id || null,
      brand_id: form.value.brand_id || null,
    })
    router.push('/dashboard/products')
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push('/dashboard/products')
}

onMounted(loadFormData)
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add Product</h2>
        <button
          @click="handleCancel"
          class="text-gray-600 hover:text-gray-900"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input
              v-model="form.name"
              type="text"
              class="select w-full"
              :class="{ 'border-red-500': errors.name }"
              placeholder="Enter product name"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
            <input
              v-model="form.sku"
              type="text"
              class="select w-full"
              :class="{ 'border-red-500': errors.sku }"
              placeholder="Enter SKU"
            />
            <p v-if="errors.sku" class="mt-1 text-sm text-red-500">{{ errors.sku[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
            <input
              v-model="form.price"
              type="number"
              step="0.01"
              min="0"
              class="select w-full"
              :class="{ 'border-red-500': errors.price }"
              placeholder="0.00"
            />
            <p v-if="errors.price" class="mt-1 text-sm text-red-500">{{ errors.price[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price</label>
            <input
              v-model="form.sale_price"
              type="number"
              step="0.01"
              min="0"
              class="select w-full"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select v-model="form.category_id" class="select w-full">
              <option value="">Select Category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
            <select v-model="form.brand_id" class="select w-full">
              <option value="">Select Brand</option>
              <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                {{ brand.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
            <input
              v-model="form.stock"
              type="number"
              min="0"
              class="select w-full"
              :class="{ 'border-red-500': errors.stock }"
              placeholder="0"
            />
            <p v-if="errors.stock" class="mt-1 text-sm text-red-500">{{ errors.stock[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="select w-full">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <TiptapEditor v-model="form.description" placeholder="Enter product description" />
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t">
          <button
            type="button"
            @click="handleCancel"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : 'Save Product' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>