<template>
  <div class="alert-list">
    <div v-if="alerts.length === 0" class="no-alerts">
      <el-empty :description="getEmptyDescription()" />
    </div>
    
    <div v-else class="alerts-container">
      <div 
        v-for="alert in alerts" 
        :key="alert.id"
        class="alert-item"
        :class="getAlertClass(alert)"
      >
        <div class="alert-header">
          <div class="alert-title">
            <h4>{{ alert.name }}</h4>
            <el-tag size="small" :type="getUrgencyType(alert.urgencyLevel)">
              {{ getUrgencyLabel(alert.urgencyLevel) }}
            </el-tag>
          </div>
          <div class="alert-actions">
            <el-button 
              type="primary" 
              size="small" 
              @click="handleView(alert.id)"
            >
              查看详情
            </el-button>
          </div>
        </div>
        
        <div class="alert-content">
          <el-row :gutter="20">
            <el-col :span="8">
              <div class="info-item">
                <span class="label">计划编码:</span>
                <span class="value">{{ alert.code }}</span>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="info-item">
                <span class="label">计划日期:</span>
                <span class="value">{{ formatDate(alert.plannedDate) }}</span>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="info-item">
                <span class="label">距离天数:</span>
                <span class="value" :class="getDaysClass(alert.daysUntil)">
                  {{ getDaysText(alert.daysUntil) }}
                </span>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="info-item">
                <span class="label">任课教师:</span>
                <span class="value">{{ alert.teacher }}</span>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="info-item">
                <span class="label">实验目录:</span>
                <span class="value">{{ alert.catalog || '未指定' }}</span>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="info-item">
                <span class="label">班级:</span>
                <span class="value">{{ alert.className || '未指定' }}</span>
              </div>
            </el-col>
          </el-row>
        </div>
        
        <div class="alert-footer">
          <div class="alert-time">
            <el-icon><Clock /></el-icon>
            <span>{{ getTimeDescription(alert.daysUntil) }}</span>
          </div>
          <div class="alert-progress">
            <el-progress 
              :percentage="getUrgencyPercentage(alert.urgencyLevel)" 
              :color="getProgressColor(alert.urgencyLevel)"
              :show-text="false"
              :stroke-width="4"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Clock } from '@element-plus/icons-vue'

const props = defineProps({
  alerts: {
    type: Array,
    required: true
  },
  type: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['view', 'refresh'])

// 方法
const handleView = (alertId) => {
  emit('view', alertId)
}

const getEmptyDescription = () => {
  const descriptions = {
    'overdue': '暂无逾期的实验计划',
    'today': '今天没有到期的实验计划',
    'tomorrow': '明天没有到期的实验计划',
    'thisWeek': '本周没有到期的实验计划'
  }
  return descriptions[props.type] || '暂无预警信息'
}

const getAlertClass = (alert) => {
  return `alert-${alert.urgencyLevel}`
}

const getUrgencyType = (level) => {
  const types = {
    'overdue': 'danger',
    'urgent': 'danger',
    'high': 'warning',
    'medium': 'primary',
    'low': 'info'
  }
  return types[level] || 'info'
}

const getUrgencyLabel = (level) => {
  const labels = {
    'overdue': '已逾期',
    'urgent': '紧急',
    'high': '高优先级',
    'medium': '中优先级',
    'low': '低优先级'
  }
  return labels[level] || level
}

const getUrgencyPercentage = (level) => {
  const percentages = {
    'overdue': 100,
    'urgent': 90,
    'high': 70,
    'medium': 50,
    'low': 30
  }
  return percentages[level] || 0
}

const getProgressColor = (level) => {
  const colors = {
    'overdue': '#f56c6c',
    'urgent': '#f56c6c',
    'high': '#e6a23c',
    'medium': '#409eff',
    'low': '#67c23a'
  }
  return colors[level] || '#909399'
}

const getDaysClass = (days) => {
  if (days < 0) return 'days-overdue'
  if (days === 0) return 'days-today'
  if (days === 1) return 'days-tomorrow'
  return 'days-normal'
}

const getDaysText = (days) => {
  if (days < 0) return `逾期 ${Math.abs(days)} 天`
  if (days === 0) return '今天到期'
  if (days === 1) return '明天到期'
  return `${days} 天后到期`
}

const getTimeDescription = (days) => {
  if (days < 0) return `已逾期 ${Math.abs(days)} 天，请尽快处理`
  if (days === 0) return '今天到期，请及时执行'
  if (days === 1) return '明天到期，请提前准备'
  if (days <= 3) return '即将到期，请关注进度'
  return '计划执行中，请按时完成'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}
</script>

<style scoped>
.alert-list {
  min-height: 200px;
}

.no-alerts {
  text-align: center;
  padding: 60px 0;
}

.alerts-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.alert-item {
  background: white;
  border-radius: 8px;
  border: 1px solid #ebeef5;
  overflow: hidden;
  transition: all 0.3s;
}

.alert-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.alert-item.alert-overdue {
  border-left: 4px solid #f56c6c;
  background: linear-gradient(90deg, #fef0f0 0%, #ffffff 10%);
}

.alert-item.alert-urgent {
  border-left: 4px solid #f56c6c;
  background: linear-gradient(90deg, #fef0f0 0%, #ffffff 10%);
}

.alert-item.alert-high {
  border-left: 4px solid #e6a23c;
  background: linear-gradient(90deg, #fdf6ec 0%, #ffffff 10%);
}

.alert-item.alert-medium {
  border-left: 4px solid #409eff;
  background: linear-gradient(90deg, #ecf5ff 0%, #ffffff 10%);
}

.alert-item.alert-low {
  border-left: 4px solid #67c23a;
  background: linear-gradient(90deg, #f0f9ff 0%, #ffffff 10%);
}

.alert-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #f5f7fa;
}

.alert-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert-title h4 {
  margin: 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
}

.alert-content {
  padding: 15px 20px;
}

.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.info-item .label {
  color: #909399;
  font-size: 13px;
  margin-right: 8px;
  min-width: 70px;
}

.info-item .value {
  color: #303133;
  font-size: 13px;
  font-weight: 500;
}

.days-overdue {
  color: #f56c6c !important;
  font-weight: 600;
}

.days-today {
  color: #e6a23c !important;
  font-weight: 600;
}

.days-tomorrow {
  color: #409eff !important;
  font-weight: 600;
}

.days-normal {
  color: #67c23a !important;
}

.alert-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background-color: #f8f9fa;
  border-top: 1px solid #f5f7fa;
}

.alert-time {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #606266;
  font-size: 12px;
}

.alert-progress {
  width: 120px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .alert-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .alert-title {
    width: 100%;
  }
  
  .alert-actions {
    width: 100%;
    display: flex;
    justify-content: flex-end;
  }
  
  .alert-footer {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
  
  .alert-progress {
    width: 100%;
  }
  
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 12px;
  }
  
  .info-item .label {
    margin-bottom: 4px;
    min-width: auto;
  }
}

/* 动画效果 */
.alert-item {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 进度条样式优化 */
:deep(.el-progress-bar__outer) {
  border-radius: 2px;
}

:deep(.el-progress-bar__inner) {
  border-radius: 2px;
}
</style>
