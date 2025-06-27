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
      organizations.value = response.data.data.data
      total.value = response.data.data.total
      return response
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  const getOrganizationTree = async (params = {}) => {
    try {
      const response = await organizationApi.getTree(params)
      organizationTree.value = response.data.data
      return response
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
    getOrganization,
    createOrganization,
    updateOrganization,
    deleteOrganization,
    moveOrganization
  }
}) 