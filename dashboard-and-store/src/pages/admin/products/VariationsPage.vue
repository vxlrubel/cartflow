<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

const variations = ref([])
const products = ref([])
const loading = ref(false)
const search = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

const form = ref({
  product_id: null,
  sku: '',
  name: '',
  price: '',
  stock: 0,
  is_active: true,
})
const editingId = ref(null)

const fetchVariations = async () => {
  loading.value = true
  try {
    const response = await api.get('/variations', {
      params: { page: currentPage.value, per_page: perPage.value, search: search.value },
    })
    const resData = response.data
    if (Array.isArray(resData)) {
      variations.value = resData
      totalItems.value = resData.length
    } else {
      variations.value = resData.data || resData.variations || resData || []
      totalItems.value = resData.total || variations.value.length
    }
  } catch (error) {
    console.error('Error fetching variations:', error)
  } finally {
    loading.value = false
  }
}

const fetchProducts = async () => {
  try {
    const response = await api.get(API_ENDPOINTS.products.list, {
      params: { per_page: 100 },
    })
    products.value = response.data.data || response.data.products || []
  } catch (error) {
    console.error('Error fetching products:', error)
  }
}

const resetForm = () => {
  form.value = { product_id: null, sku: '', name: '', price: '', stock: 0, is_active: true }
  editingId.value = null
}

const submitForm = async () => {
  try {
    const payload = {
      ...form.value,
      price: parseFloat(form.value.price),
      stock: parseInt(form.value.stock),
    }
    if (editingId.value) {
      await api.put(API_ENDPOINTS.attributes.updateVariation(editingId.value), payload)
    } else if (form.value.product_id) {
      await api.post(API_ENDPOINTS.products.createVariation(form.value.product_id), payload)
    }
    resetForm()
    fetchVariations()
  } catch (error) {
    console.error('Error saving variation:', error)
  }
}

const editVariation = (variation) => {
  form.value = { ...variation }
  editingId.value = variation.id
}

const deleteVariation = async (id) => {
  if (!confirm('Are you sure you want to delete this variation?')) return
  try {
    await api.delete(API_ENDPOINTS.attributes.deleteVariation(id))
    fetchVariations()
  } catch (error) {
    console.error('Error deleting variation:', error)
  }
}

const paginate = (page) => {
  currentPage.value = page
  fetchVariations()
}

const filteredVariations = computed(() => variations.value)

onMounted(() => {
  fetchVariations()
  fetchProducts()
})
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Variations</h2>
      <button
        @click="resetForm()"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Add Variation
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">
            {{ editingId ? 'Edit Variation' : 'Add Variation' }}
          </h3>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Product</label>
              <select
                v-model="form.product_id"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option :value="null">Select a product</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Variation Name</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g., Red, Large"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">SKU</label>
              <input
                v-model="form.sku"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g., PROD-RED-L"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Price</label>
              <input
                v-model="form.price"
                type="number"
                step="0.01"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="0.00"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Stock</label>
              <input
                v-model="form.stock"
                type="number"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="0"
                required
              />
            </div>
            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="mr-2" />
              <label for="is_active" class="text-sm">Active</label>
            </div>
            <div class="flex gap-2">
              <button
                type="submit"
                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
              >
                {{ editingId ? 'Update' : 'Create' }}
              </button>
              <button
                v-if="editingId"
                type="button"
                @click="resetForm()"
                class="px-4 py-2 border rounded hover:bg-gray-50"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
          <div class="p-4 border-b">
            <input
              v-model="search"
              @input="
                currentPage = 1,
                fetchVariations()
              "
              type="text"
              placeholder="Search variations..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Product</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">SKU</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Price</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Stock</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="7" class="px-4 py-8 text-center text-gray-500">Loading...</td>
                </tr>
                <tr v-else-if="variations.length === 0">
                  <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    No variations found
                  </td>
                </tr>
                <tr
                  v-for="variation in filteredVariations"
                  :key="variation.id"
                  class="border-t hover:bg-gray-50"
                >
                  <td class="px-4 py-3">
                    {{ variation.product_name || variation.product?.name || '-' }}
                  </td>
                  <td class="px-4 py-3">{{ variation.name }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ variation.sku }}</td>
                  <td class="px-4 py-3">${{ variation.price }}</td>
                  <td class="px-4 py-3">{{ variation.stock }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="
                        variation.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'
                      "
                      class="px-2 py-1 rounded text-xs"
                    >
                      {{ variation.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <button
                      @click="editVariation(variation)"
                      class="text-blue-600 hover:text-blue-800 mr-3"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteVariation(variation.id)"
                      class="text-red-600 hover:text-red-800"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="totalPages > 1" class="p-4 border-t flex justify-center gap-2">
            <button
              v-for="page in totalPages"
              :key="page"
              @click="paginate(page)"
              :class="
                page === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'
              "
              class="px-3 py-1 rounded"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
