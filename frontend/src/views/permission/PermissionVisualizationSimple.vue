<template>
  <div class="permission-visualization">
    <div class="page-header">
      <h1>权限可视化管理</h1>
      <p class="page-description">
        实验教学管理系统 - 五级权限层次可视化管理界面
      </p>
    </div>

    <el-tabs v-model="activeTab" type="card" class="permission-tabs">
      <!-- 权限继承关系 -->
      <el-tab-pane label="权限继承关系" name="inheritance">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>权限继承关系树</span>
              <el-button type="primary" size="small" @click="refreshInheritanceTree">
                <el-icon><Refresh /></el-icon>
                刷新
              </el-button>
            </div>
          </template>

          <div class="inheritance-demo">
            <div class="level-item level-1">
              <div class="level-box">
                <el-icon><OfficeBuilding /></el-icon>
                <span>省级权限</span>
                <el-tag size="small" type="success">河北省</el-tag>
              </div>
            </div>

            <div class="level-item level-2">
              <div class="level-box">
                <el-icon><OfficeBuilding /></el-icon>
                <span>市级权限</span>
                <el-tag size="small" type="primary">石家庄市</el-tag>
              </div>
            </div>

            <div class="level-item level-3">
              <div class="level-box">
                <el-icon><OfficeBuilding /></el-icon>
                <span>区县级权限</span>
                <el-tag size="small" type="warning">藁城区</el-tag>
              </div>
            </div>

            <div class="level-item level-4">
              <div class="level-box">
                <el-icon><School /></el-icon>
                <span>学区级权限</span>
                <el-tag size="small" type="info">廉州学区</el-tag>
              </div>
            </div>

            <div class="level-item level-5">
              <div class="level-box">
                <el-icon><School /></el-icon>
                <span>学校级权限</span>
                <el-tag size="small">东城小学</el-tag>
              </div>
            </div>
          </div>

          <div class="inheritance-info">
            <el-alert
              title="权限继承说明"
              type="info"
              :closable="false"
              show-icon>
              <p>• 下级组织自动继承上级组织的权限</p>
              <p>• 下级组织可以拥有额外的特定权限</p>
              <p>• 权限冲突时，以最具体的权限设置为准</p>
            </el-alert>
          </div>
        </el-card>
      </el-tab-pane>

      <!-- 权限矩阵管理 -->
      <el-tab-pane label="权限矩阵管理" name="matrix">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>用户权限矩阵</span>
              <div>
                <el-button type="success" size="small" @click="batchGrantPermissions">
                  <el-icon><Plus /></el-icon>
                  批量分配
                </el-button>
                <el-button type="danger" size="small" @click="batchRevokePermissions">
                  <el-icon><Minus /></el-icon>
                  批量撤销
                </el-button>
              </div>
            </div>
          </template>

          <el-table :data="permissionMatrix" border style="width: 100%">
            <el-table-column prop="user_name" label="用户" width="120" />
            <el-table-column prop="organization" label="所属组织" width="150" />
            <el-table-column label="权限">
              <el-table-column prop="view_permission" label="查看" width="80" align="center">
                <template #default="scope">
                  <el-switch v-model="scope.row.view_permission" @change="updatePermission(scope.row, 'view')" />
                </template>
              </el-table-column>
              <el-table-column prop="edit_permission" label="编辑" width="80" align="center">
                <template #default="scope">
                  <el-switch v-model="scope.row.edit_permission" @change="updatePermission(scope.row, 'edit')" />
                </template>
              </el-table-column>
              <el-table-column prop="delete_permission" label="删除" width="80" align="center">
                <template #default="scope">
                  <el-switch v-model="scope.row.delete_permission" @change="updatePermission(scope.row, 'delete')" />
                </template>
              </el-table-column>
              <el-table-column prop="admin_permission" label="管理" width="80" align="center">
                <template #default="scope">
                  <el-switch v-model="scope.row.admin_permission" @change="updatePermission(scope.row, 'admin')" />
                </template>
              </el-table-column>
            </el-table-column>
            <el-table-column label="操作" width="120" align="center">
              <template #default="scope">
                <el-button type="primary" size="small" @click="viewUserPermissions(scope.row)">详情</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>

      <!-- 权限审计 -->
      <el-tab-pane label="权限审计" name="audit">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>权限操作审计日志</span>
              <el-button type="primary" size="small" @click="exportAuditLogs">
                <el-icon><Download /></el-icon>
                导出日志
              </el-button>
            </div>
          </template>

          <div class="audit-filters">
            <el-row :gutter="20">
              <el-col :span="6">
                <el-date-picker
                  v-model="auditDateRange"
                  type="daterange"
                  range-separator="至"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  size="small"
                />
              </el-col>
              <el-col :span="4">
                <el-select v-model="auditActionFilter" placeholder="操作类型" size="small">
                  <el-option label="全部" value="" />
                  <el-option label="权限分配" value="grant" />
                  <el-option label="权限撤销" value="revoke" />
                  <el-option label="权限修改" value="update" />
                </el-select>
              </el-col>
              <el-col :span="4">
                <el-button type="primary" size="small" @click="searchAuditLogs">搜索</el-button>
              </el-col>
            </el-row>
          </div>

          <el-table :data="auditLogs" border style="width: 100%; margin-top: 20px;">
            <el-table-column prop="created_at" label="时间" width="180" />
            <el-table-column prop="operator" label="操作人" width="120" />
            <el-table-column prop="action" label="操作类型" width="100">
              <template #default="scope">
                <el-tag :type="getActionTagType(scope.row.action)" size="small">
                  {{ getActionText(scope.row.action) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="target_user" label="目标用户" width="120" />
            <el-table-column prop="permission_name" label="权限名称" width="150" />
            <el-table-column prop="description" label="操作描述" />
          </el-table>
        </el-card>
      </el-tab-pane>

      <!-- 权限统计 -->
      <el-tab-pane label="权限统计" name="analytics">
        <el-row :gutter="20">
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-item">
                <div class="stat-value">{{ stats.total_users }}</div>
                <div class="stat-label">总用户数</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-item">
                <div class="stat-value">{{ stats.total_permissions }}</div>
                <div class="stat-label">总权限数</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-item">
                <div class="stat-value">{{ stats.active_permissions }}</div>
                <div class="stat-label">活跃权限</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-item">
                <div class="stat-value">{{ stats.permission_conflicts }}</div>
                <div class="stat-label">权限冲突</div>
              </div>
            </el-card>
          </el-col>
        </el-row>

        <el-card style="margin-top: 20px;">
          <template #header>
            <span>权限分布统计</span>
          </template>
          <div class="chart-placeholder">
            <el-icon size="64" style="color: #ddd;"><DataAnalysis /></el-icon>
            <p style="color: #999; margin-top: 10px;">权限分布图表区域</p>
            <p style="color: #999; font-size: 12px;">（需要集成ECharts图表库）</p>
          </div>
        </el-card>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Refresh,
  OfficeBuilding,
  School,
  Plus,
  Minus,
  Download,
  DataAnalysis
} from '@element-plus/icons-vue'

// 响应式数据
const activeTab = ref('inheritance')
const auditDateRange = ref([])
const auditActionFilter = ref('')

// 权限矩阵数据
const permissionMatrix = ref([
  {
    id: 1,
    user_name: '张三',
    organization: '东城小学',
    view_permission: true,
    edit_permission: true,
    delete_permission: false,
    admin_permission: false
  },
  {
    id: 2,
    user_name: '李四',
    organization: '廉州学区',
    view_permission: true,
    edit_permission: true,
    delete_permission: true,
    admin_permission: false
  },
  {
    id: 3,
    user_name: '王五',
    organization: '藁城区',
    view_permission: true,
    edit_permission: true,
    delete_permission: true,
    admin_permission: true
  }
])

// 审计日志数据
const auditLogs = ref([
  {
    id: 1,
    created_at: '2024-01-15 10:30:00',
    operator: '系统管理员',
    action: 'grant',
    target_user: '张三',
    permission_name: '查看权限',
    description: '为用户张三分配查看权限'
  },
  {
    id: 2,
    created_at: '2024-01-15 11:15:00',
    operator: '系统管理员',
    action: 'revoke',
    target_user: '李四',
    permission_name: '删除权限',
    description: '撤销用户李四的删除权限'
  },
  {
    id: 3,
    created_at: '2024-01-15 14:20:00',
    operator: '系统管理员',
    action: 'update',
    target_user: '王五',
    permission_name: '管理权限',
    description: '更新用户王五的管理权限设置'
  }
])

// 统计数据
const stats = reactive({
  total_users: 156,
  total_permissions: 24,
  active_permissions: 18,
  permission_conflicts: 2
})

// 方法
const refreshInheritanceTree = () => {
  ElMessage.success('权限继承关系已刷新')
}

const updatePermission = (row, permissionType) => {
  ElMessage.success(`已更新用户 ${row.user_name} 的${permissionType}权限`)
}

const batchGrantPermissions = () => {
  ElMessage.success('批量权限分配功能')
}

const batchRevokePermissions = () => {
  ElMessage.warning('批量权限撤销功能')
}

const viewUserPermissions = (row) => {
  ElMessage.info(`查看用户 ${row.user_name} 的详细权限`)
}

const exportAuditLogs = () => {
  ElMessage.success('审计日志导出功能')
}

const searchAuditLogs = () => {
  ElMessage.success('搜索审计日志')
}

const getActionTagType = (action) => {
  const types = {
    grant: 'success',
    revoke: 'danger',
    update: 'warning'
  }
  return types[action] || 'info'
}

const getActionText = (action) => {
  const texts = {
    grant: '分配',
    revoke: '撤销',
    update: '修改'
  }
  return texts[action] || action
}

onMounted(() => {
  ElMessage.success('权限可视化管理系统已加载')
})
</script>

<style scoped>
.permission-visualization {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 28px;
  color: #303133;
  margin-bottom: 10px;
}

.page-description {
  font-size: 14px;
  color: #606266;
  margin: 0;
}

.permission-tabs {
  margin-top: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.inheritance-demo {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 20px;
}

.level-item {
  position: relative;
}

.level-item:not(:last-child)::after {
  content: '';
  position: absolute;
  bottom: -20px;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 20px;
  background: #409eff;
}

.level-box {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 15px 20px;
  border: 2px solid #409eff;
  border-radius: 8px;
  background: #f0f9ff;
  min-width: 200px;
  justify-content: center;
}

.inheritance-info {
  margin-top: 30px;
}

.audit-filters {
  margin-bottom: 20px;
}

.stat-card {
  text-align: center;
}

.stat-item {
  padding: 20px;
}

.stat-value {
  font-size: 32px;
  font-weight: bold;
  color: #409eff;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

.chart-placeholder {
  text-align: center;
  padding: 60px;
  background: #fafafa;
  border-radius: 8px;
}
</style>
