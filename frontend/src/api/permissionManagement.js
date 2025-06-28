import request from './request'

/**
 * 权限管理API
 */
export const permissionManagementApi = {
  /**
   * 分配权限
   */
  grantPermission(data) {
    return request.post('/permission-management/grant', data)
  },

  /**
   * 撤销权限
   */
  revokePermission(data) {
    return request.post('/permission-management/revoke', data)
  },

  /**
   * 批量分配权限
   */
  batchGrantPermissions(data) {
    return request.post('/permission-management/batch-grant', data)
  },

  /**
   * 批量撤销权限
   */
  batchRevokePermissions(data) {
    return request.post('/permission-management/batch-revoke', data)
  },

  /**
   * 更新权限
   */
  updatePermission(data) {
    return request.post('/permission-management/update', data)
  },

  /**
   * 获取权限继承关系树
   */
  getInheritanceTree(params = {}) {
    return request.get('/permission-management/test-tree', { params })
  },

  /**
   * 获取权限矩阵
   */
  getPermissionMatrix(params = {}) {
    return request.get('/permission-management/test-matrix', { params })
  },

  /**
   * 获取权限审计日志
   */
  getAuditLogs(params = {}) {
    return request.get('/permission-management/test-audit', { params })
  },

  /**
   * 获取权限统计
   */
  getPermissionStats(params = {}) {
    return request.get('/permission-management/test-stats', { params })
  },

  /**
   * 检测权限冲突
   */
  detectConflicts(params = {}) {
    return request.get('/permission-management/detect-conflicts', { params })
  },

  /**
   * 重新计算权限继承
   */
  recalculateInheritance(data) {
    return request.post('/permission-management/recalculate-inheritance', data)
  }
}

/**
 * 权限可视化管理相关的辅助函数
 */
export const permissionUtils = {
  /**
   * 格式化权限来源
   */
  formatPermissionSource(source) {
    const sourceMap = {
      direct: '直接分配',
      role: '角色权限',
      inherited: '继承权限',
      template: '模板权限'
    }
    return sourceMap[source] || source
  },

  /**
   * 获取权限来源的标签类型
   */
  getSourceTagType(source) {
    const typeMap = {
      direct: 'primary',
      role: 'success',
      inherited: 'info',
      template: 'warning'
    }
    return typeMap[source] || 'info'
  },

  /**
   * 格式化组织类型
   */
  formatOrganizationType(type) {
    const typeMap = {
      province: '省级',
      city: '市级',
      district: '区县级',
      zone: '学区级',
      school: '学校级'
    }
    return typeMap[type] || type
  },

  /**
   * 获取组织层级颜色
   */
  getOrganizationLevelColor(level) {
    const colors = ['#f56c6c', '#e6a23c', '#409eff', '#67c23a', '#909399']
    return colors[level - 1] || '#909399'
  },

  /**
   * 格式化权限操作类型
   */
  formatActionType(action) {
    const actionMap = {
      grant: '分配权限',
      revoke: '撤销权限',
      update: '更新权限',
      inherited: '继承权限',
      expired: '权限过期'
    }
    return actionMap[action] || action
  },

  /**
   * 获取操作类型的标签类型
   */
  getActionTagType(action) {
    const typeMap = {
      grant: 'success',
      revoke: 'danger',
      update: 'warning',
      inherited: 'info',
      expired: 'info'
    }
    return typeMap[action] || 'info'
  },

  /**
   * 构建权限矩阵数据
   */
  buildPermissionMatrix(users, permissions, userPermissions) {
    const matrix = []
    
    users.forEach(user => {
      const row = {
        user_id: user.id,
        user_name: user.real_name || user.username,
        organization: user.organization_name || '',
        permissions: {}
      }
      
      permissions.forEach(permission => {
        const userPerm = userPermissions.find(up => 
          up.user_id === user.id && up.permission_id === permission.id
        )
        
        row.permissions[permission.id] = {
          has_permission: !!userPerm,
          source: userPerm?.source || 'none',
          expires_at: userPerm?.expires_at || null
        }
      })
      
      matrix.push(row)
    })
    
    return matrix
  },

  /**
   * 过滤权限数据
   */
  filterPermissions(permissions, filters) {
    let filtered = [...permissions]
    
    if (filters.source && filters.source !== 'all') {
      filtered = filtered.filter(p => p.source === filters.source)
    }
    
    if (filters.status && filters.status !== 'all') {
      const now = new Date()
      if (filters.status === 'active') {
        filtered = filtered.filter(p => !p.expires_at || new Date(p.expires_at) > now)
      } else if (filters.status === 'expired') {
        filtered = filtered.filter(p => p.expires_at && new Date(p.expires_at) <= now)
      }
    }
    
    if (filters.organization_id) {
      filtered = filtered.filter(p => p.organization_id === filters.organization_id)
    }
    
    return filtered
  },

  /**
   * 生成权限统计图表数据
   */
  generateChartData(stats, type = 'source') {
    if (type === 'source') {
      return {
        labels: Object.keys(stats.permission_by_source || {}),
        datasets: [{
          data: Object.values(stats.permission_by_source || {}),
          backgroundColor: ['#409eff', '#67c23a', '#e6a23c', '#f56c6c']
        }]
      }
    } else if (type === 'organization') {
      return {
        labels: Object.keys(stats.permission_by_organization || {}),
        datasets: [{
          data: Object.values(stats.permission_by_organization || {}),
          backgroundColor: ['#409eff', '#67c23a', '#e6a23c', '#f56c6c', '#909399']
        }]
      }
    }
    
    return { labels: [], datasets: [] }
  },

  /**
   * 验证权限操作数据
   */
  validatePermissionOperation(data) {
    const errors = []
    
    if (!data.subject_type) {
      errors.push('请选择操作对象类型')
    }
    
    if (!data.subject_id) {
      errors.push('请选择操作对象')
    }
    
    if (!data.permission_id && !data.permission_ids?.length) {
      errors.push('请选择权限')
    }
    
    if (data.expires_at && new Date(data.expires_at) <= new Date()) {
      errors.push('过期时间不能早于当前时间')
    }
    
    return errors
  }
}

export default permissionManagementApi
