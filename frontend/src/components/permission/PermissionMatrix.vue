<template>
  <div class="permission-matrix">
    <!-- 工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-radio-group v-model="matrixType" @change="handleMatrixTypeChange">
          <el-radio-button label="user">用户-权限矩阵</el-radio-button>
          <el-radio-button label="role">角色-权限矩阵</el-radio-button>
          <el-radio-button label="organization">组织-权限矩阵</el-radio-button>
        </el-radio-group>
        
        <el-select
          v-if="matrixType === 'organization'"
          v-model="selectedOrganization"
          placeholder="选择组织机构"
          clearable
          filterable
          @change="loadMatrix"
          style="width: 200px; margin-left: 12px;"
        >
          <el-option
            v-for="org in organizations"
            :key="org.id"
            :label="org.name"
            :value="org.id"
          />
        </el-select>
        
        <el-select
          v-if="matrixType === 'user'"
          v-model="selectedUser"
          placeholder="选择用户"
          clearable
          filterable
          @change="loadMatrix"
          style="width: 200px; margin-left: 12px;"
        >
          <el-option
            v-for="user in users"
            :key="user.id"
            :label="user.name"
            :value="user.id"
          />
        </el-select>
        
        <el-select
          v-if="matrixType === 'role'"
          v-model="selectedRole"
          placeholder="选择角色"
          clearable
          filterable
          @change="loadMatrix"
          style="width: 200px; margin-left: 12px;"
        >
          <el-option
            v-for="role in roles"
            :key="role.id"
            :label="role.display_name"
            :value="role.id"
          />
        </el-select>
      </div>
      
      <div class="toolbar-right">
        <el-button
          type="primary"
          :icon="Plus"
          @click="showBatchAssignDialog = true"
          :disabled="!hasSelection"
        >
          批量分配
        </el-button>
        
        <el-button
          type="danger"
          :icon="Delete"
          @click="batchRevoke"
          :disabled="!hasSelection"
        >
          批量撤销
        </el-button>
        
        <el-button
          type="info"
          :icon="Refresh"
          @click="loadMatrix"
          :loading="loading"
        >
          刷新
        </el-button>
      </div>
    </div>

    <!-- 权限矩阵表格 -->
    <div class="matrix-container" v-loading="loading">
      <el-table
        ref="matrixTableRef"
        :data="matrixData"
        stripe
        border
        height="600"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        
        <el-table-column
          prop="subject_name"
          :label="getSubjectLabel()"
          width="200"
          fixed="left"
        >
          <template #default="{ row }">
            <div class="subject-cell">
              <el-avatar
                v-if="matrixType === 'user'"
                :size="24"
                :src="row.avatar"
                :alt="row.subject_name"
              />
              <el-icon v-else-if="matrixType === 'role'" :size="16">
                <UserFilled />
              </el-icon>
              <el-icon v-else :size="16">
                <OfficeBuilding />
              </el-icon>
              <span class="subject-name">{{ row.subject_name }}</span>
            </div>
          </template>
        </el-table-column>
        
        <!-- 动态权限列 -->
        <el-table-column
          v-for="permission in permissions"
          :key="permission.id"
          :prop="`permission_${permission.id}`"
          :label="permission.display_name"
          width="120"
          align="center"
        >
          <template #header>
            <div class="permission-header">
              <el-tooltip :content="permission.description" placement="top">
                <span class="permission-name">{{ permission.display_name }}</span>
              </el-tooltip>
              <el-tag
                :type="getPermissionLevelTag(permission.min_level)"
                size="small"
                class="level-tag"
              >
                L{{ permission.min_level }}
              </el-tag>
            </div>
          </template>
          
          <template #default="{ row }">
            <div class="permission-cell">
              <el-switch
                v-model="row[`permission_${permission.id}`]"
                :disabled="!canModifyPermission(row, permission)"
                @change="(value) => handlePermissionChange(row, permission, value)"
                :loading="row.loading"
              />
              
              <div class="permission-source" v-if="row[`source_${permission.id}`]">
                <el-tag
                  :type="getSourceTag(row[`source_${permission.id}`])"
                  size="small"
                >
                  {{ getSourceText(row[`source_${permission.id}`]) }}
                </el-tag>
              </div>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button
              type="text"
              size="small"
              @click="showEffectivePermissions(row)"
            >
              有效权限
            </el-button>
            <el-button
              type="text"
              size="small"
              @click="showPermissionHistory(row)"
            >
              历史记录
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 批量分配对话框 -->
    <el-dialog
      v-model="showBatchAssignDialog"
      title="批量权限分配"
      width="600px"
    >
      <div class="batch-assign-content">
        <div class="selected-subjects">
          <h4>选中的{{ getSubjectLabel() }}（{{ selectedRows.length }}个）</h4>
          <div class="subject-list">
            <el-tag
              v-for="row in selectedRows"
              :key="row.id"
              class="subject-tag"
            >
              {{ row.subject_name }}
            </el-tag>
          </div>
        </div>
        
        <div class="permission-selection">
          <h4>选择要分配的权限</h4>
          <el-transfer
            v-model="selectedPermissions"
            :data="transferPermissions"
            :titles="['可用权限', '已选权限']"
            filterable
            filter-placeholder="搜索权限"
          />
        </div>
        
        <div class="assign-options">
          <el-form :model="assignForm" label-width="100px">
            <el-form-item label="分配原因">
              <el-input
                v-model="assignForm.reason"
                type="textarea"
                placeholder="请输入分配原因（可选）"
                :rows="3"
              />
            </el-form-item>
            <el-form-item label="有效期">
              <el-date-picker
                v-model="assignForm.expires_at"
                type="datetime"
                placeholder="选择过期时间（可选）"
                format="YYYY-MM-DD HH:mm:ss"
                value-format="YYYY-MM-DD HH:mm:ss"
              />
            </el-form-item>
          </el-form>
        </div>
      </div>
      
      <template #footer>
        <el-button @click="showBatchAssignDialog = false">取消</el-button>
        <el-button
          type="primary"
          @click="confirmBatchAssign"
          :loading="assignLoading"
        >
          确认分配
        </el-button>
      </template>
    </el-dialog>

    <!-- 有效权限对话框 -->
    <el-dialog
      v-model="showEffectiveDialog"
      title="有效权限详情"
      width="800px"
    >
      <div class="effective-permissions" v-loading="effectiveLoading">
        <el-table :data="effectivePermissions" stripe>
          <el-table-column prop="permission_name" label="权限名称" />
          <el-table-column prop="source_type" label="来源类型" width="100">
            <template #default="{ row }">
              <el-tag :type="getSourceTag(row.source_type)">
                {{ getSourceText(row.source_type) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="source_name" label="来源" />
          <el-table-column prop="granted_at" label="授予时间" width="160" />
          <el-table-column prop="expires_at" label="过期时间" width="160">
            <template #default="{ row }">
              <span v-if="row.expires_at">{{ row.expires_at }}</span>
              <el-tag v-else type="success" size="small">永久</el-tag>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </el-dialog>

    <!-- 权限历史对话框 -->
    <el-dialog
      v-model="showHistoryDialog"
      title="权限变更历史"
      width="900px"
    >
      <div class="permission-history" v-loading="historyLoading">
        <el-table :data="permissionHistory" stripe>
          <el-table-column prop="action" label="操作" width="80">
            <template #default="{ row }">
              <el-tag :type="getActionTag(row.action)">
                {{ getActionText(row.action) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="permission_name" label="权限" />
          <el-table-column prop="operator_name" label="操作人" width="100" />
          <el-table-column prop="reason" label="原因" />
          <el-table-column prop="created_at" label="时间" width="160" />
        </el-table>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Plus,
  Delete,
  Refresh,
  UserFilled,
  OfficeBuilding
} from '@element-plus/icons-vue'
import { permissionVisualizationApi, permissionManagementApi } from '../../api/permissionVisualization'
import { organizationApi } from '../../api/organization'
import { userApi } from '../../api/user'
import { roleApi } from '../../api/role'
import { permissionApi } from '../../api/permission'

// 响应式数据
const loading = ref(false)
const assignLoading = ref(false)
const effectiveLoading = ref(false)
const historyLoading = ref(false)

const matrixType = ref('user')
const selectedOrganization = ref(null)
const selectedUser = ref(null)
const selectedRole = ref(null)

const matrixData = ref([])
const permissions = ref([])
const organizations = ref([])
const users = ref([])
const roles = ref([])

const selectedRows = ref([])
const matrixTableRef = ref(null)

// 对话框状态
const showBatchAssignDialog = ref(false)
const showEffectiveDialog = ref(false)
const showHistoryDialog = ref(false)

// 批量分配相关
const selectedPermissions = ref([])
const transferPermissions = ref([])
const assignForm = reactive({
  reason: '',
  expires_at: null
})

// 有效权限和历史记录
const effectivePermissions = ref([])
const permissionHistory = ref([])
const currentSubject = ref(null)

// 计算属性
const hasSelection = computed(() => selectedRows.value.length > 0)

// 组件挂载时初始化
onMounted(() => {
  loadBasicData()
  loadMatrix()
})

// 加载基础数据
const loadBasicData = async () => {
  try {
    const [orgRes, userRes, roleRes, permRes] = await Promise.all([
      organizationApi.getList(),
      userApi.getList(),
      roleApi.getList(),
      permissionApi.getList()
    ])
    
    organizations.value = orgRes.data.data || []
    users.value = userRes.data.data || []
    roles.value = roleRes.data.data || []
    permissions.value = permRes.data.data || []
    
    // 准备权限穿梭框数据
    transferPermissions.value = permissions.value.map(p => ({
      key: p.id,
      label: p.display_name,
      disabled: false
    }))
  } catch (error) {
    console.error('加载基础数据失败:', error)
    ElMessage.error('加载基础数据失败')
  }
}

// 处理矩阵类型变更
const handleMatrixTypeChange = () => {
  selectedOrganization.value = null
  selectedUser.value = null
  selectedRole.value = null
  selectedRows.value = []
  loadMatrix()
}

// 加载权限矩阵
const loadMatrix = async () => {
  loading.value = true
  try {
    const params = {}
    
    if (matrixType.value === 'organization' && selectedOrganization.value) {
      params.organization_id = selectedOrganization.value
    } else if (matrixType.value === 'user' && selectedUser.value) {
      params.user_id = selectedUser.value
    } else if (matrixType.value === 'role' && selectedRole.value) {
      params.role_id = selectedRole.value
    }
    
    const response = await permissionVisualizationApi.getPermissionMatrix(params)
    const data = response.data.data || {}
    
    matrixData.value = transformMatrixData(data.matrix || [], data.subjects || [])
  } catch (error) {
    console.error('加载权限矩阵失败:', error)
    ElMessage.error('加载权限矩阵失败')
  } finally {
    loading.value = false
  }
}

// 转换矩阵数据格式
const transformMatrixData = (matrix, subjects) => {
  return subjects.map(subject => {
    const row = {
      id: subject.id,
      subject_name: subject.name || subject.display_name,
      avatar: subject.avatar,
      loading: false
    }
    
    // 为每个权限添加对应的字段
    permissions.value.forEach(permission => {
      const permissionKey = `permission_${permission.id}`
      const sourceKey = `source_${permission.id}`
      
      // 查找该主体是否有此权限
      const hasPermission = matrix.some(item => 
        item.subject_id === subject.id && item.permission_id === permission.id
      )
      
      const permissionItem = matrix.find(item => 
        item.subject_id === subject.id && item.permission_id === permission.id
      )
      
      row[permissionKey] = hasPermission
      row[sourceKey] = permissionItem?.source_type || null
    })
    
    return row
  })
}

// 处理选择变更
const handleSelectionChange = (selection) => {
  selectedRows.value = selection
}

// 处理权限变更
const handlePermissionChange = async (row, permission, value) => {
  row.loading = true
  
  try {
    if (value) {
      // 分配权限
      await grantPermission(row, permission)
    } else {
      // 撤销权限
      await revokePermission(row, permission)
    }
    
    ElMessage.success(value ? '权限分配成功' : '权限撤销成功')
  } catch (error) {
    console.error('权限操作失败:', error)
    ElMessage.error('权限操作失败')
    // 恢复原状态
    row[`permission_${permission.id}`] = !value
  } finally {
    row.loading = false
  }
}

// 分配权限
const grantPermission = async (row, permission) => {
  const data = {
    subject_type: matrixType.value,
    subject_id: row.id,
    permission_id: permission.id,
    organization_id: selectedOrganization.value,
    reason: '通过权限矩阵分配'
  }

  await permissionManagementApi.grantPermission(data)
}

// 撤销权限
const revokePermission = async (row, permission) => {
  const data = {
    subject_type: matrixType.value,
    subject_id: row.id,
    permission_id: permission.id,
    organization_id: selectedOrganization.value,
    reason: '通过权限矩阵撤销'
  }

  await permissionManagementApi.revokePermission(data)
}

// 批量撤销权限
const batchRevoke = async () => {
  try {
    const result = await ElMessageBox.prompt(
      `确定要撤销选中的 ${selectedRows.value.length} 个${getSubjectLabel()}的所有权限吗？请输入撤销原因：`,
      '确认撤销',
      {
        confirmButtonText: '确定撤销',
        cancelButtonText: '取消',
        inputPattern: /.+/,
        inputErrorMessage: '请输入撤销原因',
        type: 'warning'
      }
    )

    const reason = result.value
    const subjectIds = selectedRows.value.map(row => row.id)
    const permissionIds = permissions.value.map(p => p.id)

    const data = {
      subject_type: matrixType.value,
      subject_ids: subjectIds,
      permission_ids: permissionIds,
      organization_id: selectedOrganization.value,
      reason: reason
    }

    await permissionManagementApi.batchRevokePermissions(data)
    ElMessage.success('批量撤销成功')
    loadMatrix()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('批量撤销失败:', error)
      ElMessage.error('批量撤销失败')
    }
  }
}

// 确认批量分配
const confirmBatchAssign = async () => {
  if (selectedPermissions.value.length === 0) {
    ElMessage.warning('请选择要分配的权限')
    return
  }

  assignLoading.value = true
  try {
    const data = {
      subject_type: matrixType.value,
      subject_ids: selectedRows.value.map(row => row.id),
      permission_ids: selectedPermissions.value,
      organization_id: selectedOrganization.value,
      reason: assignForm.reason,
      expires_at: assignForm.expires_at
    }

    await permissionManagementApi.batchGrantPermissions(data)

    ElMessage.success('批量分配成功')
    showBatchAssignDialog.value = false
    loadMatrix()

    // 重置表单
    selectedPermissions.value = []
    Object.assign(assignForm, {
      reason: '',
      expires_at: null
    })
  } catch (error) {
    console.error('批量分配失败:', error)
    ElMessage.error('批量分配失败')
  } finally {
    assignLoading.value = false
  }
}

// 显示有效权限
const showEffectivePermissions = async (row) => {
  currentSubject.value = row
  effectiveLoading.value = true
  showEffectiveDialog.value = true
  
  try {
    const response = await permissionVisualizationApi.calculateEffectivePermissions({
      user_id: matrixType.value === 'user' ? row.id : null,
      organization_id: matrixType.value === 'organization' ? row.id : null
    })
    
    effectivePermissions.value = response.data.data.effective_permissions || []
  } catch (error) {
    console.error('获取有效权限失败:', error)
    ElMessage.error('获取有效权限失败')
  } finally {
    effectiveLoading.value = false
  }
}

// 显示权限历史
const showPermissionHistory = async (row) => {
  currentSubject.value = row
  historyLoading.value = true
  showHistoryDialog.value = true
  
  try {
    // 这里调用获取权限历史的API
    // const response = await permissionApi.getHistory({ subject_id: row.id })
    // permissionHistory.value = response.data.data || []
    permissionHistory.value = [] // 临时空数据
  } catch (error) {
    console.error('获取权限历史失败:', error)
    ElMessage.error('获取权限历史失败')
  } finally {
    historyLoading.value = false
  }
}

// 检查是否可以修改权限
const canModifyPermission = (row, permission) => {
  // 这里可以添加权限修改的业务逻辑判断
  return true
}

// 获取主体标签
const getSubjectLabel = () => {
  const labelMap = {
    'user': '用户',
    'role': '角色',
    'organization': '组织'
  }
  return labelMap[matrixType.value] || '主体'
}

// 获取权限级别标签类型
const getPermissionLevelTag = (level) => {
  if (level <= 2) return 'danger'
  if (level <= 3) return 'warning'
  return 'success'
}

// 获取来源标签类型
const getSourceTag = (source) => {
  const tagMap = {
    'direct': 'primary',
    'role': 'success',
    'inheritance': 'info',
    'template': 'warning'
  }
  return tagMap[source] || 'default'
}

// 获取来源文本
const getSourceText = (source) => {
  const textMap = {
    'direct': '直接',
    'role': '角色',
    'inheritance': '继承',
    'template': '模板'
  }
  return textMap[source] || source
}

// 获取操作标签类型
const getActionTag = (action) => {
  const tagMap = {
    'grant': 'success',
    'revoke': 'danger',
    'update': 'warning'
  }
  return tagMap[action] || 'default'
}

// 获取操作文本
const getActionText = (action) => {
  const textMap = {
    'grant': '分配',
    'revoke': '撤销',
    'update': '更新'
  }
  return textMap[action] || action
}
</script>

<style scoped>
.permission-matrix {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #f5f7fa;
  border-radius: 4px;
  margin-bottom: 16px;
}

.toolbar-left {
  display: flex;
  align-items: center;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.matrix-container {
  flex: 1;
  overflow: hidden;
}

.subject-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.subject-name {
  font-weight: 500;
}

.permission-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.permission-name {
  font-size: 12px;
  text-align: center;
  line-height: 1.2;
}

.level-tag {
  font-size: 10px;
}

.permission-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.permission-source {
  font-size: 10px;
}

.batch-assign-content {
  max-height: 500px;
  overflow-y: auto;
}

.selected-subjects {
  margin-bottom: 20px;
}

.selected-subjects h4 {
  margin: 0 0 8px 0;
  color: #303133;
}

.subject-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.subject-tag {
  margin: 0;
}

.permission-selection {
  margin-bottom: 20px;
}

.permission-selection h4 {
  margin: 0 0 12px 0;
  color: #303133;
}

.assign-options {
  border-top: 1px solid #e4e7ed;
  padding-top: 16px;
}

.effective-permissions,
.permission-history {
  max-height: 400px;
  overflow-y: auto;
}
</style>
