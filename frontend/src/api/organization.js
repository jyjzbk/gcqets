import request from './request'

export const organizationApi = {
  getList(params) {
    return request.get('/organizations', { params })
  },
  
  getTree(params) {
    return request.get('/organizations/tree', { params })
  },

  getParentOptions(params) {
    return request.get('/organizations/parent-options', { params })
  },

  getChildren(parentId) {
    return request.get(`/organizations/${parentId}/children`)
  },
  
  getDetail(id) {
    return request.get(`/organizations/${id}`)
  },
  
  create(data) {
    return request.post('/organizations', data)
  },
  
  update(id, data) {
    return request.put(`/organizations/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/organizations/${id}`)
  },
  
  move(id, data) {
    return request.post(`/organizations/${id}/move`, data)
  },
  
  getUsers(id, params) {
    return request.get(`/organizations/${id}/users`, { params })
  },
  
  getChildren(id) {
    return request.get(`/organizations/${id}/children`)
  },
  
  getAncestors(id) {
    return request.get(`/organizations/${id}/ancestors`)
  },
  
  getDescendants(id) {
    return request.get(`/organizations/${id}/descendants`)
  },

  // 导出学校数据
  exportSchools(params = {}) {
    return request.get('/organizations/schools/export', {
      params,
      responseType: 'blob'
    })
  },

  // 批量删除学校
  batchDelete(ids) {
    return request.delete('/organizations/schools/batch', {
      data: { ids }
    })
  }
}