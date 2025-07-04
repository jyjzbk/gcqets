<template>
  <div class="experiment-calendar">
    <!-- 页面头部 -->
    <div class="calendar-header">
      <div class="header-left">
        <h2>实验日历</h2>
        <p>查看实验计划安排和逾期预警</p>
      </div>
      <div class="header-right">
        <el-button type="primary" @click="showOverdueAlerts">
          <el-icon><Warning /></el-icon>
          逾期预警 
          <el-badge :value="alertCounts.total" :hidden="alertCounts.total === 0" />
        </el-button>
        <el-button @click="refreshCalendar">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
      </div>
    </div>

    <!-- 日历工具栏 -->
    <div class="calendar-toolbar">
      <div class="view-controls">
        <el-button-group>
          <el-button @click="previousMonth">
            <el-icon><ArrowLeft /></el-icon>
            上月
          </el-button>
          <el-button @click="currentMonth">今天</el-button>
          <el-button @click="nextMonth">
            下月
            <el-icon><ArrowRight /></el-icon>
          </el-button>
        </el-button-group>
        <span class="current-month">{{ currentMonthText }}</span>
      </div>

      <div class="calendar-info">
        <el-tag type="info">总计划: {{ calendarSummary.totalPlans }}</el-tag>
        <el-tag type="danger" v-if="calendarSummary.overduePlans > 0">
          逾期: {{ calendarSummary.overduePlans }}
        </el-tag>
      </div>
    </div>

    <!-- 日历主体 -->
    <div class="calendar-container">
      <el-calendar v-model="selectedDate" @panel-change="handlePanelChange">
        <template #date-cell="{ data }">
          <div class="calendar-day">
            <div class="day-number">{{ data.day.split('-').pop() }}</div>
            <div class="day-events">
              <div
                v-for="event in getDayEvents(data.day)"
                :key="event.id"
                class="event-item"
                :class="getEventClass(event)"
                @click="handleEventClick(event)"
              >
                <span class="event-title">{{ event.title }}</span>
                <span v-if="event.extendedProps.isOverdue" class="overdue-icon">⚠️</span>
              </div>
            </div>
          </div>
        </template>
      </el-calendar>
    </div>

    <!-- 实验详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="实验计划详情"
      width="800px"
      :close-on-click-modal="false"
    >
      <ExperimentDetail
        v-if="selectedExperiment"
        :experiment="selectedExperiment"
        @close="detailDialogVisible = false"
      />
    </el-dialog>

    <!-- 逾期预警对话框 -->
    <el-dialog
      v-model="alertDialogVisible"
      title="逾期预警"
      width="900px"
      :close-on-click-modal="false"
    >
      <OverdueAlerts
        v-if="alertDialogVisible"
        :alerts="overdueAlerts"
        :counts="alertCounts"
        @refresh="loadOverdueAlerts"
        @view-experiment="viewExperimentDetail"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Warning, Refresh, ArrowLeft, ArrowRight } from '@element-plus/icons-vue'
import ExperimentDetail from './components/ExperimentDetail.vue'
import OverdueAlerts from './components/OverdueAlerts.vue'
import { experimentCalendarApi } from '@/api/experiment-calendar'

// 响应式数据
const selectedDate = ref(new Date())
const detailDialogVisible = ref(false)
const alertDialogVisible = ref(false)
const selectedExperiment = ref(null)
const overdueAlerts = ref({})
const alertCounts = ref({
  total: 0,
  overdue: 0,
  today: 0,
  tomorrow: 0,
  thisWeek: 0
})
const calendarSummary = ref({
  totalPlans: 0,
  overduePlans: 0,
  statusCounts: {}
})
const calendarEvents = ref([])

// 计算属性
const currentMonthText = computed(() => {
  return selectedDate.value.toLocaleDateString('zh-CN', {
    year: 'numeric',
    month: 'long'
  })
})

// 初始化日历
const initCalendar = async () => {
  // 加载初始数据
  await loadCalendarData()
  await loadOverdueAlerts()
}

// 加载日历数据
const loadCalendarData = async () => {
  try {
    const currentDate = selectedDate.value
    const start = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).toISOString().split('T')[0]
    const end = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).toISOString().split('T')[0]

    const response = await experimentCalendarApi.getCalendarData({ start, end })

    if (response.data.success) {
      const { events, summary } = response.data.data

      // 更新日历事件
      calendarEvents.value = events

      // 更新摘要信息
      calendarSummary.value = summary
    }
  } catch (error) {
    console.error('加载日历数据失败:', error)
    ElMessage.error('加载日历数据失败')
  }
}

// 加载逾期预警
const loadOverdueAlerts = async () => {
  try {
    const response = await experimentCalendarApi.getOverdueAlerts({ days: 7 })
    
    if (response.data.success) {
      const { alerts, counts } = response.data.data
      overdueAlerts.value = alerts
      alertCounts.value = counts
    }
  } catch (error) {
    console.error('加载预警数据失败:', error)
  }
}

// 处理事件点击
const handleEventClick = async (event) => {
  const eventId = event.id
  await viewExperimentDetail(eventId)
}

// 查看实验详情
const viewExperimentDetail = async (experimentId) => {
  try {
    const response = await experimentCalendarApi.getExperimentDetails(experimentId)
    
    if (response.data.success) {
      selectedExperiment.value = response.data.data
      detailDialogVisible.value = true
    }
  } catch (error) {
    console.error('获取实验详情失败:', error)
    ElMessage.error('获取实验详情失败')
  }
}

// 处理面板变化
const handlePanelChange = (date) => {
  selectedDate.value = date
  loadCalendarData()
}

// 获取指定日期的事件
const getDayEvents = (day) => {
  return calendarEvents.value.filter(event => event.start === day)
}

// 获取事件样式类
const getEventClass = (event) => {
  const classes = ['event-item']

  if (event.extendedProps.isOverdue) {
    classes.push('event-overdue')
  }

  if (event.extendedProps.status) {
    classes.push(`event-${event.extendedProps.status}`)
  }

  return classes
}

// 月份导航
const previousMonth = () => {
  const newDate = new Date(selectedDate.value)
  newDate.setMonth(newDate.getMonth() - 1)
  selectedDate.value = newDate
  loadCalendarData()
}

const nextMonth = () => {
  const newDate = new Date(selectedDate.value)
  newDate.setMonth(newDate.getMonth() + 1)
  selectedDate.value = newDate
  loadCalendarData()
}

const currentMonth = () => {
  selectedDate.value = new Date()
  loadCalendarData()
}

// 刷新日历
const refreshCalendar = async () => {
  await loadCalendarData()
  await loadOverdueAlerts()
  ElMessage.success('日历已刷新')
}

// 显示逾期预警
const showOverdueAlerts = () => {
  alertDialogVisible.value = true
}

// 生命周期
onMounted(() => {
  initCalendar()
})
</script>

<style scoped>
.experiment-calendar {
  padding: 20px;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #ebeef5;
}

.header-left h2 {
  margin: 0 0 5px 0;
  color: #303133;
}

.header-left p {
  margin: 0;
  color: #909399;
  font-size: 14px;
}

.header-right {
  display: flex;
  gap: 10px;
}

.calendar-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 6px;
}

.calendar-info {
  display: flex;
  gap: 10px;
}

.current-month {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
  margin-left: 20px;
}

.calendar-container {
  background: white;
  border-radius: 6px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  padding: 20px;
}

/* 日历单元格样式 */
.calendar-day {
  height: 100px;
  padding: 4px;
  position: relative;
}

.day-number {
  font-weight: 600;
  color: #303133;
  margin-bottom: 4px;
}

.day-events {
  display: flex;
  flex-direction: column;
  gap: 2px;
  max-height: 70px;
  overflow: hidden;
}

.event-item {
  background: #409eff;
  color: white;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 11px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: all 0.3s;
}

.event-item:hover {
  transform: scale(1.05);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.event-title {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.overdue-icon {
  margin-left: 4px;
  font-size: 10px;
}

/* 事件状态样式 */
.event-overdue {
  animation: blink 1.5s infinite;
}

.event-draft {
  background-color: #909399 !important;
}

.event-pending {
  background-color: #e6a23c !important;
}

.event-approved {
  background-color: #67c23a !important;
}

.event-rejected {
  background-color: #f56c6c !important;
}

.event-executing {
  background-color: #409eff !important;
}

.event-completed {
  background-color: #909399 !important;
  opacity: 0.7;
}

.event-cancelled {
  background-color: #f56c6c !important;
  opacity: 0.7;
  text-decoration: line-through;
}

/* Element Plus 日历样式定制 */
:deep(.el-calendar-table .el-calendar-day) {
  height: 120px;
  padding: 0;
}

:deep(.el-calendar__header) {
  display: none; /* 隐藏默认头部，使用自定义工具栏 */
}

:deep(.el-calendar__body) {
  padding: 0;
}

:deep(.el-calendar-table) {
  border: 1px solid #ebeef5;
}

:deep(.el-calendar-table td) {
  border-right: 1px solid #ebeef5;
  border-bottom: 1px solid #ebeef5;
  vertical-align: top;
}

:deep(.el-calendar-table .is-today .calendar-day .day-number) {
  background-color: #409eff;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 逾期闪烁动画 */
@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0.6; }
}

/* 响应式设计 */
@media (max-width: 768px) {
  .calendar-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .calendar-toolbar {
    flex-direction: column;
    gap: 15px;
  }
  
  .view-controls {
    width: 100%;
  }
  
  .calendar-info {
    width: 100%;
    justify-content: center;
  }
}
</style>
