import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia' // Diubah di sini
import { createHead } from '@vueuse/head'
import {
  NButton,
  NSpace,
  NDataTable,
  NConfigProvider,
  NTag,
  NPopselect,
} from 'naive-ui'

const app = createApp(App)
const head = createHead()
const pinia = createPinia() // Diubah di sini

// Daftar komponen Naive UI untuk registrasi global
const naiveComponents = {
  NButton,
  NSpace,
  NDataTable,
  NConfigProvider,
  NTag,
  NPopselect,
}

// Registrasi plugin
app.use(router)
app.use(head)
app.use(pinia) // Diubah di sini

// Registrasi komponen Naive UI secara global
Object.entries(naiveComponents).forEach(([componentName, component]) => {
  app.component(componentName, component)
})

app.mount('#app')