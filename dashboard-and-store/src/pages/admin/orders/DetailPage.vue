<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useOrderStore } from '@/stores/orders'
import CurrencySymbol from '@/components/CurrencySymble.vue'

const route = useRoute()
const router = useRouter()
const store = useOrderStore()

const order = ref(null)
const loading = ref(true)
const showStatusModal = ref(false)
const showPaymentModal = ref(false)
const newStatus = ref('')
const newPaymentStatus = ref('')

const statusOptions = ['pending', 'completed', 'cancelled', 'return']
const paymentStatusOptions = ['paid', 'unpaid', 'pending', 'refunded']

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatPrice = (price) => {
  return parseFloat(price || 0).toFixed(2)
}

const getStatusClass = (status) => {
  const statusMap = {
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    return: 'bg-purple-100 text-purple-800',
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusClass = (status) => {
  const statusMap = {
    paid: 'bg-green-100 text-green-800',
    unpaid: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
    refunded: 'bg-blue-100 text-blue-800',
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const openStatusModal = () => {
  newStatus.value = order.value?.status || 'pending'
  showStatusModal.value = true
}

const openPaymentModal = () => {
  newPaymentStatus.value = order.value?.payment_status || 'unpaid'
  showPaymentModal.value = true
}

const updateStatus = async () => {
  if (!newStatus.value) return
  await store.updateOrderStatus(order.value.id, newStatus.value)
  order.value.status = newStatus.value
  showStatusModal.value = false
}

const updatePaymentStatus = async () => {
  if (!newPaymentStatus.value) return
  await store.updatePaymentStatus(order.value.id, newPaymentStatus.value)
  order.value.payment_status = newPaymentStatus.value
  showPaymentModal.value = false
}

const goBack = () => {
  router.push('/dashboard/orders')
}

onMounted(async () => {
  loading.value = true
  const orderId = route.params.id
  order.value = await store.fetchOrder(orderId)
  loading.value = false
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-neutral-200 p-6">
        <button
          @click="goBack"
          class="inline-flex items-center text-gray-600 hover:text-indigo-600 mb-4"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Orders
        </button>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h2 class="text-2xl font-medium text-gray-800">
            Order #{{ order?.order_number || order?.id }}
          </h2>
          <div class="flex items-center gap-2">
            <span
              :class="[
                'inline-flex capitalize px-3 py-1 rounded-full text-sm font-medium',
                getStatusClass(order?.status),
              ]"
            >
              {{ order?.status || 'pending' }}
            </span>
            <span
              :class="[
                'inline-flex capitalize px-3 py-1 rounded-full text-sm font-medium',
                getPaymentStatusClass(order?.payment_status),
              ]"
            >
              {{ order?.payment_status || 'unpaid' }}
            </span>
          </div>
        </div>
      </div>

      <div v-if="loading" class="p-8 text-center">
        <svg class="animate-spin h-6 w-6 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-500 mt-2">Loading order...</p>
      </div>

      <div v-else-if="!order" class="p-8 text-center text-gray-500">
        Order not found
      </div>

      <div v-else class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Customer Information</h3>
            <div class="text-sm">
              <p class="text-gray-500">Name</p>
              <p class="font-medium">{{ order.user?.name || order.customer_name || '-' }}</p>
            </div>
            <div class="text-sm">
              <p class="text-gray-500">Email</p>
              <p class="font-medium">{{ order.user?.email || order.customer_email || '-' }}</p>
            </div>
            <div class="text-sm">
              <p class="text-gray-500">Phone</p>
              <p class="font-medium">{{ order.user?.phone || order.customer_phone || '-' }}</p>
            </div>
          </div>

          <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Order Information</h3>
            <div class="text-sm">
              <p class="text-gray-500">Order Date</p>
              <p class="font-medium">{{ formatDate(order.created_at) }}</p>
            </div>
            <div class="text-sm">
              <p class="text-gray-500">Last Updated</p>
              <p class="font-medium">{{ formatDate(order.updated_at) }}</p>
            </div>
            <div class="text-sm">
              <p class="text-gray-500">Shipping Address</p>
              <p class="font-medium">{{ order.shipping_address || '-' }}</p>
            </div>
            <div class="text-sm">
              <p class="text-gray-500">Billing Address</p>
              <p class="font-medium">{{ order.billing_address || order.shipping_address || '-' }}</p>
            </div>
          </div>
        </div>

        <div class="mt-8">
          <h3 class="text-lg font-medium text-gray-800 border-b pb-2 mb-4">Order Items</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in order.order_items" :key="item.id">
                  <td class="px-4 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ item.product?.name || item.name || 'Product' }}</div>
                    <div class="text-xs text-gray-500" v-if="item.variation">
                      {{ item.variation.name || '' }}
                    </div>
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-500">{{ item.quantity }}</td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    <CurrencySymbol />{{ formatPrice(item.price) }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    <CurrencySymbol />{{ formatPrice(item.quantity * item.price) }}
                  </td>
                </tr>
                <tr v-if="!order.order_items?.length">
                  <td colspan="4" class="px-4 py-4 text-center text-gray-500">No items found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <div class="w-full max-w-xs space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Subtotal</span>
              <span class="font-medium">
                <CurrencySymbol />{{ formatPrice(order.subtotal || order.total) }}
              </span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Tax</span>
              <span class="font-medium">
                <CurrencySymbol />{{ formatPrice(order.tax || 0) }}
              </span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Shipping</span>
              <span class="font-medium">
                <CurrencySymbol />{{ formatPrice(order.shipping || 0) }}
              </span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Discount</span>
              <span class="font-medium">
                -<CurrencySymbol />{{ formatPrice(order.discount || 0) }}
              </span>
            </div>
            <div class="flex justify-between text-base font-medium border-t pt-2">
              <span>Total</span>
              <span>
                <CurrencySymbol />{{ formatPrice(order.total) }}
              </span>
            </div>
          </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-2">
          <button
            @click="openStatusModal"
            class="inline-flex items-center px-3 py-1 text-sm bg-indigo-600 text-white hover:bg-indigo-700 rounded"
          >
            Update Status
          </button>
          <button
            @click="openPaymentModal"
            class="inline-flex items-center px-3 py-1 text-sm bg-gray-600 text-white hover:bg-gray-700 rounded"
          >
            Update Payment
          </button>
        </div>
      </div>
    </div>

    <div v-if="showStatusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Update Order Status</h3>
        <select
          v-model="newStatus"
          class="w-full border rounded p-2 mb-4"
        >
          <option v-for="status in statusOptions" :key="status" :value="status">
            {{ status }}
          </option>
        </select>
        <div class="flex justify-end gap-2">
          <button
            @click="showStatusModal = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-800"
          >
            Cancel
          </button>
          <button
            @click="updateStatus"
            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
          >
            Update
          </button>
        </div>
      </div>
    </div>

    <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Update Payment Status</h3>
        <select
          v-model="newPaymentStatus"
          class="w-full border rounded p-2 mb-4"
        >
          <option v-for="status in paymentStatusOptions" :key="status" :value="status">
            {{ status }}
          </option>
        </select>
        <div class="flex justify-end gap-2">
          <button
            @click="showPaymentModal = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-800"
          >
            Cancel
          </button>
          <button
            @click="updatePaymentStatus"
            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
          >
            Update
          </button>
        </div>
      </div>
    </div>
  </div>
</template>