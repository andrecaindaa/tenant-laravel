import { defineStore } from 'pinia'
import api from '@/lib/axios'

export const useBillingStore = defineStore('billing', {
  state: () => ({
    plan: null,
    usage: {},
    loading: false,
  }),

  actions: {
    async fetch() {
      this.loading = true

      try {
        const { data } = await api.get('/billing')
        this.plan = data.plan
        this.usage = data.usage
      } finally {
        this.loading = false
      }
    }
  }
})
