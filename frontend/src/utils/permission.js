import { useAuthStore } from '../stores/auth'

/**
 * 检查用户是否有指定权限
 * @param {string} permission 权限名称
 * @returns {boolean}
 */
export function hasPermission(permission) {
  const authStore = useAuthStore()
  return authStore.hasPermission(permission)
}

/**
 * 检查用户是否有任一指定权限
 * @param {Array} permissions 权限名称数组
 * @returns {boolean}
 */
export function hasAnyPermission(permissions) {
  const authStore = useAuthStore()
  return permissions.some(permission => authStore.hasPermission(permission))
}

/**
 * 检查用户是否有所有指定权限
 * @param {Array} permissions 权限名称数组
 * @returns {boolean}
 */
export function hasAllPermissions(permissions) {
  const authStore = useAuthStore()
  return permissions.every(permission => authStore.hasPermission(permission))
}

/**
 * 检查用户是否有指定角色
 * @param {string} role 角色名称
 * @returns {boolean}
 */
export function hasRole(role) {
  const authStore = useAuthStore()
  return authStore.hasRole(role)
}

/**
 * 检查用户是否有任一指定角色
 * @param {Array} roles 角色名称数组
 * @returns {boolean}
 */
export function hasAnyRole(roles) {
  const authStore = useAuthStore()
  return roles.some(role => authStore.hasRole(role))
}

/**
 * 权限指令
 */
export const permission = {
  mounted(el, binding) {
    const { value } = binding
    const authStore = useAuthStore()
    
    let hasAuth = false
    
    if (typeof value === 'string') {
      hasAuth = authStore.hasPermission(value)
    } else if (Array.isArray(value)) {
      if (value.length === 0) {
        hasAuth = true
      } else {
        hasAuth = value.some(permission => authStore.hasPermission(permission))
      }
    }
    
    if (!hasAuth) {
      el.parentNode?.removeChild(el)
    }
  }
}

/**
 * 角色指令
 */
export const role = {
  mounted(el, binding) {
    const { value } = binding
    const authStore = useAuthStore()
    
    let hasAuth = false
    
    if (typeof value === 'string') {
      hasAuth = authStore.hasRole(value)
    } else if (Array.isArray(value)) {
      if (value.length === 0) {
        hasAuth = true
      } else {
        hasAuth = value.some(role => authStore.hasRole(role))
      }
    }
    
    if (!hasAuth) {
      el.parentNode?.removeChild(el)
    }
  }
} 