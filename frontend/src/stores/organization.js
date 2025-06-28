import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { organizationApi } from '../api/organization'

export const useOrganizationStore = defineStore('organization', () => {
  const organizations = ref([])
  const organizationTree = ref([])
  const currentOrganization = ref(null)
  const loading = ref(false)
  const total = ref(0)

  const getOrganizations = async (params = {}) => {
    loading.value = true
    try {
      const response = await organizationApi.getList(params)

      // 处理不同的响应结构
      if (response.data.data && Array.isArray(response.data.data)) {
        // 新的树形结构API响应
        organizations.value = response.data.data
        total.value = response.data.total || response.data.data.length
      } else if (response.data.data && response.data.data.data) {
        // 原来的分页API响应
        organizations.value = response.data.data.data
        total.value = response.data.data.total
      } else {
        organizations.value = []
        total.value = 0
      }

      return response
    } catch (error) {
      console.error('获取组织列表错误:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  const getOrganizationTree = async (params = {}) => {
    try {
      const response = await organizationApi.getTree(params)
      console.log('Organization tree API response:', response.data)
      organizationTree.value = response.data.data
      console.log('Organization tree value set to:', organizationTree.value)
      return response
    } catch (error) {
      console.error('Get organization tree error:', error)
      throw error
    }
  }

  const getParentOptions = async (params = {}) => {
    try {
      const response = await organizationApi.getParentOptions(params)
      return response.data
    } catch (error) {
      throw error
    }
  }

  const getChildren = async (parentId) => {
    try {
      const response = await organizationApi.getChildren(parentId)
      return response.data
    } catch (error) {
      throw error
    }
  }

  const getOrganization = async (id) => {
    try {
      const response = await organizationApi.getDetail(id)
      currentOrganization.value = response.data.data
      return response
    } catch (error) {
      throw error
    }
  }

  const createOrganization = async (data) => {
    try {
      const response = await organizationApi.create(data)
      await getOrganizations()
      return response
    } catch (error) {
      throw error
    }
  }

  const updateOrganization = async (id, data) => {
    try {
      const response = await organizationApi.update(id, data)
      await getOrganizations()
      return response
    } catch (error) {
      throw error
    }
  }

  const deleteOrganization = async (id) => {
    try {
      const response = await organizationApi.delete(id)
      await getOrganizations()
      return response
    } catch (error) {
      throw error
    }
  }

  const moveOrganization = async (id, data) => {
    try {
      const response = await organizationApi.move(id, data)
      await getOrganizationTree()
      return response
    } catch (error) {
      throw error
    }
  }

  return {
    organizations,
    organizationTree,
    currentOrganization,
    loading,
    total,
    getOrganizations,
    getOrganizationTree,
    getParentOptions,
    getChildren,
    getOrganization,
    createOrganization,
    updateOrganization,
    deleteOrganization,
    moveOrganization
  }
})