import { defineStore } from 'pinia'
import api from '@/lib/axios'

export const useBillingStore = defineStore('billing', {
  state: () => ({
    plan: null,
    status: null,
    usage: {},
    onTrial: false,
    trialEndsAt: null,
    trialDaysLeft: null,
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

        if (this.trialEndsAt) {
          const end = new Date(this.trialEndsAt)
          const now = new Date()

          const diffMs = end - now
          this.trialDaysLeft = Math.ceil(diffMs / (1000 * 60 * 60 * 24))
        }
      } finally {
        this.loading = false
      }
    }
  }
})
