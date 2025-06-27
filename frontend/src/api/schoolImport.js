import request from './request'

// 学校导入相关API
export const schoolImportApi = {
  /**
   * 导入学校信息
   * @param {FormData} formData - 包含文件和导入参数的表单数据
   */
  import(formData) {
    return request.post('/organizations/schools/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      timeout: 300000 // 5分钟超时
    })
  },

  /**
   * 下载导入模板
   * @param {string} format - 模板格式 (excel|csv)
   */
  downloadTemplate(format = 'excel') {
    return request.get('/organizations/schools/import/template', {
      params: { format },
      responseType: 'blob'
    })
  },

  /**
   * 获取导入历史
   * @param {Object} params - 查询参数
   */
  getHistory(params = {}) {
    return request.get('/organizations/schools/import/history', { params })
  },

  /**
   * 获取导入详情
   * @param {number} importLogId - 导入日志ID
   */
  getDetail(importLogId) {
    return request.get(`/organizations/schools/import/history/${importLogId}`)
  },

  /**
   * 获取导入统计
   * @param {Object} params - 查询参数
   */
  getStats(params = {}) {
    return request.get('/organizations/schools/import/stats', { params })
  },

  /**
   * 验证导入文件
   * @param {FormData} formData - 包含文件和验证参数的表单数据
   */
  validateFile(formData) {
    const validateFormData = new FormData()
    for (let [key, value] of formData.entries()) {
      validateFormData.append(key, value)
    }
    validateFormData.set('validate_only', '1')

    return this.import(validateFormData)
  },

  /**
   * 预览导入数据
   * @param {FormData} formData - 包含文件和预览参数的表单数据
   */
  previewImport(formData) {
    return request.post('/organizations/schools/import/preview', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      timeout: 60000 // 1分钟超时
    })
  },

  /**
   * 分析导入文件
   * @param {FormData} formData - 包含文件和分析参数的表单数据
   */
  analyzeFile(formData) {
    return request.post('/organizations/schools/import/analyze', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      timeout: 60000 // 1分钟超时
    })
  },

  /**
   * 生成导入审计报告
   * @param {number} importLogId - 导入日志ID
   */
  generateAuditReport(importLogId) {
    return request.get(`/organizations/schools/import/history/${importLogId}/audit`)
  },

  /**
   * 回滚导入操作
   * @param {number} importLogId - 导入日志ID
   */
  rollbackImport(importLogId) {
    return request.post(`/organizations/schools/import/history/${importLogId}/rollback`)
  },

  /**
   * 比较两次导入
   * @param {number} importId1 - 第一次导入ID
   * @param {number} importId2 - 第二次导入ID
   */
  compareImports(importId1, importId2) {
    return request.post('/organizations/schools/import/compare', {
      import_id_1: importId1,
      import_id_2: importId2
    })
  },

  /**
   * 获取导入时间线
   * @param {Object} params - 查询参数
   */
  getTimeline(params = {}) {
    return request.get('/organizations/schools/import/timeline', { params })
  }
}

export default schoolImportApi
