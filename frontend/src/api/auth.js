import request from './request'

export const authApi = {
  login(credentials) {
    return request.post('/auth/login', credentials)
  },
  
  logout() {
    return request.post('/auth/logout')
  },
  
  getUser() {
    return request.get('/auth/user')
  },
  
  refresh() {
    return request.post('/auth/refresh')
  }
} 