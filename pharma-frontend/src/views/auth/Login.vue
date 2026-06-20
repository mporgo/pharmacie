<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-900 to-blue-700 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">

      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
          <span class="text-white text-2xl">💊</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Pharma Manager</h1>
        <p class="text-gray-500 text-sm mt-1">Connexion à votre espace</p>
      </div>

      <!-- Formulaire -->
      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="adresse mail"
          />
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="••••••••"
          />
        </div>

        <div v-if="error" class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition disabled:opacity-60"
        >
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

const router  = useRouter()
const auth    = useAuthStore()
const loading = ref(false)
const error   = ref('')
const form    = ref({ email: '', password: '' })

async function handleLogin() {
  loading.value = true
  error.value   = ''
  try {
    await auth.login(form.value)
    router.push('/dashboard')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur de connexion.'
  } finally {
    loading.value = false
  }
}
</script>