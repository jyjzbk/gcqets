import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { userApi } from '../api/user'

export const useUserStore = defineStore('user', () => {
  const users = ref([])
  const currentUser = ref(null)
  const loading = ref(false)
  const total = ref(0)

  const getUsers = async (params = {}) => {
    loading.value = true
    try {
      const response = await userApi.getList(params)
      users.value = response.data.data.data
      total.value = response.data.data.total
      return response
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const getUser = async (id) => {
    try {
      const response = await userApi.getDetail(id)
      currentUser.value = response.data.data
      return response
    } catch (error) {
      throw error
    }
  }

  const createUser = async (data) => {
    try {
      const response = await userApi.create(data)
      await getUsers()
      return response
    } catch (error) {
      throw error
    }
  }

  const updateUser = async (id, data) => {
    try {
      const response = await userApi.update(id, data)
      await getUsers()
      return response
    } catch (error) {
      throw error
    }
  }

  const deleteUser = async (id) => {
    try {
      const response = await userApi.delete(id)
      await getUsers()
      return response
    } catch (error) {
      throw error
    }
  }

  const assignRole = async (userId, data) => {
    try {
      const response = await userApi.assignRole(userId, data)
      return response
    } catch (error) {
      throw error
    }
  }

  const removeRole = async (userId, data) => {
    try {
      const response = await userApi.removeRole(userId, data)
      return response
    } catch (error) {
      throw error
    }
  }

  return {
    users,
    currentUser,
    loading,
    total,
    getUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
    assignRole,
    removeRole
  }
}) 