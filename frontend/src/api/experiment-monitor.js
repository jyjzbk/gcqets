import request from '@/utils/request'

/**
 * 实验监控相关API
 */
export const experimentMonitorApi = {
  /**
   * 获取监控看板数据
   * @param {Object} params - 查询参数
   * @param {string} params.time_range - 时间范围 (week, month, quarter, year)
   */
  getDashboard(params) {
    return request({
      url: '/experiment-monitor/dashboard',
      method: 'get',
      params
    })
  },

  /**
   * 获取进度统计
   * @param {Object} params - 查询参数
   * @param {string} params.time_range - 时间范围
   */
  getProgressStats(params) {
    return request({
      url: '/experiment-monitor/progress-stats',
      method: 'get',
      params
    })
  },

  /**
   * 获取异常分析
   * @param {Object} params - 查询参数
   * @param {string} params.time_range - 时间范围
   */
  getAnomalyAnalysis(params) {
    return request({
      url: '/experiment-monitor/anomaly-analysis',
      method: 'get',
      params
    })
  },

  /**
   * 获取实时统计
   */
  getRealTimeStats() {
    return request({
      url: '/experiment-monitor/realtime-stats',
      method: 'get'
    })
  }
}

export default experimentMonitorApi
