<template>
  <div class="realtime-stats">
    <!-- 实时数据刷新提示 -->
    <div class="refresh-info">
      <el-icon class="refresh-icon" :class="{ spinning: autoRefresh }"><Refresh /></el-icon>
      <span>数据每30秒自动刷新</span>
      <el-switch 
        v-model="autoRefresh" 
        @change="toggleAutoRefresh"
        active-text="自动刷新"
        style="margin-left: 10px;"
      />
    </div>

    <!-- 今日统计 -->
    <div class="stats-section">
      <h4>今日统计</h4>
      <el-row :gutter="20">
        <el-col :span="8">
          <div class="stat-item today-plans">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.today?.todayPlans || 0 }}</div>
              <div class="stat-label">今日计划</div>
            </div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-item today-created">
            <div class="stat-icon">➕</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.today?.todayCreated || 0 }}</div>
              <div class="stat-label">今日创建</div>
            </div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-item today-approved">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.today?.todayApproved || 0 }}</div>
              <div class="stat-label">今日批准</div>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 本周统计 -->
    <div class="stats-section">
      <h4>本周统计</h4>
      <el-row :gutter="20">
        <el-col :span="8">
          <div class="stat-item week-plans">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.week?.weekPlans || 0 }}</div>
              <div class="stat-label">本周计划</div>
            </div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-item week-created">
            <div class="stat-icon">🆕</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.week?.weekCreated || 0 }}</div>
              <div class="stat-label">本周创建</div>
            </div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-item week-completed">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.week?.weekCompleted || 0 }}</div>
              <div class="stat-label">本周完成</div>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 24小时活跃度 -->
    <div class="stats-section">
      <h4>24小时活跃度</h4>
      <el-row :gutter="20">
        <el-col :span="8">
          <div class="stat-item activity-plans">
            <div class="stat-icon">🔥</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.activity?.recentPlans || 0 }}</div>
              <div class="stat-label">新增计划</div>
            </div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-item activity-records">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.activity?.recentRecords || 0 }}</div>
              <div class="stat-label">新增记录</div>
            </div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-item activity-reviews">
            <div class="stat-icon">👁️</div>
            <div class="stat-content">
              <div class="stat-number">{{ realTimeData.activity?.recentReviews || 0 }}</div>
              <div class="stat-label">审核操作</div>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 更新时间 -->
    <div class="update-time">
      <el-icon><Clock /></el-icon>
      最后更新: {{ formatTime(lastUpdateTime) }}
    </div>

    <!-- 操作按钮 -->
    <div class="actions">
      <el-button @click="$emit('close')">关闭</el-button>
      <el-button type="primary" @click="refreshData" :loading="loading">
        立即刷新
      </el-button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Refresh, Clock } from '@element-plus/icons-vue'
import { experimentMonitorApi } from '@/api/experiment-monitor'

const emit = defineEmits(['close'])

// 响应式数据
const realTimeData = ref({})
const autoRefresh = ref(true)
const loading = ref(false)
const lastUpdateTime = ref(null)
const refreshTimer = ref(null)

// 加载实时数据
const loadRealTimeData = async () => {
  loading.value = true
  try {
    const response = await experimentMonitorApi.getRealTimeStats()
    
    if (response.data.success) {
      realTimeData.value = response.data.data
      lastUpdateTime.value = new Date()
    }
  } catch (error) {
    console.error('加载实时数据失败:', error)
    ElMessage.error('加载实时数据失败')
  } finally {
    loading.value = false
  }
}

// 刷新数据
const refreshData = () => {
  loadRealTimeData()
}

// 切换自动刷新
const toggleAutoRefresh = (enabled) => {
  if (enabled) {
    startAutoRefresh()
  } else {
    stopAutoRefresh()
  }
}

// 开始自动刷新
const startAutoRefresh = () => {
  if (refreshTimer.value) {
    clearInterval(refreshTimer.value)
  }
  
  refreshTimer.value = setInterval(() => {
    loadRealTimeData()
  }, 30000) // 30秒刷新一次
}

// 停止自动刷新
const stopAutoRefresh = () => {
  if (refreshTimer.value) {
    clearInterval(refreshTimer.value)
    refreshTimer.value = null
  }
}

// 格式化时间
const formatTime = (time) => {
  if (!time) return '-'
  return time.toLocaleTimeString('zh-CN')
}

// 生命周期
onMounted(() => {
  loadRealTimeData()
  if (autoRefresh.value) {
    startAutoRefresh()
  }
})

onUnmounted(() => {
  stopAutoRefresh()
})
</script>

<style scoped>
.realtime-stats {
  padding: 20px 0;
}

.refresh-info {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  padding: 10px;
  background-color: #f8f9fa;
  border-radius: 6px;
  font-size: 14px;
  color: #606266;
}

.refresh-icon {
  margin-right: 8px;
  color: #409eff;
}

.refresh-icon.spinning {
  animation: spin 2s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.stats-section {
  margin-bottom: 25px;
}

.stats-section h4 {
  margin: 0 0 15px 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
  border-left: 3px solid #409eff;
  padding-left: 10px;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 15px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-radius: 8px;
  transition: transform 0.3s ease;
}

.stat-item:hover {
  transform: translateY(-2px);
}

.stat-item.today-plans {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-item.today-created {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.stat-item.today-approved {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.stat-item.week-plans {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

.stat-item.week-created {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  color: white;
}

.stat-item.week-completed {
  background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
  color: #333;
}

.stat-item.activity-plans {
  background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
  color: #333;
}

.stat-item.activity-records {
  background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
  color: white;
}

.stat-item.activity-reviews {
  background: linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%);
  color: #333;
}

.stat-icon {
  font-size: 24px;
  margin-right: 15px;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  line-height: 1;
}

.stat-label {
  font-size: 12px;
  opacity: 0.8;
  margin-top: 4px;
}

.update-time {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 20px 0;
  font-size: 12px;
  color: #909399;
}

.update-time .el-icon {
  margin-right: 5px;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .refresh-info {
    flex-direction: column;
    gap: 10px;
  }
  
  .stat-item {
    flex-direction: column;
    text-align: center;
  }
  
  .stat-icon {
    margin-right: 0;
    margin-bottom: 10px;
  }
  
  .actions {
    flex-direction: column;
  }
  
  .actions .el-button {
    width: 100%;
  }
}
</style>
