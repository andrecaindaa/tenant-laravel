import { defineStore } from 'pinia'
import api from '@/lib/axios'

export const useTenantStore = defineStore('tenant', {
  state: () => ({
    currentTenant: null,
  }),

  actions: {
    async selectTenant(tenantId) {
      const response = await api.post(`/select-tenant/${tenantId}`)
      this.currentTenant = response.data.tenant
    }
  }
})
