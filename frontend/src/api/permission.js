import request from './request'

export const permissionApi = {
  getList(params) {
    return request.get('/permissions', { params })
  },
  
  getTree(params) {
    return request.get('/permissions/tree', { params })
  },
  
  getMenu() {
    return request.get('/permissions/menu')
  },
  
  getDetail(id) {
    return request.get(`/permissions/${id}`)
  },
  
  create(data) {
    return request.post('/permissions', data)
  },
  
  update(id, data) {
    return request.put(`/permissions/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/permissions/${id}`)
  },
  
  getGroups() {
    return request.get('/permissions/groups')
  },
  
  getModules() {
    return request.get('/permissions/modules')
  }
} 