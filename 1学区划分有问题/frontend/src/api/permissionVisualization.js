import request from './request'

export const permissionVisualizationApi = {
  // 获取权限继承关系树
  getInheritanceTree(params) {
    return request.get('/permission-visualization/inheritance-tree', { params })
  },

  // 获取权限继承路径
  getInheritancePath(params) {
    return request.get('/permission-visualization/inheritance-path', { params })
  },

  // 检测权限冲突
  detectConflicts(data) {
    return request.post('/permission-visualization/detect-conflicts', data)
  },

  // 获取权限矩阵
  getPermissionMatrix(params) {
    return request.get('/permission-visualization/permission-matrix', { params })
  },

  // 计算有效权限
  calculateEffectivePermissions(params) {
    return request.get('/permission-visualization/effective-permissions', { params })
  }
}

// 权限审计API
export const permissionAuditApi = {
  // 获取审计日志
  getLogs(params) {
    return request.get('/permission-audit/logs', { params })
  },

  // 获取审计日志详情
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

  // 解决冲突
  resolveConflict(id, data) {
    return request.post(`/permission-audit/conflicts/${id}/resolve`, data)
  },

  // 忽略冲突
  ignoreConflict(id, data) {
    return request.post(`/permission-audit/conflicts/${id}/ignore`, data)
  },

  // 导出审计日志
  export(params) {
    return request.post('/permission-audit/export', params)
  }
}

// 权限模板API
export const permissionTemplateApi = {
  // 获取模板列表
  getList(params) {
    return request.get('/permission-templates', { params })
  },

  // 获取推荐模板
  getRecommended(params) {
    return request.get('/permission-templates/recommended', { params })
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

  // 应用模板到角色
  applyToRole(id, data) {
    return request.post(`/permission-templates/${id}/apply-to-role`, data)
  },

  // 应用模板到用户
  applyToUser(id, data) {
    return request.post(`/permission-templates/${id}/apply-to-user`, data)
  },

  // 复制模板
  duplicate(id, data) {
    return request.post(`/permission-templates/${id}/duplicate`, data)
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
