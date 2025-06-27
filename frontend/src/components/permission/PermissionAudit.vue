<template>
  <div class="permission-audit">
    <!-- 搜索和筛选工具栏 -->
    <div class="search-toolbar">
      <el-form :model="searchForm" inline class="search-form">
        <el-form-item label="操作类型">
          <el-select
            v-model="searchForm.action"
            placeholder="选择操作类型"
            clearable
            style="width: 150px"
          >
            <el-option label="权限分配" value="grant" />
            <el-option label="权限撤销" value="revoke" />
            <el-option label="权限更新" value="update" />
            <el-option label="权限继承" value="inherit" />
            <el-option label="权限覆盖" value="override" />
            <el-option label="模板应用" value="template_apply" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="主体类型">
          <el-select
            v-model="searchForm.subject_type"
            placeholder="选择主体类型"
            clearable
            style="width: 120px"
          >
            <el-option label="用户" value="user" />
            <el-option label="角色" value="role" />
            <el-option label="组织" value="organization" />
            <el-option label="模板" value="template" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="操作人">
          <el-select
            v-model="searchForm.user_id"
            placeholder="选择操作人"
            clearable
            filterable
            style="width: 150px"
          >
            <el-option
              v-for="user in users"
              :key="user.id"
              :label="user.name"
              :value="user.id"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="组织机构">
          <el-select
            v-model="searchForm.organization_id"
            placeholder="选择组织机构"
            clearable
            filterable
            style="width: 180px"
          >
            <el-option
              v-for="org in organizations"
              :key="org.id"
              :label="org.name"
              :value="org.id"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="时间范围">
          <el-date-picker
            v-model="searchForm.date_range"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            format="YYYY-MM-DD HH:mm:ss"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 350px"
          />
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" :icon="Search" @click="searchAuditLogs">
            搜索
          </el-button>
          <el-button :icon="Refresh" @click="resetSearch">
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 统计信息 -->
    <div class="stats-cards">
      <el-row :gutter="16">
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-value">{{ stats.total_logs }}</div>
              <div class="stat-label">总操作数</div>
            </div>
            <el-icon class="stat-icon"><Document /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card grant">
            <div class="stat-content">
              <div class="stat-value">{{ stats.grant_count }}</div>
              <div class="stat-label">权限分配</div>
            </div>
            <el-icon class="stat-icon"><Plus /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card revoke">
            <div class="stat-content">
              <div class="stat-value">{{ stats.revoke_count }}</div>
              <div class="stat-label">权限撤销</div>
            </div>
            <el-icon class="stat-icon"><Minus /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card conflict">
            <div class="stat-content">
              <div class="stat-value">{{ stats.conflict_count }}</div>
              <div class="stat-label">权限冲突</div>
            </div>
            <el-icon class="stat-icon"><Warning /></el-icon>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 审计日志表格 -->
    <div class="audit-table-container">
      <el-table
        v-loading="loading"
        :data="auditLogs"
        stripe
        border
        height="500"
        @row-click="showLogDetail"
      >
        <el-table-column prop="created_at" label="时间" width="160" sortable />
        
        <el-table-column prop="action" label="操作" width="100">
          <template #default="{ row }">
            <el-tag :type="getActionTagType(row.action)">
              {{ getActionText(row.action) }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="subject_type" label="主体类型" width="100">
          <template #default="{ row }">
            <el-tag :type="getSubjectTagType(row.subject_type)" size="small">
              {{ getSubjectText(row.subject_type) }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="target_name" label="目标对象" width="150">
          <template #default="{ row }">
            <div class="target-info">
              <el-icon><component :is="getSubjectIcon(row.subject_type)" /></el-icon>
              <span>{{ row.target_name || '未知' }}</span>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column prop="permission_name" label="权限" width="180" />
        
        <el-table-column prop="operator_name" label="操作人" width="120">
          <template #default="{ row }">
            <div class="operator-info">
              <el-avatar :size="24" :src="row.operator_avatar" />
              <span>{{ row.operator_name || '系统' }}</span>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column prop="result" label="结果" width="80">
          <template #default="{ row }">
            <el-tag :type="getResultTagType(row.result)" size="small">
              {{ getResultText(row.result) }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="reason" label="原因" min-width="200" show-overflow-tooltip />
        
        <el-table-column prop="ip_address" label="IP地址" width="120" />
        
        <el-table-column label="操作" width="100">
          <template #default="{ row }">
            <el-button type="text" size="small" @click.stop="showLogDetail(row)">
              详情
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="pagination.current_page"
          v-model:page-size="pagination.per_page"
          :total="pagination.total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </div>

    <!-- 日志详情对话框 -->
    <el-dialog
      v-model="showDetailDialog"
      title="审计日志详情"
      width="800px"
    >
      <div class="log-detail" v-if="selectedLog">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="操作时间">
            {{ selectedLog.created_at }}
          </el-descriptions-item>
          <el-descriptions-item label="操作类型">
            <el-tag :type="getActionTagType(selectedLog.action)">
              {{ getActionText(selectedLog.action) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="主体类型">
            <el-tag :type="getSubjectTagType(selectedLog.subject_type)">
              {{ getSubjectText(selectedLog.subject_type) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="目标对象">
            {{ selectedLog.target_name }}
          </el-descriptions-item>
          <el-descriptions-item label="权限名称">
            {{ selectedLog.permission_name }}
          </el-descriptions-item>
          <el-descriptions-item label="操作人">
            {{ selectedLog.operator_name || '系统' }}
          </el-descriptions-item>
          <el-descriptions-item label="操作结果">
            <el-tag :type="getResultTagType(selectedLog.result)">
              {{ getResultText(selectedLog.result) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="IP地址">
            {{ selectedLog.ip_address }}
          </el-descriptions-item>
          <el-descriptions-item label="用户代理" span="2">
            {{ selectedLog.user_agent }}
          </el-descriptions-item>
          <el-descriptions-item label="操作原因" span="2">
            {{ selectedLog.reason || '无' }}
          </el-descriptions-item>
          <el-descriptions-item label="错误信息" span="2" v-if="selectedLog.error_message">
            <el-text type="danger">{{ selectedLog.error_message }}</el-text>
          </el-descriptions-item>
        </el-descriptions>
        
        <!-- 变更详情 -->
        <div class="change-details" v-if="selectedLog.old_values || selectedLog.new_values">
          <h4>变更详情</h4>
          <el-row :gutter="16">
            <el-col :span="12" v-if="selectedLog.old_values">
              <h5>变更前</h5>
              <el-input
                type="textarea"
                :value="formatJson(selectedLog.old_values)"
                readonly
                :rows="6"
              />
            </el-col>
            <el-col :span="12" v-if="selectedLog.new_values">
              <h5>变更后</h5>
              <el-input
                type="textarea"
                :value="formatJson(selectedLog.new_values)"
                readonly
                :rows="6"
              />
            </el-col>
          </el-row>
        </div>
        
        <!-- 上下文信息 -->
        <div class="context-info" v-if="selectedLog.context">
          <h4>上下文信息</h4>
          <el-input
            type="textarea"
            :value="formatJson(selectedLog.context)"
            readonly
            :rows="4"
          />
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import {
  Search,
  Refresh,
  Document,
  Plus,
  Minus,
  Warning,
  User,
  UserFilled,
  OfficeBuilding,
  Files
} from '@element-plus/icons-vue'
import { permissionAuditApi } from '../../api/permissionVisualization'
import { organizationApi } from '../../api/organization'
import { userApi } from '../../api/user'

// 响应式数据
const loading = ref(false)
const auditLogs = ref([])
const users = ref([])
const organizations = ref([])
const selectedLog = ref(null)
const showDetailDialog = ref(false)

// 搜索表单
const searchForm = reactive({
  action: '',
  subject_type: '',
  user_id: null,
  organization_id: null,
  date_range: null
})

// 统计数据
const stats = reactive({
  total_logs: 0,
  grant_count: 0,
  revoke_count: 0,
  conflict_count: 0
})

// 分页数据
const pagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0
})

// 组件挂载时初始化
onMounted(() => {
  loadBasicData()
  loadAuditLogs()
  loadStats()
})

// 加载基础数据
const loadBasicData = async () => {
  try {
    const [userRes, orgRes] = await Promise.all([
      userApi.getList(),
      organizationApi.getList()
    ])
    
    users.value = userRes.data.data || []
    organizations.value = orgRes.data.data || []
  } catch (error) {
    console.error('加载基础数据失败:', error)
    ElMessage.error('加载基础数据失败')
  }
}

// 加载审计日志
const loadAuditLogs = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      per_page: pagination.per_page,
      ...searchForm
    }
    
    // 处理时间范围
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_time = searchForm.date_range[0]
      params.end_time = searchForm.date_range[1]
    }
    
    const response = await permissionAuditApi.getLogs(params)
    const data = response.data.data || {}
    
    auditLogs.value = data.data || []
    pagination.total = data.total || 0
    pagination.current_page = data.current_page || 1
    pagination.per_page = data.per_page || 20
  } catch (error) {
    console.error('加载审计日志失败:', error)
    ElMessage.error('加载审计日志失败')
  } finally {
    loading.value = false
  }
}

// 加载统计数据
const loadStats = async () => {
  try {
    const response = await permissionAuditApi.getUserStats()
    const data = response.data.data || {}
    
    Object.assign(stats, data)
  } catch (error) {
    console.error('加载统计数据失败:', error)
  }
}

// 搜索审计日志
const searchAuditLogs = () => {
  pagination.current_page = 1
  loadAuditLogs()
}

// 重置搜索
const resetSearch = () => {
  Object.assign(searchForm, {
    action: '',
    subject_type: '',
    user_id: null,
    organization_id: null,
    date_range: null
  })
  pagination.current_page = 1
  loadAuditLogs()
}

// 处理页面大小变更
const handleSizeChange = (size) => {
  pagination.per_page = size
  pagination.current_page = 1
  loadAuditLogs()
}

// 处理当前页变更
const handleCurrentChange = (page) => {
  pagination.current_page = page
  loadAuditLogs()
}

// 显示日志详情
const showLogDetail = async (row) => {
  try {
    const response = await permissionAuditApi.getLogDetail(row.id)
    selectedLog.value = response.data.data
    showDetailDialog.value = true
  } catch (error) {
    console.error('获取日志详情失败:', error)
    ElMessage.error('获取日志详情失败')
  }
}

// 获取操作标签类型
const getActionTagType = (action) => {
  const typeMap = {
    'grant': 'success',
    'revoke': 'danger',
    'update': 'warning',
    'inherit': 'info',
    'override': 'warning',
    'template_apply': 'primary'
  }
  return typeMap[action] || 'default'
}

// 获取操作文本
const getActionText = (action) => {
  const textMap = {
    'grant': '分配',
    'revoke': '撤销',
    'update': '更新',
    'inherit': '继承',
    'override': '覆盖',
    'template_apply': '模板应用'
  }
  return textMap[action] || action
}

// 获取主体标签类型
const getSubjectTagType = (subjectType) => {
  const typeMap = {
    'user': 'primary',
    'role': 'success',
    'organization': 'info',
    'template': 'warning'
  }
  return typeMap[subjectType] || 'default'
}

// 获取主体文本
const getSubjectText = (subjectType) => {
  const textMap = {
    'user': '用户',
    'role': '角色',
    'organization': '组织',
    'template': '模板'
  }
  return textMap[subjectType] || subjectType
}

// 获取主体图标
const getSubjectIcon = (subjectType) => {
  const iconMap = {
    'user': User,
    'role': UserFilled,
    'organization': OfficeBuilding,
    'template': Files
  }
  return iconMap[subjectType] || User
}

// 获取结果标签类型
const getResultTagType = (result) => {
  const typeMap = {
    'success': 'success',
    'failed': 'danger',
    'partial': 'warning'
  }
  return typeMap[result] || 'default'
}

// 获取结果文本
const getResultText = (result) => {
  const textMap = {
    'success': '成功',
    'failed': '失败',
    'partial': '部分成功'
  }
  return textMap[result] || result
}

// 格式化JSON
const formatJson = (obj) => {
  if (typeof obj === 'string') {
    try {
      obj = JSON.parse(obj)
    } catch (e) {
      return obj
    }
  }
  return JSON.stringify(obj, null, 2)
}
</script>

<style scoped>
.permission-audit {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.search-toolbar {
  padding: 16px;
  background: #f5f7fa;
  border-radius: 4px;
  margin-bottom: 16px;
}

.search-form {
  width: 100%;
}

.stats-cards {
  margin-bottom: 16px;
}

.stat-card {
  position: relative;
  overflow: hidden;
}

.stat-card.grant {
  border-left: 4px solid #67c23a;
}

.stat-card.revoke {
  border-left: 4px solid #f56c6c;
}

.stat-card.conflict {
  border-left: 4px solid #e6a23c;
}

.stat-content {
  position: relative;
  z-index: 2;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #303133;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

.stat-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 32px;
  color: #dcdfe6;
  z-index: 1;
}

.audit-table-container {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.target-info,
.operator-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-container {
  padding: 16px;
  text-align: right;
  border-top: 1px solid #e4e7ed;
}

.log-detail {
  max-height: 600px;
  overflow-y: auto;
}

.change-details,
.context-info {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e4e7ed;
}

.change-details h4,
.context-info h4 {
  margin: 0 0 12px 0;
  color: #303133;
}

.change-details h5 {
  margin: 0 0 8px 0;
  color: #606266;
  font-size: 14px;
}
</style>
