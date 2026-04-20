<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '@/stores/products'
import TiptapEditor from '@/components/TiptapEditor.vue'
import ImageUploader from '@/components/ImageUploader.vue'

const route = useRoute()
const router = useRouter()
const store = useProductStore()

const productId = route.params.id

const form = ref({
  name: '',
  description: '',
  price: '',
  sale_price: '',
  slug: '',
  category_ids: [],
  brand_id: null,
  stock: '',
  sku: '',
  status: 'active',
  images: [],
})

const categories = ref([])
const brands = ref([])
const loading = ref(false)
const fetching = ref(true)
const errors = ref({})
const newCategory = ref('')
const newBrand = ref('')

const slugPattern = /^[a-z0-9-]+$/

const isValidSlug = computed(() => {
  if (!form.value.slug) return true
  return slugPattern.test(form.value.slug)
})

const loadFormData = async () => {
  const [cats, brds] = await Promise.all([store.fetchCategories(), store.fetchBrands()])
  categories.value = cats
  brands.value = brds

  const product = await store.fetchProduct(productId)
  if (product) {
    form.value = {
      name: product.name || '',
      description: product.description || '',
      price: product.price || '',
      sale_price: product.sale_price || '',
      slug: product.slug || '',
      category_ids: product.categories ? product.categories.map((c) => c.id) : [],
      brand_id: product.brand_id ? product.brand_id : null,
      stock: product.stock || '',
      sku: product.sku || '',
      status: product.status || 'active',
      images: product.images || [],
    }
  }
  fetching.value = false
}

const createCategory = async () => {
  if (!newCategory.value.trim()) return

  try {
    const category = await store.createCategory(newCategory.value.trim())
    if (category) {
      categories.value.push(category)
      form.value.category_ids.push(category.id)
      newCategory.value = ''
    }
  } catch (err) {
    errors.value.category = err.response?.data?.message || 'Failed to create category'
  }
}

const createBrand = async () => {
  if (!newBrand.value.trim()) return

  try {
    const brand = await store.createBrand(newBrand.value.trim())
    if (brand) {
      brands.value.push(brand)
      form.value.brand_id = brand.id
      newBrand.value = ''
    }
  } catch (err) {
    errors.value.brand = err.response?.data?.message || 'Failed to create brand'
  }
}

const validateForm = () => {
  errors.value = {}

  if (!form.value.name.trim()) {
    errors.value.name = 'Product name is required'
  }
  if (form.value.slug && !isValidSlug.value) {
    errors.value.slug =
      'Slug can only contain lowercase letters (a-z), numbers (0-9), and hyphens (-)'
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
    await store.updateProduct(productId, {
      ...form.value,
      price: parseFloat(form.value.price),
      sale_price: form.value.sale_price ? parseFloat(form.value.sale_price) : null,
      stock: parseInt(form.value.stock),
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
    <div class="bg-white rounded-lg shadow">
      <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
        <h2 class="text-2xl font-bold text-gray-800">Edit Product</h2>
        <button @click="handleCancel" class="text-gray-600 hover:text-gray-900">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <div v-if="fetching" class="flex items-center justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-12 gap-6 px-6 py-4 border-b border-neutral-200">
          <div class="col-span-12 md:col-span-7 lg:col-span-8 xl:col-span-9 space-y-5">
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <TiptapEditor v-model="form.description" placeholder="Enter product description" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
              <input
                type="text"
                v-model="form.slug"
                class="select w-full"
                :class="{ 'border-red-500': errors.slug }"
                placeholder="Enter product slug (e.g., product-name)"
              />
              <p v-if="errors.slug" class="mt-1 text-sm text-red-500">{{ errors.slug[0] }}</p>
              <p v-else-if="form.slug && !isValidSlug" class="mt-1 text-sm text-red-500">
                Slug can only contain lowercase letters (a-z), numbers (0-9), and hyphens (-)
              </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-6">
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
                  <label class="block text-sm font-medium text-gray-700 mb-1">Images</label>
                  <ImageUploader v-model="form.images" :multiple="true" :max-files="5" />
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
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-5 lg:col-span-4 xl:col-span-3 space-y-5">
            <div class="border border-neutral-200 rounded">
              <div class="px-3 py-1 font-medium bg-neutral-200">Status</div>
              <ul class="select-none overflow-y-auto text-sm">
                <li>
                  <label
                    class="text-sm text-gray-700 flex items-center space-x-1 py-[2px] px-3 cursor-pointer hover:bg-neutral-100"
                  >
                    <input type="radio" name="status" value="active" v-model="form.status" />
                    <span>Active</span>
                  </label>
                </li>
                <li>
                  <label
                    class="text-sm text-gray-700 flex items-center space-x-1 py-1 px-3 cursor-pointer hover:bg-neutral-100"
                  >
                    <input type="radio" name="status" value="inactive" v-model="form.status" />
                    <span>Inactive</span>
                  </label>
                </li>
              </ul>
            </div>

            <div class="border border-neutral-200 rounded">
              <div class="px-3 py-1 font-medium bg-neutral-200">Category</div>
              <ul class="h-30 select-none overflow-y-auto text-sm">
                <li v-for="category in categories" :key="category.id">
                  <label
                    class="text-sm text-gray-700 flex items-center space-x-1 py-[2px] px-3 cursor-pointer hover:bg-neutral-100"
                  >
                    <input type="checkbox" :value="category.id" v-model="form.category_ids" />
                    <span>{{ category.name }}</span>
                  </label>
                </li>
              </ul>
              <div class="border-t border-neutral-200 px-3 py-2 flex items-center space-x-2">
                <input
                  type="text"
                  v-model="newCategory"
                  @keyup.enter="createCategory"
                  placeholder="Add new category"
                  class="flex-1 text-[12px] h-7 px-3 border border-indigo-400 bg-neutral-100 rounded focus:bg-indigo-50 focus:text-indigo-700 focus:border-indigo-400 focus:outline-indigo-400"
                />
                <button
                  type="button"
                  @click="createCategory"
                  class="px-3 h-7 text-[12px] font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                >
                  Add
                </button>
              </div>
              <p v-if="errors.category" class="px-3 pb-2 text-xs text-red-500">
                {{ errors.category }}
              </p>
            </div>

            <div class="border border-neutral-200 rounded">
              <div class="px-3 py-1 font-medium bg-neutral-200">Brand</div>
              <ul class="h-30 select-none overflow-y-auto text-sm">
                <li v-for="brand in brands" :key="brand.id">
                  <label
                    class="text-sm text-gray-700 flex items-center space-x-1 py-[2px] px-3 cursor-pointer hover:bg-neutral-100"
                  >
                    <input type="radio" name="brand" :value="brand.id" v-model="form.brand_id" />
                    <span>{{ brand.name }}</span>
                  </label>
                </li>
              </ul>

              <div class="border-t border-neutral-200 px-3 py-2 flex items-center space-x-2">
                <input
                  type="text"
                  v-model="newBrand"
                  @keyup.enter="createBrand"
                  placeholder="Add new brand"
                  class="flex-1 text-[12px] h-7 px-3 border border-indigo-400 bg-neutral-100 rounded focus:bg-indigo-50 focus:text-indigo-700 focus:border-indigo-400 focus:outline-indigo-400"
                />
                <button
                  type="button"
                  @click="createBrand"
                  class="px-3 h-7 text-[12px] font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                >
                  Add
                </button>
              </div>
              <p v-if="errors.brand" class="px-3 pb-2 text-xs text-red-500">{{ errors.brand }}</p>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 flex justify-end space-x-3">
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
            {{ loading ? 'Updating...' : 'Update Product' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
