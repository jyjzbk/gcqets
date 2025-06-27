import request from './request'

// 权限可视化API
export const permissionVisualizationApi = {
  // 获取权限继承树
  getInheritanceTree(params) {
    return request.get('/permission-visualization/inheritance-tree', { params })
  },

  // 获取权限继承路径
  getInheritancePath(params) {
    return request.get('/permission-visualization/inheritance-path', { params })
  },

  // 检测权限冲突
  detectConflicts(params) {
    return request.post('/permission-visualization/detect-conflicts', params)
  },

  // 获取权限矩阵
  getPermissionMatrix(params) {
    return request.get('/permission-visualization/permission-matrix', { params })
  },

  // 计算有效权限
  calculateEffectivePermissions(params) {
    return request.get('/permission-visualization/effective-permissions', { params })
  },

  // 获取总体统计
  getOverallStats(params) {
    return request.get('/permission-visualization/overall-stats', { params })
  },

  // 获取权限分布
  getPermissionDistribution(params) {
    return request.get('/permission-visualization/permission-distribution', { params })
  },

  // 获取权限热力图
  getPermissionHeatmap(params) {
    return request.get('/permission-visualization/permission-heatmap', { params })
  }
}

// 权限审计API
export const permissionAuditApi = {
  // 获取审计日志
  getLogs(params) {
    return request.get('/permission-audit/logs', { params })
  },

  // 获取日志详情
  getLogDetail(id) {
    return request.get(`/permission-audit/logs/${id}`)
  },

  // 获取用户统计
  getUserStats(params) {
    return request.get('/permission-audit/user-stats', { params })
  },

  // 获取组织统计
  getOrganizationStats(params) {
    return request.get('/permission-audit/organization-stats', { params })
  },

  // 获取权限热点
  getPermissionHotspots(params) {
    return request.get('/permission-audit/permission-hotspots', { params })
  },

  // 获取权限冲突
  getConflicts(params) {
    return request.get('/permission-audit/conflicts', { params })
  },

  // 获取冲突统计
  getConflictStats(params) {
    return request.get('/permission-audit/conflict-stats', { params })
  },

  // 获取操作趋势
  getOperationTrend(params) {
    return request.get('/permission-audit/operation-trend', { params })
  }
}

// 权限模板API
export const permissionTemplateApi = {
  // 获取模板列表
  getList(params) {
    return request.get('/permission-templates', { params })
  },

  // 获取模板详情
  getDetail(id) {
    return request.get(`/permission-templates/${id}`)
  },

  // 创建模板
  create(data) {
    return request.post('/permission-templates', data)
  },

  // 更新模板
  update(id, data) {
    return request.put(`/permission-templates/${id}`, data)
  },

  // 删除模板
  delete(id) {
    return request.delete(`/permission-templates/${id}`)
  },

  // 应用模板
  apply(data) {
    return request.post('/permission-templates/apply', data)
  },

  // 导出模板
  export(params) {
    return request.get('/permission-templates/export', { params })
  },

  // 导入模板
  import(formData) {
    return request.post('/permission-templates/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // 获取模板统计
  getStats(params) {
    return request.get('/permission-templates/stats', { params })
  }
}

// 权限管理API
export const permissionManagementApi = {
  // 分配权限
  grantPermission(data) {
    return request.post('/permission-management/grant', data)
  },

  // 撤销权限
  revokePermission(data) {
    return request.post('/permission-management/revoke', data)
  },

  // 批量分配权限
  batchGrantPermissions(data) {
    return request.post('/permission-management/batch-grant', data)
  },

  // 批量撤销权限
  batchRevokePermissions(data) {
    return request.post('/permission-management/batch-revoke', data)
  },

  // 更新权限
  updatePermission(data) {
    return request.post('/permission-management/update', data)
  }
}
