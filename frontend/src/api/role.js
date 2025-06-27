import request from './request'

export const roleApi = {
  getList(params) {
    return request.get('/roles', { params })
  },
  
  getDetail(id) {
    return request.get(`/roles/${id}`)
  },
  
  create(data) {
    return request.post('/roles', data)
  },
  
  update(id, data) {
    return request.put(`/roles/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/roles/${id}`)
  },
  
  assignPermissions(roleId, data) {
    return request.post(`/roles/${roleId}/assign-permissions`, data)
  },
  
  removePermissions(roleId, data) {
    return request.delete(`/roles/${roleId}/remove-permissions`, data)
  },
  
  getPermissions(roleId) {
    return request.get(`/roles/${roleId}/permissions`)
  },
  
  getUsers(roleId, params) {
    return request.get(`/roles/${roleId}/users`, { params })
  },
  
  getOptions(params) {
    return request.get('/roles/options', { params })
  }
} 