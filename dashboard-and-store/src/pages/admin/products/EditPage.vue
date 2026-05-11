<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '@/stores/products'
import TiptapEditor from '@/components/TiptapEditor.vue'
import ImageUploader from '@/components/ImageUploader.vue'
import PrimacyButton from '@/components/buttons/PrimacyButton.vue'
import CancelButton from '@/components/buttons/CancelButton.vue'
import handleAxiosError from '@/services/handleAxiosError'
import { openMediaBox } from '@/services/media-box'
import { showToast } from '@/services/toast'


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

  const payload = {
    ...form.value,
    price: parseFloat(form.value.price),
    sale_price: form.value.sale_price ? parseFloat(form.value.sale_price) : null,
    stock: parseInt(form.value.stock),
    brand_id: form.value.brand_id || null,
  }

  try {

    await store.updateProduct(productId, payload)

    showToast('success', 'Product updated successfully!')

  } catch (err) {

    const response = handleAxiosError(err)

    if (response.errors) {
      errors.value = response.errors
    }

    showToast(response.status, response.message)

  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push('/dashboard/products')
}

onMounted(loadFormData)


const image = ref(null)

const chooseImage = async () => {

  const media = await openMediaBox()

  image.value = media
}

const enabled = ref(false)


</script>

<template>
  <div>

    <div v-if="fetching" class="flex items-center justify-center py-12">
      <svg class="animate-spin h-8 w-8 text-theme-600" fill="none" viewBox="0 0 24 24">
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
    <div v-else class="flex flex-col md:flex-row gap-4 lg:gap-8 mb-20">
      <div class="md:flex-1 bg-white border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800 px-4 lg:px-6 h-12.5 flex items-center border-b border-gray-300">Edit Product</h2>
        <div class="py-3 px-4 lg:px-6">
          <div class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
              <input
                v-model="form.name"
                type="text"
                class="input-field"
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
                class="input-field"
                :class="{ 'invalid': errors.slug }"
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
                    class="input-field"
                    :class="{ 'invalid': errors.sku }"
                    placeholder="Enter SKU"
                  />
                  <p v-if="errors.sku" class="mt-1 text-sm text-red-500">{{ errors.sku[0] }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Images</label>
                  <ImageUploader v-model="form.images" :multiple="true" :max-files="5" />
                </div>

              </div>
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                  <input
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="input-field"
                    :class="{ 'invalid': errors.price }"
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
                    class="input-field"
                    placeholder="0.00"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                  <input
                    v-model="form.stock"
                    type="number"
                    min="0"
                    class="input-field"
                    :class="{ 'invalid': errors.stock }"
                    placeholder="0"
                  />
                  <p v-if="errors.stock" class="mt-1 text-sm text-red-500">{{ errors.stock[0] }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="md:w-75 bg-white sticky top-20">
        <div class="p-3 h-12.5 flex justify-end items-center border-b border-gray-300">
          <CancelButton
            label="Cancel"
            @click="handleCancel"
            :disabled="loading"
          />
          <PrimacyButton
            label="Update"
            class="ml-2"
            @click="handleSubmit"
            :loading="loading"
          />
        </div>

        <div class="p-3 space-y-5">

          <div class="border border-neutral-200 rounded">
            <div class="px-3 py-1 font-medium bg-gray-50 border-b text-sm border-neutral-200 flex items-center justify-between">
              <span>Publish</span>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer">
                <span class="w-12 h-6 bg-gray-300 rounded peer-checked:bg-theme-500 transition-colors duration-300"></span>
                <span class="absolute left-1 top-1 w-5 h-4 bg-white rounded shadow-md transition-transform duration-300 peer-checked:translate-x-5"></span>
              </label>
            </div>
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
            <div class="px-3 py-1 font-medium bg-gray-50 border-b text-sm border-neutral-200">Feature image</div>
            <div
              v-if="image"
              class="aspect-video relative">
               <img
                  :src="image.url"
                  class="h-full w-full object-cover"
                >
            </div>
            <div v-else class="p-3">
              <button
                @click="chooseImage"
                class="block text-sm font-medium text-center border border-neutral-300 text-gray-700 bg-neutral-100 py-3 w-full transition-colors duration-200 cursor-pointer hover:bg-neutral-200 hover:text-theme-500">
                Choose Image
              </button>
              <!-- Switch Button -->


            </div>
          </div>

          <div class="border border-neutral-200 rounded">
            <div class="px-3 py-1 font-medium bg-gray-50 border-b text-sm border-neutral-200">Category</div>
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
                class="flex-1 text-[12px] h-7 px-3 border border-theme-400 bg-neutral-100 rounded focus:bg-theme-50 focus:text-theme-700 focus:border-theme-400 focus:outline-theme-400"
              />
              <button
                type="button"
                @click="createCategory"
                class="px-3 h-7 text-[12px] font-medium text-white bg-theme-600 rounded hover:bg-theme-700 focus:outline-none focus:ring-2 focus:ring-theme-500 cursor-pointer"
              >
                Add
              </button>
            </div>
            <p v-if="errors.category" class="px-3 pb-2 text-xs text-red-500">
              {{ errors.category }}
            </p>
          </div>

          <div class="border border-neutral-200 rounded">
            <div class="px-3 py-1 font-medium bg-gray-50 border-b text-sm border-neutral-200">Brand</div>
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
                class="flex-1 text-[12px] h-7 px-3 border border-theme-400 bg-neutral-100 rounded focus:bg-theme-50 focus:text-theme-700 focus:border-theme-400 focus:outline-theme-400"
              />
              <button
                type="button"
                @click="createBrand"
                class="px-3 h-7 text-[12px] font-medium text-white bg-theme-600 rounded hover:bg-theme-700 focus:outline-none focus:ring-2 focus:ring-theme-500 cursor-pointer"
              >
                Add
              </button>
            </div>
            <p v-if="errors.brand" class="px-3 pb-2 text-xs text-red-500">{{ errors.brand }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
