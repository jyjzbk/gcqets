import axios from 'axios'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

// 创建axios实例
const request = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// 请求拦截器
request.interceptors.request.use(
  config => {
    // 添加认证token
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    
    return config
  },
  error => {
    console.error('请求错误:', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
request.interceptors.response.use(
  response => {
    // 直接返回响应数据
    return response
  },
  error => {
    console.error('响应错误:', error)
    
    // 处理不同的错误状态
    if (error.response) {
      const { status, data } = error.response
      
      switch (status) {
        case 401:
          // 未授权，清除token并跳转到登录页
          const authStore = useAuthStore()
          authStore.logout()
          router.push('/login')
          ElMessage.error('登录已过期，请重新登录')
          break
          
        case 403:
          ElMessage.error('权限不足，无法访问')
          break
          
        case 404:
          ElMessage.error('请求的资源不存在')
          break
          
        case 422:
          // 表单验证错误
          if (data.errors) {
            const firstError = Object.values(data.errors)[0]
            ElMessage.error(Array.isArray(firstError) ? firstError[0] : firstError)
          } else {
            ElMessage.error(data.message || '数据验证失败')
          }
          break
          
        case 429:
          ElMessage.error('请求过于频繁，请稍后重试')
          break
          
        case 500:
          ElMessage.error('服务器内部错误，请稍后重试')
          break
          
        default:
          ElMessage.error(data.message || `请求失败 (${status})`)
      }
    } else if (error.request) {
      // 网络错误
      ElMessage.error('网络连接失败，请检查网络设置')
    } else {
      // 其他错误
      ElMessage.error('请求配置错误')
    }
    
    return Promise.reject(error)
  }
)

export default request
