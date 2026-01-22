import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

;(async () => {
  const { useAuthStore } = await import('@/stores/auth')
  const auth = useAuthStore()
  await auth.hydrate()
  app.mount('#app')
})()

