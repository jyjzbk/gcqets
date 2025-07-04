import request from '@/utils/request'

/**
 * 实验日历相关API
 */
export const experimentCalendarApi = {
  /**
   * 获取日历数据
   * @param {Object} params - 查询参数
   * @param {string} params.start - 开始日期
   * @param {string} params.end - 结束日期
   */
  getCalendarData(params) {
    return request({
      url: '/experiment-calendar/data',
      method: 'get',
      params
    })
  },

  /**
   * 获取逾期预警
   * @param {Object} params - 查询参数
   * @param {number} params.days - 预警天数
   */
  getOverdueAlerts(params) {
    return request({
      url: '/experiment-calendar/overdue-alerts',
      method: 'get',
      params
    })
  },

  /**
   * 获取实验详情
   * @param {number} id - 实验计划ID
   */
  getExperimentDetails(id) {
    return request({
      url: `/experiment-calendar/experiment/${id}`,
      method: 'get'
    })
  },

  /**
   * 检查日程冲突
   * @param {Object} data - 检查数据
   * @param {string} data.date - 日期
   * @param {number} data.teacher_id - 教师ID
   * @param {number} data.exclude_id - 排除的计划ID（编辑时使用）
   */
  checkConflicts(data) {
    return request({
      url: '/experiment-calendar/check-conflicts',
      method: 'post',
      data
    })
  }
}

export default experimentCalendarApi
