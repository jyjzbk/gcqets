<template>
  <div class="permission-audit">
    <div class="audit-header">
      <div class="header-left">
        <h3>权限审计与历史</h3>
        <el-text type="info" size="small">
          查看权限变更历史、审计日志和冲突管理
        </el-text>
      </div>
      <div class="header-right">
        <el-button @click="refreshData" :loading="loading">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <el-button @click="exportLogs" type="primary">
          <el-icon><Download /></el-icon>
          导出日志
        </el-button>
      </div>
    </div>

    <el-tabs v-model="activeTab" class="audit-tabs">
      <!-- 审计日志 -->
      <el-tab-pane label="审计日志" name="logs">
        <div class="tab-content">
          <!-- 筛选条件 -->
          <div class="filter-bar">
            <el-form :model="logFilters" inline>
              <el-form-item label="操作类型">
                <el-select v-model="logFilters.action" clearable placeholder="全部">
                  <el-option label="授予" value="grant" />
                  <el-option label="撤销" value="revoke" />
                  <el-option label="修改" value="modify" />
                  <el-option label="继承" value="inherit" />
                  <el-option label="覆盖" value="override" />
                </el-select>
              </el-form-item>
              <el-form-item label="目标类型">
                <el-select v-model="logFilters.target_type" clearable placeholder="全部">
                  <el-option label="用户" value="user" />
                  <el-option label="角色" value="role" />
                  <el-option label="组织" value="organization" />
                </el-select>
              </el-form-item>
              <el-form-item label="时间范围">
                <el-date-picker
                  v-model="logFilters.dateRange"
                  type="daterange"
                  range-separator="至"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  format="YYYY-MM-DD"
                  value-format="YYYY-MM-DD"
                />
              </el-form-item>
              <el-form-item>
                <el-button @click="loadAuditLogs" type="primary">查询</el-button>
                <el-button @click="resetLogFilters">重置</el-button>
              </el-form-item>
            </el-form>
          </div>

          <!-- 审计日志表格 -->
          <el-table :data="auditLogs" v-loading="loading" style="width: 100%">
            <el-table-column prop="created_at" label="操作时间" width="180">
              <template #default="{ row }">
                {{ formatDateTime(row.created_at) }}
              </template>
            </el-table-column>
            <el-table-column prop="user.name" label="操作人" width="120" />
            <el-table-column prop="action_description" label="操作描述" width="150" />
            <el-table-column prop="target_name" label="目标对象" width="150" />
            <el-table-column prop="permission.display_name" label="权限" width="180" />
            <el-table-column prop="organization.name" label="组织" width="150" />
            <el-table-column prop="status" label="状态" width="80">
              <template #default="{ row }">
                <el-tag :type="getStatusTag(row.status)">
                  {{ getStatusText(row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="120">
              <template #default="{ row }">
                <el-button size="small" @click="viewLogDetail(row)">
                  查看详情
                </el-button>
              </template>
            </el-table-column>
          </el-table>

          <!-- 分页 -->
          <div class="pagination">
            <el-pagination
              v-model:current-page="logPagination.current_page"
              v-model:page-size="logPagination.per_page"
              :total="logPagination.total"
              :page-sizes="[10, 20, 50, 100]"
              layout="total, sizes, prev, pager, next, jumper"
              @size-change="loadAuditLogs"
              @current-change="loadAuditLogs"
            />
          </div>
        </div>
      </el-tab-pane>

      <!-- 权限冲突 -->
      <el-tab-pane label="权限冲突" name="conflicts">
        <div class="tab-content">
          <!-- 冲突统计 -->
          <div class="conflict-stats">
            <el-row :gutter="20">
              <el-col :span="6">
                <el-card>
                  <div class="stat-item">
                    <div class="stat-value">{{ conflictStats.total || 0 }}</div>
                    <div class="stat-label">总冲突数</div>
                  </div>
                </el-card>
              </el-col>
              <el-col :span="6">
                <el-card>
                  <div class="stat-item">
                    <div class="stat-value danger">{{ conflictStats.unresolved || 0 }}</div>
                    <div class="stat-label">未解决</div>
                  </div>
                </el-card>
              </el-col>
              <el-col :span="6">
                <el-card>
                  <div class="stat-item">
                    <div class="stat-value warning">{{ conflictStats.high_priority || 0 }}</div>
                    <div class="stat-label">高优先级</div>
                  </div>
                </el-card>
              </el-col>
              <el-col :span="6">
                <el-card>
                  <div class="stat-item">
                    <div class="stat-value success">{{ getResolvedCount() }}</div>
                    <div class="stat-label">已解决</div>
                  </div>
                </el-card>
              </el-col>
            </el-row>
          </div>

          <!-- 冲突列表 -->
          <div class="conflict-filters">
            <el-form :model="conflictFilters" inline>
              <el-form-item label="状态">
                <el-select v-model="conflictFilters.status" @change="loadConflicts">
                  <el-option label="未解决" value="unresolved" />
                  <el-option label="已解决" value="resolved" />
                  <el-option label="已忽略" value="ignored" />
                </el-select>
              </el-form-item>
              <el-form-item label="优先级">
                <el-select v-model="conflictFilters.priority" clearable @change="loadConflicts">
                  <el-option label="高" value="high" />
                  <el-option label="中" value="medium" />
                  <el-option label="低" value="low" />
                </el-select>
              </el-form-item>
              <el-form-item label="冲突类型">
                <el-select v-model="conflictFilters.conflict_type" clearable @change="loadConflicts">
                  <el-option label="角色用户冲突" value="role_user" />
                  <el-option label="角色间冲突" value="role_role" />
                  <el-option label="继承冲突" value="inheritance" />
                  <el-option label="显式拒绝" value="explicit_deny" />
                </el-select>
              </el-form-item>
            </el-form>
          </div>

          <el-table :data="conflicts" v-loading="loading" style="width: 100%">
            <el-table-column prop="conflict_description" label="冲突描述" width="200" />
            <el-table-column prop="user.name" label="用户" width="120" />
            <el-table-column prop="permission.display_name" label="权限" width="180" />
            <el-table-column prop="priority_label" label="优先级" width="80">
              <template #default="{ row }">
                <el-tag :type="getPriorityTag(row.priority)">
                  {{ row.priority_label }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="status_label" label="状态" width="80">
              <template #default="{ row }">
                <el-tag :type="getConflictStatusTag(row.status)">
                  {{ row.status_label }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="created_at" label="发现时间" width="180">
              <template #default="{ row }">
                {{ formatDateTime(row.created_at) }}
              </template>
            </el-table-column>
            <el-table-column label="操作" width="200">
              <template #default="{ row }">
                <el-button
                  v-if="row.status === 'unresolved'"
                  size="small"
                  type="primary"
                  @click="resolveConflict(row)"
                >
                  解决
                </el-button>
                <el-button
                  v-if="row.status === 'unresolved'"
                  size="small"
                  @click="ignoreConflict(row)"
                >
                  忽略
                </el-button>
                <el-button size="small" @click="viewConflictDetail(row)">
                  详情
                </el-button>
              </template>
            </el-table-column>
          </el-table>

          <!-- 分页 -->
          <div class="pagination">
            <el-pagination
              v-model:current-page="conflictPagination.current_page"
              v-model:page-size="conflictPagination.per_page"
              :total="conflictPagination.total"
              :page-sizes="[10, 20, 50]"
              layout="total, sizes, prev, pager, next, jumper"
              @size-change="loadConflicts"
              @current-change="loadConflicts"
            />
          </div>
        </div>
      </el-tab-pane>

      <!-- 统计分析 -->
      <el-tab-pane label="统计分析" name="stats">
        <div class="tab-content">
          <div class="stats-container">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-card title="权限热点分析">
                  <div class="hotspot-list">
                    <div
                      v-for="(hotspot, index) in permissionHotspots"
                      :key="index"
                      class="hotspot-item"
                    >
                      <div class="hotspot-name">{{ hotspot.permission.display_name }}</div>
                      <div class="hotspot-count">{{ hotspot.operation_count }} 次操作</div>
                    </div>
                  </div>
                </el-card>
              </el-col>
              <el-col :span="12">
                <el-card title="操作趋势">
                  <div class="trend-placeholder">
                    <el-text type="info">操作趋势图表（待实现）</el-text>
                  </div>
                </el-card>
              </el-col>
            </el-row>
          </div>
        </div>
      </el-tab-pane>
    </el-tabs>

    <!-- 日志详情对话框 -->
    <el-dialog
      v-model="logDetailVisible"
      title="审计日志详情"
      width="600px"
    >
      <div v-if="selectedLog">
        <el-descriptions :column="1" border>
          <el-descriptions-item label="操作时间">
            {{ formatDateTime(selectedLog.created_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="操作人">
            {{ selectedLog.user?.name || '系统' }}
          </el-descriptions-item>
          <el-descriptions-item label="操作描述">
            {{ selectedLog.action_description }}
          </el-descriptions-item>
          <el-descriptions-item label="目标对象">
            {{ selectedLog.target_name }}
          </el-descriptions-item>
          <el-descriptions-item label="IP地址">
            {{ selectedLog.ip_address }}
          </el-descriptions-item>
          <el-descriptions-item label="用户代理">
            {{ selectedLog.user_agent }}
          </el-descriptions-item>
          <el-descriptions-item label="操作原因">
            {{ selectedLog.reason || '无' }}
          </el-descriptions-item>
        </el-descriptions>
        
        <div v-if="selectedLog.changes_summary && selectedLog.changes_summary.length > 0" class="changes-summary">
          <h4>变更详情</h4>
          <el-table :data="selectedLog.changes_summary" style="width: 100%">
            <el-table-column prop="field" label="字段" />
            <el-table-column prop="old_value" label="原值" />
            <el-table-column prop="new_value" label="新值" />
          </el-table>
        </div>
      </div>
      <template #footer>
        <el-button @click="logDetailVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { permissionAuditApi } from '@/api/permissionVisualization'
import dayjs from 'dayjs'

// 响应式数据
const loading = ref(false)
const activeTab = ref('logs')
const auditLogs = ref([])
const conflicts = ref([])
const conflictStats = ref({})
const permissionHotspots = ref([])
const selectedLog = ref(null)
const logDetailVisible = ref(false)

// 筛选条件
const logFilters = reactive({
  action: '',
  target_type: '',
  dateRange: []
})

const conflictFilters = reactive({
  status: 'unresolved',
  priority: '',
  conflict_type: ''
})

// 分页
const logPagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0
})

const conflictPagination = reactive({
  current_page: 1,
  per_page: 15,
  total: 0
})

// 计算属性
const getResolvedCount = computed(() => {
  return (conflictStats.value.total || 0) - (conflictStats.value.unresolved || 0)
})

// 方法
const loadAuditLogs = async () => {
  loading.value = true
  try {
    const params = {
      page: logPagination.current_page,
      per_page: logPagination.per_page,
      ...logFilters
    }
    
    if (logFilters.dateRange && logFilters.dateRange.length === 2) {
      params.start_date = logFilters.dateRange[0]
      params.end_date = logFilters.dateRange[1]
    }
    
    const response = await permissionAuditApi.getLogs(params)
    auditLogs.value = response.data.data
    logPagination.total = response.data.total
    logPagination.current_page = response.data.current_page
  } catch (error) {
    console.error('加载审计日志失败:', error)
    ElMessage.error('加载审计日志失败')
  } finally {
    loading.value = false
  }
}

const loadConflicts = async () => {
  loading.value = true
  try {
    const params = {
      page: conflictPagination.current_page,
      per_page: conflictPagination.per_page,
      ...conflictFilters
    }
    
    const response = await permissionAuditApi.getConflicts(params)
    conflicts.value = response.data.data
    conflictPagination.total = response.data.total
    conflictPagination.current_page = response.data.current_page
  } catch (error) {
    console.error('加载权限冲突失败:', error)
    ElMessage.error('加载权限冲突失败')
  } finally {
    loading.value = false
  }
}

const loadConflictStats = async () => {
  try {
    const response = await permissionAuditApi.getConflictStats()
    conflictStats.value = response.data
  } catch (error) {
    console.error('加载冲突统计失败:', error)
  }
}

const loadPermissionHotspots = async () => {
  try {
    const response = await permissionAuditApi.getPermissionHotspots({ days: 30 })
    permissionHotspots.value = response.data.hotspots || []
  } catch (error) {
    console.error('加载权限热点失败:', error)
  }
}

const refreshData = () => {
  if (activeTab.value === 'logs') {
    loadAuditLogs()
  } else if (activeTab.value === 'conflicts') {
    loadConflicts()
    loadConflictStats()
  } else if (activeTab.value === 'stats') {
    loadPermissionHotspots()
  }
}

const resetLogFilters = () => {
  logFilters.action = ''
  logFilters.target_type = ''
  logFilters.dateRange = []
  loadAuditLogs()
}

const viewLogDetail = (log) => {
  selectedLog.value = log
  logDetailVisible.value = true
}

const resolveConflict = async (conflict) => {
  try {
    await ElMessageBox.prompt('请输入解决方案说明', '解决权限冲突', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      inputPlaceholder: '请输入解决方案说明'
    })
    
    await permissionAuditApi.resolveConflict(conflict.id, {
      strategy: 'manual',
      notes: '手动解决'
    })
    
    ElMessage.success('解决权限冲突成功')
    loadConflicts()
    loadConflictStats()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('解决权限冲突失败:', error)
      ElMessage.error('解决权限冲突失败')
    }
  }
}

const ignoreConflict = async (conflict) => {
  try {
    await ElMessageBox.confirm('确定要忽略此权限冲突吗？', '确认忽略', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    await permissionAuditApi.ignoreConflict(conflict.id, {
      notes: '手动忽略'
    })
    
    ElMessage.success('忽略权限冲突成功')
    loadConflicts()
    loadConflictStats()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('忽略权限冲突失败:', error)
      ElMessage.error('忽略权限冲突失败')
    }
  }
}

const viewConflictDetail = (conflict) => {
  ElMessage.info('查看冲突详情功能待实现')
}

const exportLogs = () => {
  ElMessage.info('导出日志功能待实现')
}

// 工具方法
const formatDateTime = (datetime) => {
  return dayjs(datetime).format('YYYY-MM-DD HH:mm:ss')
}

const getStatusTag = (status) => {
  const tagMap = {
    success: 'success',
    failed: 'danger',
    pending: 'warning'
  }
  return tagMap[status] || 'info'
}

const getStatusText = (status) => {
  const textMap = {
    success: '成功',
    failed: '失败',
    pending: '待处理'
  }
  return textMap[status] || status
}

const getPriorityTag = (priority) => {
  const tagMap = {
    high: 'danger',
    medium: 'warning',
    low: 'info'
  }
  return tagMap[priority] || 'info'
}

const getConflictStatusTag = (status) => {
  const tagMap = {
    unresolved: 'danger',
    resolved: 'success',
    ignored: 'info'
  }
  return tagMap[status] || 'info'
}

// 生命周期
onMounted(() => {
  loadAuditLogs()
  loadConflicts()
  loadConflictStats()
  loadPermissionHotspots()
})

// 暴露给父组件的方法
defineExpose({
  refreshData
})
</script>

<style scoped>
.permission-audit {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.audit-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #ebeef5;
}

.header-left h3 {
  margin: 0 0 5px 0;
  color: #303133;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.audit-tabs {
  flex: 1;
  padding: 20px;
}

.tab-content {
  height: 100%;
}

.filter-bar {
  margin-bottom: 20px;
  padding: 15px;
  background: #f5f7fa;
  border-radius: 4px;
}

.conflict-stats {
  margin-bottom: 20px;
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #303133;
}

.stat-value.danger {
  color: #f56c6c;
}

.stat-value.warning {
  color: #e6a23c;
}

.stat-value.success {
  color: #67c23a;
}

.stat-label {
  font-size: 14px;
  color: #909399;
  margin-top: 5px;
}

.conflict-filters {
  margin-bottom: 20px;
  padding: 15px;
  background: #f5f7fa;
  border-radius: 4px;
}

.pagination {
  margin-top: 20px;
  text-align: right;
}

.changes-summary {
  margin-top: 20px;
}

.changes-summary h4 {
  margin: 0 0 10px 0;
  color: #303133;
}

.stats-container {
  height: 100%;
}

.hotspot-list {
  max-height: 300px;
  overflow-y: auto;
}

.hotspot-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #ebeef5;
}

.hotspot-name {
  font-weight: 500;
  color: #303133;
}

.hotspot-count {
  color: #409eff;
  font-weight: bold;
}

.trend-placeholder {
  height: 300px;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
