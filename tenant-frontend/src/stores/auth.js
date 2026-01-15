import { defineStore } from 'pinia'
import api from '@/lib/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    tenants: [],
    loading: false,
  }),

  actions: {
    async login(email, password) {
      this.loading = true

      try {

        await api.get('/sanctum/csrf-cookie')

        const response = await api.post('/login', {
          email,
          password,
        })

        this.user = response.data.user
        this.tenants = response.data.tenants
      } finally {
        this.loading = false
      }
    },

    async hydrate() {
    try {
        const { data } = await api.get('/me')
        this.user = data.user
    } catch {
        this.user = null
    }
    },

    async logout() {
      await api.post('/logout')
      this.user = null
      this.tenants = []
    }
  }
})
