<template>
  <AuthLayout>
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Admin Portal</h1>
      <p class="text-gray-600 mt-2">Sign in to manage your store</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4 p-10 bg-white rounded-2xl">
      <div
        v-if="authStore.error"
        class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"
      >
        {{ authStore.error }}
      </div>

      <InputEmail
          require="true"
          label="Email Address"
          placeholder="example@domain.com"
          hint="Enter your valid email address with @"
          v-model="form.email"
        />

      <InputPassword
          label="Password"\
          placeholder="Password"
          v-model="form.password"
        />

      <div class="flex justify-end">
        <PrimaryButton
          type="submit"
          label="Login"
          :disabled="authStore.loading"
          :loading="authStore.loading"
        />
      </div>
    </form>

    <div class="mt-6 text-center">
      <p class="text-gray-600 text-sm font-medium">
        Are you a customer?
        <router-link to="/customer/login" class="text-theme-600 hover:text-theme-700 font-medium">
          Login here
        </router-link>
      </p>
    </div>

    <div class="mt-4 text-center">
      <router-link to="/" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
        &larr; Back to home
      </router-link>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'
import InputPassword from '@/components/input/InputPassword.vue'
import InputEmail from '@/components/input/InputEmail.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'

const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const handleLogin = async () => {
  try {
    await authStore.login(form.value)
    authStore.redirectBasedOnRole()
  } catch (error) {
    console.error('Login failed:', error)
  }
}
</script>
