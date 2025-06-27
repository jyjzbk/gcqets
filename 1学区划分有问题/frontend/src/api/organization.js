import request from './request'

export const organizationApi = {
  getList(params) {
    return request.get('/organizations', { params })
  },
  
  getTree(params) {
    return request.get('/organizations/tree', { params })
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
  }
} 