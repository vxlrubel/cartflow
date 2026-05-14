<template>
  <AuthLayout>
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
      <p class="text-gray-600 mt-2">Join us and start shopping</p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-6">


      <div class="border-l-3 px-3 py-2 border-rose-500 text-rose-600 bg-rose-100 text-sm font-medium">Generated error message.</div>
      <hr>
      <div class="border-l-3 px-3 py-2 border-green-500 text-green-600 bg-green-100 text-sm font-medium">Generated success message.</div>
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


      <div class="space-y-4">

        <InputEmail
          require="true"
          label="Full Name"
          placeholder="Enter your full name"
          v-model="form.name"
        />

        <InputEmail
          require="true"
          label="Email Address"
          placeholder="example@domain.com"
          hint="Enter your valid email address with @"
          v-model="form.email"
        />

        <InputPassword
          label="Password"\
          v-model="form.password"
        />
        <InputPassword
          label="Confirm Password"\
          placeholder="Confirm Your Password"
          v-model="form.password_confirmation"
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
import InputPassword from '@/components/input/InputPassword.vue'
import InputEmail from '@/components/input/InputEmail.vue'

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
