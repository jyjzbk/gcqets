import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { roleApi } from '../api/role'

export const useRoleStore = defineStore('role', () => {
  const roles = ref([])
  const currentRole = ref(null)
  const loading = ref(false)
  const total = ref(0)

  const getRoles = async (params = {}) => {
    loading.value = true
    try {
      const response = await roleApi.getList(params)
      roles.value = response.data.data.data
      total.value = response.data.data.total
      return response
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const getRole = async (id) => {
    try {
      const response = await roleApi.getDetail(id)
      currentRole.value = response.data.data
      return response
    } catch (error) {
      throw error
    }
  }

  const createRole = async (data) => {
    try {
      const response = await roleApi.create(data)
      await getRoles()
      return response
    } catch (error) {
      throw error
    }
  }

  const updateRole = async (id, data) => {
    try {
      const response = await roleApi.update(id, data)
      await getRoles()
      return response
    } catch (error) {
      throw error
    }
  }

  const deleteRole = async (id) => {
    try {
      const response = await roleApi.delete(id)
      await getRoles()
      return response
    } catch (error) {
      throw error
    }
  }

  const assignPermissions = async (roleId, data) => {
    try {
      const response = await roleApi.assignPermissions(roleId, data)
      return response
    } catch (error) {
      throw error
    }
  }

  const removePermissions = async (roleId, data) => {
    try {
      const response = await roleApi.removePermissions(roleId, data)
      return response
    } catch (error) {
      throw error
    }
  }

  return {
    roles,
    currentRole,
    loading,
    total,
    getRoles,
    getRole,
    createRole,
    updateRole,
    deleteRole,
    assignPermissions,
    removePermissions
  }
}) 