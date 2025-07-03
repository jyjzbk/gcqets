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
