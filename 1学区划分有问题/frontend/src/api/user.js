import request from './request'

export const userApi = {
  getList(params) {
    return request.get('/users', { params })
  },
  
  getDetail(id) {
    return request.get(`/users/${id}`)
  },
  
  create(data) {
    return request.post('/users', data)
  },
  
  update(id, data) {
    return request.put(`/users/${id}`, data)
  },
  
  delete(id) {
    return request.delete(`/users/${id}`)
  },
  
  assignRole(userId, data) {
    return request.post(`/users/${userId}/assign-role`, data)
  },
  
  removeRole(userId, data) {
    return request.delete(`/users/${userId}/remove-role`, data)
  },
  
  givePermission(userId, data) {
    return request.post(`/users/${userId}/give-permission`, data)
  },
  
  revokePermission(userId, data) {
    return request.delete(`/users/${userId}/revoke-permission`, data)
  },
  
  getPermissions(userId, params) {
    return request.get(`/users/${userId}/permissions`, { params })
  },
  
  getRoles(userId, params) {
    return request.get(`/users/${userId}/roles`, { params })
  },
  
  changePassword(userId, data) {
    return request.post(`/users/${userId}/change-password`, data)
  },
  
  resetPassword(userId, data) {
    return request.post(`/users/${userId}/reset-password`, data)
  }
} 