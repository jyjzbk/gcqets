import request from './request'

// 实验记录审核API
export const experimentReviewApi = {
  // 获取待审核记录列表
  getPendingRecords(params) {
    return request.get('/experiment-review/pending', { params })
  },

  // 审核通过
  approve(id, data) {
    return request.post(`/experiment-review/${id}/approve`, data)
  },

  // 审核拒绝
  reject(id, data) {
    return request.post(`/experiment-review/${id}/reject`, data)
  },

  // 要求修改
  requestRevision(id, data) {
    return request.post(`/experiment-review/${id}/revision`, data)
  },

  // 强制完成
  forceComplete(id, data) {
    return request.post(`/experiment-review/${id}/force-complete`, data)
  },

  // 批量审核
  batchReview(data) {
    return request.post('/experiment-review/batch', data)
  },

  // AI照片合规性检查
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

  // 获取审核人排行
  getReviewerRanking(params) {
    return request.get('/experiment-review/ranking', { params })
  }
}

// 审核类型选项
export const reviewTypeOptions = [
  { value: 'submit', label: '提交审核', type: 'info' },
  { value: 'approve', label: '审核通过', type: 'success' },
  { value: 'reject', label: '审核拒绝', type: 'danger' },
  { value: 'revision_request', label: '要求修改', type: 'warning' },
  { value: 'force_complete', label: '强制完成', type: 'primary' },
  { value: 'batch_approve', label: '批量通过', type: 'success' },
  { value: 'batch_reject', label: '批量拒绝', type: 'danger' },
  { value: 'ai_check', label: 'AI检查', type: 'info' },
  { value: 'manual_check', label: '人工检查', type: 'primary' }
]

// 审核分类选项
export const reviewCategoryOptions = [
  { value: 'format', label: '格式问题', icon: 'Document', color: '#409EFF' },
  { value: 'content', label: '内容问题', icon: 'EditPen', color: '#E6A23C' },
  { value: 'photo', label: '照片问题', icon: 'Picture', color: '#F56C6C' },
  { value: 'data', label: '数据问题', icon: 'DataAnalysis', color: '#909399' },
  { value: 'safety', label: '安全问题', icon: 'Warning', color: '#F56C6C' },
  { value: 'completeness', label: '完整性问题', icon: 'List', color: '#67C23A' },
  { value: 'other', label: '其他问题', icon: 'More', color: '#909399' }
]

// 审核评分等级
export const reviewGradeOptions = [
  { value: 10, label: '优秀', color: '#67C23A' },
  { value: 9, label: '优秀', color: '#67C23A' },
  { value: 8, label: '良好', color: '#95D475' },
  { value: 7, label: '中等', color: '#E6A23C' },
  { value: 6, label: '及格', color: '#F78989' },
  { value: 5, label: '不及格', color: '#F56C6C' },
  { value: 4, label: '不及格', color: '#F56C6C' },
  { value: 3, label: '不及格', color: '#F56C6C' },
  { value: 2, label: '不及格', color: '#F56C6C' },
  { value: 1, label: '不及格', color: '#F56C6C' }
]

// 获取审核类型标签类型
export const getReviewTypeType = (type) => {
  const option = reviewTypeOptions.find(item => item.value === type)
  return option ? option.type : 'info'
}

// 获取审核类型标签文本
export const getReviewTypeLabel = (type) => {
  const option = reviewTypeOptions.find(item => item.value === type)
  return option ? option.label : type
}

// 获取审核分类标签文本
export const getReviewCategoryLabel = (category) => {
  const option = reviewCategoryOptions.find(item => item.value === category)
  return option ? option.label : category
}

// 获取审核分类图标
export const getReviewCategoryIcon = (category) => {
  const option = reviewCategoryOptions.find(item => item.value === category)
  return option ? option.icon : 'More'
}

// 获取审核分类颜色
export const getReviewCategoryColor = (category) => {
  const option = reviewCategoryOptions.find(item => item.value === category)
  return option ? option.color : '#909399'
}

// 获取审核评分等级
export const getReviewGrade = (score) => {
  if (!score) return { label: '未评分', color: '#909399' }
  
  if (score >= 9) return { label: '优秀', color: '#67C23A' }
  if (score >= 8) return { label: '良好', color: '#95D475' }
  if (score >= 7) return { label: '中等', color: '#E6A23C' }
  if (score >= 6) return { label: '及格', color: '#F78989' }
  return { label: '不及格', color: '#F56C6C' }
}

// 获取审核评分颜色
export const getReviewScoreColor = (score) => {
  const grade = getReviewGrade(score)
  return grade.color
}

// 格式化审核时长
export const formatReviewDuration = (minutes) => {
  if (!minutes) return '未记录'
  
  if (minutes < 60) {
    return `${minutes}分钟`
  }
  
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  
  if (mins === 0) {
    return `${hours}小时`
  }
  
  return `${hours}小时${mins}分钟`
}

// 计算审核效率等级
export const getReviewEfficiency = (duration, score) => {
  if (!duration || !score) return { level: '未知', color: '#909399' }
  
  // 根据时长和评分计算效率
  // 时长越短、评分越高，效率越高
  const efficiency = (score / 10) * (120 / Math.max(duration, 1)) // 基准120分钟
  
  if (efficiency >= 1.5) return { level: '高效', color: '#67C23A' }
  if (efficiency >= 1.0) return { level: '正常', color: '#E6A23C' }
  if (efficiency >= 0.5) return { level: '较慢', color: '#F78989' }
  return { level: '低效', color: '#F56C6C' }
}

// 验证审核数据
export const validateReviewData = (action, data) => {
  const errors = []
  
  // 通用验证
  if (!data.review_notes || data.review_notes.trim().length === 0) {
    errors.push('请填写审核意见')
  }
  
  if (data.review_notes && data.review_notes.length > 1000) {
    errors.push('审核意见不能超过1000个字符')
  }
  
  // 特定操作验证
  switch (action) {
    case 'reject':
    case 'revision_request':
      if (!data.review_category) {
        errors.push('请选择问题分类')
      }
      break
      
    case 'force_complete':
      if (!data.force_reason || data.force_reason.trim().length === 0) {
        errors.push('请填写强制完成原因')
      }
      break
      
    case 'batch':
      if (!data.record_ids || data.record_ids.length === 0) {
        errors.push('请选择要审核的记录')
      }
      
      if (!data.action || !['approve', 'reject'].includes(data.action)) {
        errors.push('请选择批量操作类型')
      }
      break
  }
  
  // 评分验证
  if (data.review_score !== undefined && data.review_score !== null) {
    if (data.review_score < 1 || data.review_score > 10) {
      errors.push('评分应在1-10之间')
    }
  }
  
  return {
    valid: errors.length === 0,
    errors
  }
}

// 生成审核建议
export const generateReviewSuggestions = (record) => {
  const suggestions = []
  
  if (!record) return suggestions
  
  // 检查完成度
  if (record.completion_percentage < 80) {
    suggestions.push({
      type: 'warning',
      category: 'completeness',
      message: '记录完成度较低，建议要求补充完整信息'
    })
  }
  
  // 检查照片数量
  if (record.photo_count < 3) {
    suggestions.push({
      type: 'warning',
      category: 'photo',
      message: '照片数量较少，建议要求补充更多照片'
    })
  }
  
  // 检查器材确认
  if (!record.equipment_confirmed) {
    suggestions.push({
      type: 'info',
      category: 'completeness',
      message: '器材准备未确认，建议提醒确认'
    })
  }
  
  // 检查安全事件
  if (record.safety_incidents && record.safety_incidents.trim().length > 0) {
    suggestions.push({
      type: 'danger',
      category: 'safety',
      message: '记录了安全事件，需要重点关注安全问题'
    })
  }
  
  // 检查教学反思
  if (!record.teaching_reflection || record.teaching_reflection.trim().length < 50) {
    suggestions.push({
      type: 'info',
      category: 'content',
      message: '教学反思内容较少，建议要求详细填写'
    })
  }
  
  return suggestions
}

// 审核操作确认消息
export const getReviewConfirmMessage = (action, count = 1) => {
  const messages = {
    approve: count > 1 ? `确定要批量通过这 ${count} 条记录吗？` : '确定要通过这条记录吗？',
    reject: count > 1 ? `确定要批量拒绝这 ${count} 条记录吗？` : '确定要拒绝这条记录吗？',
    revision_request: '确定要要求修改这条记录吗？',
    force_complete: '确定要强制完成这条记录吗？此操作不可撤销！',
    ai_check: '确定要进行AI照片合规性检查吗？'
  }
  
  return messages[action] || '确定要执行此操作吗？'
}
