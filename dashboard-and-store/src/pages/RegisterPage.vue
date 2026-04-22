<template>
  <AuthLayout>
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
      <p class="text-gray-600 mt-2">Join us and start shopping</p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-6">
      <div
        v-if="authStore.error"
        class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"
      >
        {{ authStore.error }}
      </div>

      <div
        v-if="successMessage"
        class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg"
      >
        {{ successMessage }}
      </div>

      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2"> Full Name </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-theme-500 focus:border-transparent"
          placeholder="John Doe"
        />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
          Email Address
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-theme-500 focus:border-transparent"
          placeholder="you@example.com"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
          Password
        </label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          minlength="8"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-theme-500 focus:border-transparent"
          placeholder="Min. 8 characters"
        />
      </div>

      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
          Confirm Password
        </label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-theme-500 focus:border-transparent"
          placeholder="Confirm your password"
        />
      </div>

      <button
        type="submit"
        :disabled="authStore.loading"
        class="w-full bg-theme-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-theme-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
      >
        {{ authStore.loading ? 'Creating account...' : 'Create Account' }}
      </button>
    </form>

    <div class="mt-6 text-center">
      <p class="text-gray-600 text-sm">
        Already have an account?
        <router-link to="/customer/login" class="text-theme-600 hover:text-theme-700 font-medium">
          Sign in
        </router-link>
      </p>
    </div>

    <div class="mt-2 text-center">
      <router-link to="/" class="text-gray-500 hover:text-gray-700 text-sm">
        &larr; Back to home
      </router-link>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const successMessage = ref('')

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    authStore.error = 'Passwords do not match'
    return
  }

  try {
    await authStore.register(form.value)
    successMessage.value = 'Account created successfully! Redirecting to login...'
    setTimeout(() => {
      router.push('/customer/login')
    }, 1500)
  } catch (error) {
    console.error('Registration failed:', error)
  }
}
</script>
