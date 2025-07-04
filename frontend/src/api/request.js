import axios from 'axios'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '../stores/auth'
import { handleApiError } from '../utils/errorHandler'

// 创建axios实例
const request = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 30000 // 增加到30秒
})

// 请求拦截器
request.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// 响应拦截器
request.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // 使用统一的错误处理器
    handleApiError(error)
    return Promise.reject(error)
  }
)

export default request 