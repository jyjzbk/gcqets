import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import router from './router'
import { permission, role } from './utils/permission'
import './style.css'
import App from './App.vue'

// 开发环境图标检查
if (import.meta.env.DEV) {
  import('./utils/iconCheck.js')
}

const app = createApp(App)
const pinia = createPinia()

// 注册Element Plus图标
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}

// 注册权限指令
app.directive('permission', permission)
app.directive('role', role)

app.use(pinia)
app.use(router)
app.use(ElementPlus)

app.mount('#app')
