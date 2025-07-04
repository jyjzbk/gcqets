import request from './request'

// 实验计划API
export const experimentPlanApi = {
  // 获取实验计划列表
  getList(params) {
    return request.get('/experiment-plans', { params })
  },

  // 获取实验计划详情
  getDetail(id) {
    return request.get(`/experiment-plans/${id}`)
  },

  // 创建实验计划
  create(data) {
    return request.post('/experiment-plans', data)
  },

  // 更新实验计划
  update(id, data) {
    return request.put(`/experiment-plans/${id}`, data)
  },

  // 删除实验计划
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
  },

  // 获取统计数据
  getStatistics(params) {
    return request.get('/experiment-plans/statistics', { params })
  },

  // 获取实验目录选项
  getCatalogOptions(params) {
    return request.get('/experiment-plans/catalog-options', { params })
  },

  // 获取课程标准选项
  getStandardOptions(params) {
    return request.get('/experiment-plans/standard-options', { params })
  }
}

// 实验计划状态选项
export const planStatusOptions = [
  { value: 'draft', label: '草稿', type: 'info' },
  { value: 'pending', label: '待审批', type: 'warning' },
  { value: 'approved', label: '已批准', type: 'success' },
  { value: 'rejected', label: '已拒绝', type: 'danger' },
  { value: 'executing', label: '执行中', type: 'primary' },
  { value: 'completed', label: '已完成', type: 'success' },
  { value: 'cancelled', label: '已取消', type: 'info' }
]

// 优先级选项
export const priorityOptions = [
  { value: 'low', label: '低', type: 'info' },
  { value: 'medium', label: '中', type: 'warning' },
  { value: 'high', label: '高', type: 'danger' }
]

// 学科选项
export const subjectOptions = [
  { value: 'physics', label: '物理' },
  { value: 'chemistry', label: '化学' },
  { value: 'biology', label: '生物' },
  { value: 'science', label: '科学' }
]

// 年级选项
export const gradeOptions = [
  { value: 'grade1', label: '一年级' },
  { value: 'grade2', label: '二年级' },
  { value: 'grade3', label: '三年级' },
  { value: 'grade4', label: '四年级' },
  { value: 'grade5', label: '五年级' },
  { value: 'grade6', label: '六年级' },
  { value: 'grade7', label: '七年级' },
  { value: 'grade8', label: '八年级' },
  { value: 'grade9', label: '九年级' }
]

// 获取状态标签类型
export const getStatusType = (status) => {
  const option = planStatusOptions.find(item => item.value === status)
  return option ? option.type : 'info'
}

// 获取状态标签文本
export const getStatusLabel = (status) => {
  const option = planStatusOptions.find(item => item.value === status)
  return option ? option.label : status
}

// 获取优先级标签类型
export const getPriorityType = (priority) => {
  const option = priorityOptions.find(item => item.value === priority)
  return option ? option.type : 'info'
}

// 获取优先级标签文本
export const getPriorityLabel = (priority) => {
  const option = priorityOptions.find(item => item.value === priority)
  return option ? option.label : priority
}

// 获取学科标签文本
export const getSubjectLabel = (subject) => {
  const option = subjectOptions.find(item => item.value === subject)
  return option ? option.label : subject
}

// 获取年级标签文本
export const getGradeLabel = (grade) => {
  const option = gradeOptions.find(item => item.value === grade)
  return option ? option.label : grade
}
