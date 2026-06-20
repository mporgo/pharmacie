import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user:  JSON.parse(localStorage.getItem('pharma_user') || 'null'),
    token: localStorage.getItem('pharma_token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,

    // ✅ Sécurisé si user est null
    hasRole: (state) => (role) => {
      return state.user?.roles?.includes(role) ?? false
    },

    hasPermission: (state) => (perm) => {
      return state.user?.permissions?.includes(perm) ?? false
    },
  },

  actions: {
    async login(credentials) {
      const { data } = await api.post('/login', credentials)
      this.token = data.token
      this.user  = data.user
      localStorage.setItem('pharma_token', data.token)
      localStorage.setItem('pharma_user', JSON.stringify(data.user))
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch {}
      // ✅ Reset complet état Pinia
      this.token = null
      this.user  = null
      localStorage.removeItem('pharma_token')
      localStorage.removeItem('pharma_user')
    },

    // ✅ Méthode utilitaire pour rafraîchir le user depuis /me
    async fetchMe() {
      try {
        const { data } = await api.get('/me')
        this.user = data
        localStorage.setItem('pharma_user', JSON.stringify(data))
      } catch {
        this.logout()
      }
    },
  },
})