import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '../api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const userPermissions = computed(() => {
    if (!user.value) return []
    return user.value.permissions || []
  })
  const userRoles = computed(() => {
    if (!user.value) return []
    return user.value.roles || []
  })

  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await authApi.login(credentials)
      const { user: userData, token: tokenData } = response.data.data
      
      user.value = userData
      token.value = tokenData
      localStorage.setItem('token', tokenData)
      
      return response
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await authApi.logout()
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
    }
  }

  const getUser = async () => {
    try {
      const response = await authApi.getUser()
      user.value = response.data.data
      return response
    } catch (error) {
      throw error
    }
  }

  const hasPermission = (permission) => {
    if (!user.value || !user.value.permissions) return false
    return user.value.permissions.some(p => p.name === permission)
  }

  const hasRole = (role) => {
    if (!user.value || !user.value.roles) return false
    return user.value.roles.some(r => r.name === role)
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    userPermissions,
    userRoles,
    login,
    logout,
    getUser,
    hasPermission,
    hasRole
  }
}) 