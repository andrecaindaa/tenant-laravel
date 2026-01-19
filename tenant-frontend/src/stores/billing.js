import { defineStore } from 'pinia'
import api from '@/lib/axios'

export const useBillingStore = defineStore('billing', {
  state: () => ({
    plan: null,
    status: null,
    usage: {},
    onTrial: false,
    trialEndsAt: null,
    loading: false,
  }),

  actions: {
    async fetch() {
      this.loading = true

      try {
        const { data } = await api.get('/billing')

        this.plan = data.plan
        this.status = data.status
        this.usage = data.usage
        this.onTrial = data.on_trial
        this.trialEndsAt = data.trial_ends_at
      } finally {
        this.loading = false
      }
    }
  }
})
