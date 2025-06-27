import request from './request'

/**
 * 学区划分管理API
 */
export const districtApi = {
  /**
   * 获取学区划分概览
   * @param {Object} params - 查询参数
   * @param {string} params.region_id - 区域ID
   * @returns {Promise}
   */
  getOverview(params) {
    return request.get('/districts/overview', { params })
  },

  /**
   * 获取学校地理位置信息
   * @param {Object} params - 查询参数
   * @param {string} params.region_id - 区域ID
   * @returns {Promise}
   */
  getSchoolLocations(params) {
    return request.get('/districts/schools/locations', { params })
  },

  /**
   * 获取学区边界信息
   * @param {Object} params - 查询参数
   * @param {string} params.region_id - 区域ID
   * @returns {Promise}
   */
  getDistrictBoundaries(params) {
    return request.get('/districts/boundaries', { params })
  },

  /**
   * 自动划分学区
   * @param {Object} data - 划分参数
   * @param {string} data.region_id - 区域ID
   * @param {Object} data.criteria - 划分标准
   * @param {number} data.criteria.distance_weight - 距离权重
   * @param {number} data.criteria.scale_weight - 规模权重
   * @param {number} data.criteria.balance_weight - 均衡权重
   * @returns {Promise}
   */
  autoAssign(data) {
    return request.post('/districts/auto-assign', data)
  },

  /**
   * 手动调整学校学区归属
   * @param {Object} data - 调整参数
   * @param {string} data.school_id - 学校ID
   * @param {string} data.district_id - 学区ID
   * @param {string} data.reason - 调整原因
   * @returns {Promise}
   */
  manualAssign(data) {
    return request.post('/districts/manual-assign', data)
  },

  /**
   * 批量调整学校学区归属
   * @param {Object} data - 批量调整参数
   * @param {Array} data.assignments - 调整列表
   * @param {string} data.assignments[].school_id - 学校ID
   * @param {string} data.assignments[].district_id - 学区ID
   * @param {string} data.assignments[].reason - 调整原因
   * @returns {Promise}
   */
  batchAssign(data) {
    return request.post('/districts/batch-assign', data)
  },

  /**
   * 获取学区划分历史记录
   * @param {Object} params - 查询参数
   * @param {string} params.school_id - 学校ID
   * @param {string} params.district_id - 学区ID
   * @param {string} params.assignment_type - 划分类型 (auto/manual)
   * @param {string} params.date_from - 开始日期
   * @param {string} params.date_to - 结束日期
   * @param {number} params.page - 页码
   * @param {number} params.per_page - 每页数量
   * @returns {Promise}
   */
  getAssignmentHistory(params) {
    return request.get('/districts/assignment-history', { params })
  },

  /**
   * 获取学区统计信息
   * @param {Object} params - 查询参数
   * @param {string} params.region_id - 区域ID
   * @returns {Promise}
   */
  getStatistics(params) {
    return request.get('/districts/statistics', { params })
  },

  /**
   * 创建学区边界
   * @param {Object} data - 边界数据
   * @param {string} data.education_district_id - 学区ID
   * @param {string} data.name - 边界名称
   * @param {Array} data.boundary_points - 边界点坐标
   * @param {number} data.center_latitude - 中心纬度
   * @param {number} data.center_longitude - 中心经度
   * @param {string} data.description - 描述
   * @returns {Promise}
   */
  createBoundary(data) {
    return request.post('/districts/boundaries', data)
  },

  /**
   * 更新学区边界
   * @param {string} id - 边界ID
   * @param {Object} data - 更新数据
   * @returns {Promise}
   */
  updateBoundary(id, data) {
    return request.put(`/districts/boundaries/${id}`, data)
  },

  /**
   * 删除学区边界
   * @param {string} id - 边界ID
   * @returns {Promise}
   */
  deleteBoundary(id) {
    return request.delete(`/districts/boundaries/${id}`)
  },

  /**
   * 获取可分配的学校列表
   * @param {Object} params - 查询参数
   * @param {string} params.region_id - 区域ID
   * @param {boolean} params.without_district - 是否只获取未分配学区的学校
   * @returns {Promise}
   */
  getAvailableSchools(params) {
    return request.get('/districts/available-schools', { params })
  },

  /**
   * 获取学区内的学校列表
   * @param {string} districtId - 学区ID
   * @param {Object} params - 查询参数
   * @returns {Promise}
   */
  getDistrictSchools(districtId, params) {
    return request.get(`/districts/${districtId}/schools`, { params })
  },

  /**
   * 预览自动划分结果
   * @param {Object} data - 预览参数
   * @param {string} data.region_id - 区域ID
   * @param {Object} data.criteria - 划分标准
   * @returns {Promise}
   */
  previewAutoAssign(data) {
    return request.post('/districts/preview-auto-assign', data)
  },

  /**
   * 导出学区划分报告
   * @param {Object} params - 导出参数
   * @param {string} params.region_id - 区域ID
   * @param {string} params.format - 导出格式 (excel/pdf)
   * @returns {Promise}
   */
  exportReport(params) {
    return request.get('/districts/export-report', { 
      params,
      responseType: 'blob'
    })
  },

  /**
   * 获取学区负载均衡分析
   * @param {Object} params - 查询参数
   * @param {string} params.region_id - 区域ID
   * @returns {Promise}
   */
  getLoadBalanceAnalysis(params) {
    return request.get('/districts/load-balance-analysis', { params })
  },

  /**
   * 获取距离分析数据
   * @param {Object} params - 查询参数
   * @param {string} params.region_id - 区域ID
   * @returns {Promise}
   */
  getDistanceAnalysis(params) {
    return request.get('/districts/distance-analysis', { params })
  },

  /**
   * 撤销学区划分操作
   * @param {string} historyId - 历史记录ID
   * @param {Object} data - 撤销参数
   * @param {string} data.reason - 撤销原因
   * @returns {Promise}
   */
  revertAssignment(historyId, data) {
    return request.post(`/districts/assignment-history/${historyId}/revert`, data)
  }
}

export default districtApi
