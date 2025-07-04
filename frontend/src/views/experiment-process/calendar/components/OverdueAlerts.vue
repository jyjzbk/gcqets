<template>
  <div class="overdue-alerts">
    <!-- 统计概览 -->
    <div class="alert-summary">
      <el-row :gutter="20">
        <el-col :span="6">
          <div class="summary-card overdue">
            <div class="card-icon">⚠️</div>
            <div class="card-content">
              <div class="card-number">{{ counts.overdue }}</div>
              <div class="card-label">已逾期</div>
            </div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="summary-card today">
            <div class="card-icon">🔥</div>
            <div class="card-content">
              <div class="card-number">{{ counts.today }}</div>
              <div class="card-label">今天到期</div>
            </div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="summary-card tomorrow">
            <div class="card-icon">⏰</div>
            <div class="card-content">
              <div class="card-number">{{ counts.tomorrow }}</div>
              <div class="card-label">明天到期</div>
            </div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="summary-card week">
            <div class="card-icon">📅</div>
            <div class="card-content">
              <div class="card-number">{{ counts.thisWeek }}</div>
              <div class="card-label">本周到期</div>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 预警列表 -->
    <div class="alert-tabs">
      <el-tabs v-model="activeTab" @tab-change="handleTabChange">
        <el-tab-pane 
          label="已逾期" 
          name="overdue"
          :disabled="counts.overdue === 0"
        >
          <template #label>
            <span>
              已逾期
              <el-badge 
                v-if="counts.overdue > 0" 
                :value="counts.overdue" 
                type="danger" 
                style="margin-left: 5px"
              />
            </span>
          </template>
          <AlertList 
            :alerts="alerts.overdue" 
            type="overdue"
            @view="handleViewExperiment"
            @refresh="handleRefresh"
          />
        </el-tab-pane>
        
        <el-tab-pane 
          label="今天到期" 
          name="today"
          :disabled="counts.today === 0"
        >
          <template #label>
            <span>
              今天到期
              <el-badge 
                v-if="counts.today > 0" 
                :value="counts.today" 
                type="warning" 
                style="margin-left: 5px"
              />
            </span>
          </template>
          <AlertList 
            :alerts="alerts.today" 
            type="today"
            @view="handleViewExperiment"
            @refresh="handleRefresh"
          />
        </el-tab-pane>
        
        <el-tab-pane 
          label="明天到期" 
          name="tomorrow"
          :disabled="counts.tomorrow === 0"
        >
          <template #label>
            <span>
              明天到期
              <el-badge 
                v-if="counts.tomorrow > 0" 
                :value="counts.tomorrow" 
                type="primary" 
                style="margin-left: 5px"
              />
            </span>
          </template>
          <AlertList 
            :alerts="alerts.tomorrow" 
            type="tomorrow"
            @view="handleViewExperiment"
            @refresh="handleRefresh"
          />
        </el-tab-pane>
        
        <el-tab-pane 
          label="本周到期" 
          name="thisWeek"
          :disabled="counts.thisWeek === 0"
        >
          <template #label>
            <span>
              本周到期
              <el-badge 
                v-if="counts.thisWeek > 0" 
                :value="counts.thisWeek" 
                type="info" 
                style="margin-left: 5px"
              />
            </span>
          </template>
          <AlertList 
            :alerts="alerts.thisWeek" 
            type="thisWeek"
            @view="handleViewExperiment"
            @refresh="handleRefresh"
          />
        </el-tab-pane>
      </el-tabs>
    </div>

    <!-- 操作按钮 -->
    <div class="alert-actions">
      <el-button @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新数据
      </el-button>
      <el-button type="primary" @click="exportAlerts">
        <el-icon><Download /></el-icon>
        导出预警
      </el-button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Refresh, Download } from '@element-plus/icons-vue'
import AlertList from './AlertList.vue'

const props = defineProps({
  alerts: {
    type: Object,
    required: true
  },
  counts: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['refresh', 'view-experiment'])

// 响应式数据
const activeTab = ref('overdue')

// 计算属性
const hasAnyAlerts = computed(() => {
  return props.counts.total > 0
})

// 方法
const handleTabChange = (tabName) => {
  activeTab.value = tabName
}

const handleViewExperiment = (experimentId) => {
  emit('view-experiment', experimentId)
}

const handleRefresh = () => {
  emit('refresh')
}

const exportAlerts = () => {
  // 导出预警数据为CSV
  try {
    const csvData = generateCSVData()
    downloadCSV(csvData, `实验预警_${new Date().toLocaleDateString()}.csv`)
    ElMessage.success('预警数据导出成功')
  } catch (error) {
    ElMessage.error('导出失败: ' + error.message)
  }
}

const generateCSVData = () => {
  const headers = ['计划名称', '计划编码', '计划日期', '教师', '实验目录', '班级', '逾期天数', '紧急程度']
  const rows = []
  
  // 添加表头
  rows.push(headers.join(','))
  
  // 添加数据行
  Object.entries(props.alerts).forEach(([type, alertList]) => {
    alertList.forEach(alert => {
      const row = [
        `"${alert.name}"`,
        alert.code,
        alert.plannedDate,
        `"${alert.teacher}"`,
        `"${alert.catalog}"`,
        `"${alert.className}"`,
        alert.daysUntil,
        getUrgencyLabel(alert.urgencyLevel)
      ]
      rows.push(row.join(','))
    })
  })
  
  return rows.join('\n')
}

const downloadCSV = (csvData, filename) => {
  const blob = new Blob(['\ufeff' + csvData], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  link.setAttribute('href', url)
  link.setAttribute('download', filename)
  link.style.visibility = 'hidden'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const getUrgencyLabel = (level) => {
  const labels = {
    'overdue': '已逾期',
    'urgent': '紧急',
    'high': '高',
    'medium': '中',
    'low': '低'
  }
  return labels[level] || level
}

// 生命周期
onMounted(() => {
  // 自动选择有数据的第一个标签
  if (props.counts.overdue > 0) {
    activeTab.value = 'overdue'
  } else if (props.counts.today > 0) {
    activeTab.value = 'today'
  } else if (props.counts.tomorrow > 0) {
    activeTab.value = 'tomorrow'
  } else if (props.counts.thisWeek > 0) {
    activeTab.value = 'thisWeek'
  }
})
</script>

<style scoped>
.overdue-alerts {
  padding: 20px 0;
}

.alert-summary {
  margin-bottom: 30px;
}

.summary-card {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 8px;
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
}

.summary-card:hover {
  transform: translateY(-2px);
}

.summary-card.overdue {
  border-left: 4px solid #f56c6c;
}

.summary-card.today {
  border-left: 4px solid #e6a23c;
}

.summary-card.tomorrow {
  border-left: 4px solid #409eff;
}

.summary-card.week {
  border-left: 4px solid #909399;
}

.card-icon {
  font-size: 32px;
  margin-right: 15px;
}

.card-content {
  flex: 1;
}

.card-number {
  font-size: 28px;
  font-weight: 600;
  color: #303133;
  line-height: 1;
}

.card-label {
  font-size: 14px;
  color: #909399;
  margin-top: 5px;
}

.alert-tabs {
  margin-bottom: 30px;
}

.alert-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
}

/* 标签页样式优化 */
:deep(.el-tabs__header) {
  margin-bottom: 20px;
}

:deep(.el-tabs__nav-wrap::after) {
  background-color: #ebeef5;
}

:deep(.el-tabs__active-bar) {
  background-color: #409eff;
}

:deep(.el-tabs__item.is-active) {
  color: #409eff;
  font-weight: 600;
}

/* 徽章样式 */
:deep(.el-badge__content) {
  font-size: 12px;
  padding: 0 6px;
  height: 18px;
  line-height: 18px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .alert-summary .el-col {
    margin-bottom: 15px;
  }
  
  .summary-card {
    padding: 15px;
  }
  
  .card-icon {
    font-size: 24px;
    margin-right: 10px;
  }
  
  .card-number {
    font-size: 24px;
  }
  
  .alert-actions {
    flex-direction: column;
  }
}
</style>
