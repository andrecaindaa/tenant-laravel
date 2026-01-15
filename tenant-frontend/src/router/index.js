import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTenantStore } from '@/stores/tenant'

const routes = [
  { path: '/login', component: () => import('@/views/LoginView.vue') },
  { path: '/tenants', component: () => import('@/views/TenantSelectView.vue') },
  { path: '/dashboard', component: () => import('@/views/DashboardView.vue') },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  const tenant = useTenantStore()

  if (!auth.user && to.path !== '/login') {
    return '/login'
  }

  if (auth.user && !tenant.currentTenant && to.path === '/dashboard') {
    return '/tenants'
  }
})

export default router
