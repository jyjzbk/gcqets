import request from './request'

// 实验记录API
export const experimentRecordApi = {
  // 获取实验记录列表
  getList(params) {
    return request.get('/experiment-records', { params })
  },

  // 获取实验记录详情
  getDetail(id) {
    return request.get(`/experiment-records/${id}`)
  },

  // 创建实验记录
  create(data) {
    return request.post('/experiment-records', data)
  },

  // 更新实验记录
  update(id, data) {
    return request.put(`/experiment-records/${id}`, data)
  },

  // 删除实验记录
  delete(id) {
    return request.delete(`/experiment-records/${id}`)
  },

  // 提交审核
  submit(id) {
    return request.post(`/experiment-records/${id}/submit`)
  },

  // 确认器材准备
  confirmEquipment(id) {
    return request.post(`/experiment-records/${id}/confirm-equipment`)
  },

  // 验证记录数据
  validateData(id) {
    return request.get(`/experiment-records/${id}/validate`)
  },

  // 上传照片
  uploadPhoto(recordId, formData) {
    return request.post(`/experiment-records/${recordId}/photos`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },

  // 删除照片
  deletePhoto(recordId, photoId) {
    return request.delete(`/experiment-records/${recordId}/photos/${photoId}`)
  },

  // 获取统计数据
  getStatistics(params) {
    return request.get('/experiment-records/statistics', { params })
  },

  // 获取实验计划选项
  getPlanOptions(params) {
    return request.get('/experiment-records/plan-options', { params })
  }
}

// 完成状态选项
export const completionStatusOptions = [
  { value: 'not_started', label: '未开始', type: 'info' },
  { value: 'in_progress', label: '进行中', type: 'warning' },
  { value: 'partial', label: '部分完成', type: 'warning' },
  { value: 'completed', label: '已完成', type: 'success' },
  { value: 'cancelled', label: '已取消', type: 'info' }
]

// 审核状态选项
export const recordStatusOptions = [
  { value: 'draft', label: '草稿', type: 'info' },
  { value: 'submitted', label: '已提交', type: 'warning' },
  { value: 'under_review', label: '审核中', type: 'primary' },
  { value: 'approved', label: '已通过', type: 'success' },
  { value: 'rejected', label: '已拒绝', type: 'danger' },
  { value: 'revision_required', label: '需要修改', type: 'warning' }
]

// 照片类型选项
export const photoTypeOptions = [
  { value: 'preparation', label: '准备阶段', icon: 'Setting', color: '#409EFF' },
  { value: 'process', label: '过程记录', icon: 'VideoCamera', color: '#E6A23C' },
  { value: 'result', label: '结果展示', icon: 'Picture', color: '#67C23A' },
  { value: 'equipment', label: '器材准备', icon: 'Box', color: '#909399' },
  { value: 'safety', label: '安全记录', icon: 'Warning', color: '#F56C6C' }
]

// 合规状态选项
export const complianceStatusOptions = [
  { value: 'pending', label: '待检查', type: 'info' },
  { value: 'compliant', label: '合规', type: 'success' },
  { value: 'non_compliant', label: '不合规', type: 'danger' },
  { value: 'needs_review', label: '需要人工审核', type: 'warning' }
]

// 上传方式选项
export const uploadMethodOptions = [
  { value: 'web', label: '网页上传', icon: 'Monitor' },
  { value: 'mobile', label: '移动端上传', icon: 'Iphone' }
]

// 获取完成状态标签类型
export const getCompletionStatusType = (status) => {
  const option = completionStatusOptions.find(item => item.value === status)
  return option ? option.type : 'info'
}

// 获取完成状态标签文本
export const getCompletionStatusLabel = (status) => {
  const option = completionStatusOptions.find(item => item.value === status)
  return option ? option.label : status
}

// 获取审核状态标签类型
export const getRecordStatusType = (status) => {
  const option = recordStatusOptions.find(item => item.value === status)
  return option ? option.type : 'info'
}

// 获取审核状态标签文本
export const getRecordStatusLabel = (status) => {
  const option = recordStatusOptions.find(item => item.value === status)
  return option ? option.label : status
}

// 获取照片类型标签文本
export const getPhotoTypeLabel = (type) => {
  const option = photoTypeOptions.find(item => item.value === type)
  return option ? option.label : type
}

// 获取照片类型图标
export const getPhotoTypeIcon = (type) => {
  const option = photoTypeOptions.find(item => item.value === type)
  return option ? option.icon : 'Picture'
}

// 获取照片类型颜色
export const getPhotoTypeColor = (type) => {
  const option = photoTypeOptions.find(item => item.value === type)
  return option ? option.color : '#909399'
}

// 获取合规状态标签类型
export const getComplianceStatusType = (status) => {
  const option = complianceStatusOptions.find(item => item.value === status)
  return option ? option.type : 'info'
}

// 获取合规状态标签文本
export const getComplianceStatusLabel = (status) => {
  const option = complianceStatusOptions.find(item => item.value === status)
  return option ? option.label : status
}

// 格式化文件大小
export const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unitIndex = 0
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex++
  }
  
  return `${size.toFixed(2)} ${units[unitIndex]}`
}

// 格式化时长
export const formatDuration = (minutes) => {
  if (!minutes) return '0分钟'
  
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  
  if (hours > 0) {
    return `${hours}小时${mins}分钟`
  }
  return `${mins}分钟`
}

// 计算完成百分比颜色
export const getCompletionColor = (percentage) => {
  if (percentage >= 90) return '#67C23A'
  if (percentage >= 70) return '#E6A23C'
  if (percentage >= 50) return '#F56C6C'
  return '#909399'
}

// 验证照片文件
export const validatePhotoFile = (file) => {
  const errors = []
  
  // 检查文件类型
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
  if (!allowedTypes.includes(file.type)) {
    errors.push('只支持 JPEG、PNG、JPG、GIF 格式的图片')
  }
  
  // 检查文件大小（10MB）
  const maxSize = 10 * 1024 * 1024
  if (file.size > maxSize) {
    errors.push('图片大小不能超过 10MB')
  }
  
  return {
    valid: errors.length === 0,
    errors
  }
}

// 生成照片上传数据
export const createPhotoUploadData = (file, photoType, description = '', locationInfo = null) => {
  const formData = new FormData()
  formData.append('photo', file)
  formData.append('photo_type', photoType)
  formData.append('upload_method', 'web')
  
  if (description) {
    formData.append('description', description)
  }
  
  if (locationInfo) {
    formData.append('location_info[latitude]', locationInfo.latitude)
    formData.append('location_info[longitude]', locationInfo.longitude)
  }
  
  return formData
}

// 检查是否需要某种类型的照片
export const isPhotoTypeRequired = (photoType, existingPhotos = []) => {
  const requiredTypes = ['preparation', 'process', 'result']
  
  if (!requiredTypes.includes(photoType)) {
    return false
  }
  
  return !existingPhotos.some(photo => photo.photo_type === photoType)
}

// 获取照片类型完成情况
export const getPhotoTypeCompletion = (photos = []) => {
  const requiredTypes = ['preparation', 'process', 'result']
  const optionalTypes = ['equipment', 'safety']
  
  const completion = {
    required: {},
    optional: {},
    total: photos.length,
    requiredCount: 0,
    optionalCount: 0
  }
  
  // 统计必需类型
  requiredTypes.forEach(type => {
    const count = photos.filter(photo => photo.photo_type === type).length
    completion.required[type] = count
    completion.requiredCount += count
  })
  
  // 统计可选类型
  optionalTypes.forEach(type => {
    const count = photos.filter(photo => photo.photo_type === type).length
    completion.optional[type] = count
    completion.optionalCount += count
  })
  
  return completion
}
