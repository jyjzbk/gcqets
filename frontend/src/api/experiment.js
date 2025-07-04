import request from './request'

// 实验目录管理API
export const experimentCatalogApi = {
  // 获取实验目录列表
  getList(params) {
    return request.get('/experiment-catalogs', { params })
  },

  // 获取实验目录详情
  getDetail(id) {
    return request.get(`/experiment-catalogs/${id}`)
  },

  // 创建实验目录
  create(data) {
    return request.post('/experiment-catalogs', data)
  },

  // 更新实验目录
  update(id, data) {
    return request.put(`/experiment-catalogs/${id}`, data)
  },

  // 删除实验目录
  delete(id) {
    return request.delete(`/experiment-catalogs/${id}`)
  },

  // 获取选项数据
  getOptions() {
    return request.get('/experiment-catalogs/options')
  },

  // 获取版本历史
  getVersions(catalogId, params) {
    return request.get(`/experiment-catalogs/${catalogId}/versions`, { params })
  },

  // 获取版本详情
  getVersionDetail(catalogId, versionId) {
    return request.get(`/experiment-catalogs/${catalogId}/versions/${versionId}`)
  },

  // 比较版本
  compareVersions(catalogId, data) {
    return request.post(`/experiment-catalogs/${catalogId}/versions/compare`, data)
  },

  // 回滚版本
  rollbackVersion(catalogId, versionId, data) {
    return request.post(`/experiment-catalogs/${catalogId}/versions/${versionId}/rollback`, data)
  },

  // 获取版本统计
  getVersionStatistics(catalogId) {
    return request.get(`/experiment-catalogs/${catalogId}/versions/statistics`)
  }
}

// 课程标准管理API
export const curriculumStandardApi = {
  // 获取课程标准列表
  getList(params) {
    return request.get('/curriculum-standards', { params })
  },

  // 获取课程标准详情
  getDetail(id) {
    return request.get(`/curriculum-standards/${id}`)
  },

  // 创建课程标准
  create(data) {
    return request.post('/curriculum-standards', data)
  },

  // 更新课程标准
  update(id, data) {
    return request.put(`/curriculum-standards/${id}`, data)
  },

  // 删除课程标准
  delete(id) {
    return request.delete(`/curriculum-standards/${id}`)
  },

  // 获取选项数据
  getOptions() {
    return request.get('/curriculum-standards/options')
  },

  // 获取有效的课程标准
  getValidStandards(params) {
    return request.get('/curriculum-standards/valid', { params })
  }
}

// 照片模板管理API
export const photoTemplateApi = {
  // 获取照片模板列表
  getList(params) {
    return request.get('/photo-templates', { params })
  },

  // 获取照片模板详情
  getDetail(id) {
    return request.get(`/photo-templates/${id}`)
  },

  // 创建照片模板
  create(data) {
    return request.post('/photo-templates', data)
  },

  // 更新照片模板
  update(id, data) {
    return request.put(`/photo-templates/${id}`, data)
  },

  // 删除照片模板
  delete(id) {
    return request.delete(`/photo-templates/${id}`)
  },

  // 获取选项数据
  getOptions() {
    return request.get('/photo-templates/options')
  },

  // 获取匹配的照片模板
  getMatchingTemplates(params) {
    return request.get('/photo-templates/matching', { params })
  }
}

// 实验计划API
export const experimentPlanApi = {
  // 获取计划列表
  getList(params) {
    return request.get('/experiment-plans', { params })
  },

  // 获取计划详情
  getDetail(id) {
    return request.get(`/experiment-plans/${id}`)
  },

  // 创建计划
  create(data) {
    return request.post('/experiment-plans', data)
  },

  // 更新计划
  update(id, data) {
    return request.put(`/experiment-plans/${id}`, data)
  },

  // 删除计划
  delete(id) {
    return request.delete(`/experiment-plans/${id}`)
  },

  // 提交审批
  submit(id) {
    return request.post(`/experiment-plans/${id}/submit`)
  },

  // 审批通过
  approve(id, data) {
    return request.post(`/experiment-plans/${id}/approve`, data)
  },

  // 审批拒绝
  reject(id, data) {
    return request.post(`/experiment-plans/${id}/reject`, data)
  }
}

// 实验记录API
export const experimentRecordApi = {
  // 获取记录列表
  getList(params) {
    return request.get('/experiment-records', { params })
  },

  // 获取记录详情
  getDetail(id) {
    return request.get(`/experiment-records/${id}`)
  },

  // 创建记录
  create(data) {
    return request.post('/experiment-records', data)
  },

  // 更新记录
  update(id, data) {
    return request.put(`/experiment-records/${id}`, data)
  },

  // 删除记录
  delete(id) {
    return request.delete(`/experiment-records/${id}`)
  },

  // 上传照片
  uploadPhoto(id, formData) {
    return request.post(`/experiment-records/${id}/photos`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  // 删除照片
  deletePhoto(recordId, photoId) {
    return request.delete(`/experiment-records/${recordId}/photos/${photoId}`)
  }
}

// 实验审核API
export const experimentReviewApi = {
  // 获取待审核记录
  getPendingRecords(params) {
    return request.get('/experiment-review/pending', { params })
  },

  // 批量审核
  batchReview(data) {
    return request.post('/experiment-review/batch', data)
  },

  // 审核通过
  approve(id, data) {
    return request.post(`/experiment-review/${id}/approve`, data)
  },

  // 审核拒绝
  reject(id, data) {
    return request.post(`/experiment-review/${id}/reject`, data)
  },

  // 请求修订
  requestRevision(id, data) {
    return request.post(`/experiment-review/${id}/revision`, data)
  },

  // 强制完成
  forceComplete(id, data) {
    return request.post(`/experiment-review/${id}/force-complete`, data)
  },

  // AI照片检查
  aiPhotoCheck(id) {
    return request.post(`/experiment-review/${id}/ai-check`)
  },

  // 获取审核日志
  getReviewLogs(id) {
    return request.get(`/experiment-review/${id}/logs`)
  },

  // 获取审核统计
  getStatistics(params) {
    return request.get('/experiment-review/statistics', { params })
  },

  // 获取审核趋势
  getTrend(params) {
    return request.get('/experiment-review/trend', { params })
  },

  // 获取审核员排名
  getReviewerRanking(params) {
    return request.get('/experiment-review/ranking', { params })
  }
}

// 实验日历API
export const experimentCalendarApi = {
  // 获取日历数据
  getCalendarData(params) {
    return request.get('/experiment-calendar/data', { params })
  },

  // 获取逾期预警
  getOverdueAlerts(params) {
    return request.get('/experiment-calendar/overdue-alerts', { params })
  },

  // 获取实验详情
  getExperimentDetails(id) {
    return request.get(`/experiment-calendar/experiment/${id}`)
  },

  // 检查日程冲突
  checkConflicts(data) {
    return request.post('/experiment-calendar/check-conflicts', data)
  }
}
