import { ElMessage, ElMessageBox, ElNotification } from 'element-plus'
import router from '@/router'
import { useAuthStore } from '@/stores/auth'

/**
 * 全局错误处理器
 */
class ErrorHandler {
  constructor() {
    this.setupGlobalErrorHandlers()
  }

  /**
   * 设置全局错误处理
   */
  setupGlobalErrorHandlers() {
    // 处理未捕获的Promise错误
    window.addEventListener('unhandledrejection', (event) => {
      console.error('Unhandled promise rejection:', event.reason)
      this.handleError(event.reason)
      event.preventDefault()
    })

    // 处理JavaScript运行时错误
    window.addEventListener('error', (event) => {
      console.error('JavaScript error:', event.error)
      this.handleError(event.error)
    })

    // 处理Vue错误
    if (window.Vue) {
      window.Vue.config.errorHandler = (err, vm, info) => {
        console.error('Vue error:', err, info)
        this.handleError(err, { component: vm, info })
      }
    }
  }

  /**
   * 处理API错误
   */
  handleApiError(error) {
    const { response, request, message } = error

    // 网络错误
    if (!response && request) {
      this.showNetworkError()
      return
    }

    // 没有响应对象，可能是请求配置错误
    if (!response) {
      this.showGenericError(message || '请求配置错误')
      return
    }

    const { status, data } = response

    switch (status) {
      case 400:
        this.handleBadRequest(data)
        break
      case 401:
        this.handleUnauthorized(data)
        break
      case 403:
        this.handleForbidden(data)
        break
      case 404:
        this.handleNotFound(data)
        break
      case 422:
        this.handleValidationError(data)
        break
      case 429:
        this.handleRateLimitError(data)
        break
      case 500:
        this.handleServerError(data)
        break
      case 502:
      case 503:
      case 504:
        this.handleServiceUnavailable(data)
        break
      default:
        this.handleGenericError(data)
    }
  }

  /**
   * 处理400错误
   */
  handleBadRequest(data) {
    const message = data?.message || '请求参数错误'
    ElMessage.error(message)
  }

  /**
   * 处理401错误
   */
  handleUnauthorized(data) {
    const message = data?.message || '登录已过期，请重新登录'
    
    ElMessageBox.confirm(message, '登录过期', {
      confirmButtonText: '重新登录',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      const authStore = useAuthStore()
      authStore.logout()
      router.push('/login')
    }).catch(() => {
      // 用户取消
    })
  }

  /**
   * 处理403错误
   */
  handleForbidden(data) {
    const message = data?.message || '没有权限执行此操作'
    ElMessage.error(message)
  }

  /**
   * 处理404错误
   */
  handleNotFound(data) {
    const message = data?.message || '请求的资源不存在'
    ElMessage.error(message)
  }

  /**
   * 处理422验证错误
   */
  handleValidationError(data) {
    if (data?.errors) {
      // 显示第一个验证错误
      const firstError = Object.values(data.errors)[0]
      if (Array.isArray(firstError) && firstError.length > 0) {
        ElMessage.error(firstError[0])
      } else {
        ElMessage.error('数据验证失败')
      }
    } else {
      ElMessage.error(data?.message || '数据验证失败')
    }
  }

  /**
   * 处理429限流错误
   */
  handleRateLimitError(data) {
    const message = data?.message || '请求过于频繁，请稍后再试'
    ElMessage.warning(message)
  }

  /**
   * 处理500服务器错误
   */
  handleServerError(data) {
    const message = data?.message || '服务器内部错误'
    ElNotification.error({
      title: '服务器错误',
      message: message,
      duration: 5000
    })
  }

  /**
   * 处理服务不可用错误
   */
  handleServiceUnavailable(data) {
    const message = data?.message || '服务暂时不可用，请稍后再试'
    ElNotification.warning({
      title: '服务不可用',
      message: message,
      duration: 5000
    })
  }

  /**
   * 处理网络错误
   */
  showNetworkError() {
    ElNotification.error({
      title: '网络错误',
      message: '网络连接失败，请检查网络设置',
      duration: 5000
    })
  }

  /**
   * 处理通用错误
   */
  handleGenericError(data) {
    const message = data?.message || '操作失败，请稍后再试'
    ElMessage.error(message)
  }

  /**
   * 处理一般错误
   */
  handleError(error, context = {}) {
    console.error('Error occurred:', error, context)

    // 如果是API错误，使用专门的处理方法
    if (error.response || error.request) {
      this.handleApiError(error)
      return
    }

    // 其他类型的错误
    let message = '发生未知错误'
    
    if (error instanceof Error) {
      message = error.message
    } else if (typeof error === 'string') {
      message = error
    }

    // 在开发环境显示详细错误信息
    if (import.meta.env.DEV) {
      console.error('Detailed error:', error)
      ElNotification.error({
        title: '开发环境错误',
        message: message,
        duration: 0 // 不自动关闭
      })
    } else {
      ElMessage.error('系统出现异常，请稍后再试')
    }
  }

  /**
   * 显示成功消息
   */
  showSuccess(message, options = {}) {
    ElMessage.success({
      message,
      duration: 3000,
      ...options
    })
  }

  /**
   * 显示警告消息
   */
  showWarning(message, options = {}) {
    ElMessage.warning({
      message,
      duration: 3000,
      ...options
    })
  }

  /**
   * 显示信息消息
   */
  showInfo(message, options = {}) {
    ElMessage.info({
      message,
      duration: 3000,
      ...options
    })
  }

  /**
   * 显示确认对话框
   */
  confirm(message, title = '确认操作', options = {}) {
    return ElMessageBox.confirm(message, title, {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
      ...options
    })
  }

  /**
   * 显示提示对话框
   */
  alert(message, title = '提示', options = {}) {
    return ElMessageBox.alert(message, title, {
      confirmButtonText: '确定',
      ...options
    })
  }

  /**
   * 显示输入对话框
   */
  prompt(message, title = '输入', options = {}) {
    return ElMessageBox.prompt(message, title, {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      ...options
    })
  }

  /**
   * 显示通知
   */
  notify(options = {}) {
    return ElNotification({
      duration: 4500,
      ...options
    })
  }

  /**
   * 显示加载提示
   */
  showLoading(message = '加载中...', options = {}) {
    return ElLoading.service({
      lock: true,
      text: message,
      background: 'rgba(0, 0, 0, 0.7)',
      ...options
    })
  }
}

// 创建全局实例
const errorHandler = new ErrorHandler()

// 导出常用方法
export const handleError = (error, context) => errorHandler.handleError(error, context)
export const handleApiError = (error) => errorHandler.handleApiError(error)
export const showSuccess = (message, options) => errorHandler.showSuccess(message, options)
export const showWarning = (message, options) => errorHandler.showWarning(message, options)
export const showInfo = (message, options) => errorHandler.showInfo(message, options)
export const confirm = (message, title, options) => errorHandler.confirm(message, title, options)
export const alert = (message, title, options) => errorHandler.alert(message, title, options)
export const prompt = (message, title, options) => errorHandler.prompt(message, title, options)
export const notify = (options) => errorHandler.notify(options)
export const showLoading = (message, options) => errorHandler.showLoading(message, options)

export default errorHandler
