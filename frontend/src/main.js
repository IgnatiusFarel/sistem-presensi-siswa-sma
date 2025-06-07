import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
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
const pinia = createPinia() 

const naiveComponents = {
  NButton,
  NSpace,
  NDataTable,
  NConfigProvider,
  NTag,
  NPopselect,
}

app.use(router)
app.use(head)
app.use(pinia) 

Object.entries(naiveComponents).forEach(([componentName, component]) => {
  app.component(componentName, component)
})

app.mount('#app')