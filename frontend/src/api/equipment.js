import request from './request'

// 设备分类API
export const equipmentCategoryApi = {
  // 获取设备分类列表
  getList(params) {
    return request.get('/equipment-categories', { params })
  },

  // 获取设备分类树
  getTree(params) {
    return request.get('/equipment-categories/tree', { params })
  },

  // 获取设备分类详情
  getDetail(id) {
    return request.get(`/equipment-categories/${id}`)
  },

  // 创建设备分类
  create(data) {
    return request.post('/equipment-categories', data)
  },

  // 更新设备分类
  update(id, data) {
    return request.put(`/equipment-categories/${id}`, data)
  },

  // 删除设备分类
  delete(id) {
    return request.delete(`/equipment-categories/${id}`)
  }
}

// 设备API
export const equipmentApi = {
  // 获取设备列表
  getList(params) {
    return request.get('/equipment', { params })
  },

  // 获取设备详情
  getDetail(id) {
    return request.get(`/equipment/${id}`)
  },

  // 创建设备
  create(data) {
    return request.post('/equipment', data)
  },

  // 更新设备
  update(id, data) {
    return request.put(`/equipment/${id}`, data)
  },

  // 删除设备
  delete(id) {
    return request.delete(`/equipment/${id}`)
  },

  // 获取设备统计信息
  getStatistics(params) {
    return request.get('/equipment/statistics', { params })
  }
}

// 设备借用API
export const equipmentBorrowingApi = {
  // 获取借用记录列表
  getList(params) {
    return request.get('/equipment-borrowings', { params })
  },

  // 获取借用记录详情
  getDetail(id) {
    return request.get(`/equipment-borrowings/${id}`)
  },

  // 创建借用申请
  create(data) {
    return request.post('/equipment-borrowings', data)
  },

  // 审批借用申请
  approve(id, data) {
    return request.post(`/equipment-borrowings/${id}/approve`, data)
  },

  // 借出设备
  borrow(id, data) {
    return request.post(`/equipment-borrowings/${id}/borrow`, data)
  },

  // 归还设备
  returnEquipment(id, data) {
    return request.post(`/equipment-borrowings/${id}/return`, data)
  },

  // 取消借用申请
  cancel(id, data) {
    return request.post(`/equipment-borrowings/${id}/cancel`, data)
  },

  // 获取借用统计信息
  getStatistics(params) {
    return request.get('/equipment-borrowings/statistics', { params })
  }
}

// 材料分类API
export const materialCategoryApi = {
  // 获取材料分类列表
  getList(params) {
    return request.get('/material-categories', { params })
  },

  // 获取材料分类树
  getTree(params) {
    return request.get('/material-categories/tree', { params })
  },

  // 获取材料分类详情
  getDetail(id) {
    return request.get(`/material-categories/${id}`)
  },

  // 创建材料分类
  create(data) {
    return request.post('/material-categories', data)
  },

  // 更新材料分类
  update(id, data) {
    return request.put(`/material-categories/${id}`, data)
  },

  // 删除材料分类
  delete(id) {
    return request.delete(`/material-categories/${id}`)
  }
}

// 材料API
export const materialApi = {
  // 获取材料列表
  getList(params) {
    return request.get('/materials', { params })
  },

  // 获取材料详情
  getDetail(id) {
    return request.get(`/materials/${id}`)
  },

  // 创建材料
  create(data) {
    return request.post('/materials', data)
  },

  // 更新材料
  update(id, data) {
    return request.put(`/materials/${id}`, data)
  },

  // 删除材料
  delete(id) {
    return request.delete(`/materials/${id}`)
  },

  // 库存调整
  adjustStock(id, data) {
    return request.post(`/materials/${id}/adjust-stock`, data)
  },

  // 获取材料统计信息
  getStatistics(params) {
    return request.get('/materials/statistics', { params })
  }
}

// 材料使用记录API
export const materialUsageApi = {
  // 获取使用记录列表
  getList(params) {
    return request.get('/material-usages', { params })
  },

  // 获取使用记录详情
  getDetail(id) {
    return request.get(`/material-usages/${id}`)
  },

  // 创建使用记录
  create(data) {
    return request.post('/material-usages', data)
  },

  // 更新使用记录
  update(id, data) {
    return request.put(`/material-usages/${id}`, data)
  },

  // 删除使用记录
  delete(id) {
    return request.delete(`/material-usages/${id}`)
  },

  // 获取使用统计信息
  getStatistics(params) {
    return request.get('/material-usages/statistics', { params })
  }
}
