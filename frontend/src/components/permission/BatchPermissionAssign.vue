<template>
  <el-dialog
    v-model="visible"
    title="批量权限分配"
    width="900px"
    :close-on-click-modal="false"
  >
    <div class="batch-permission-assign">
      <!-- 操作类型选择 -->
      <div class="operation-type">
        <el-radio-group v-model="operationType" @change="handleOperationChange">
          <el-radio-button label="assign">批量分配权限</el-radio-button>
          <el-radio-button label="revoke">批量撤销权限</el-radio-button>
          <el-radio-button label="template">应用权限模板</el-radio-button>
        </el-radio-group>
      </div>

      <el-row :gutter="20">
        <!-- 左侧：目标选择 -->
        <el-col :span="12">
          <el-card class="target-selection">
            <template #header>
              <div class="card-header">
                <span>选择目标{{ getTargetLabel() }}</span>
                <el-button type="text" @click="selectAll">全选</el-button>
              </div>
            </template>
            
            <!-- 筛选条件 -->
            <div class="filters">
              <el-select
                v-model="filters.organizationId"
                placeholder="选择组织"
                clearable
                @change="loadTargets"
                style="width: 100%; margin-bottom: 12px;"
              >
                <el-option
                  v-for="org in organizations"
                  :key="org.id"
                  :label="org.name"
                  :value="org.id"
                />
              </el-select>
              
              <el-select
                v-if="operationType !== 'template'"
                v-model="filters.roleId"
                placeholder="按角色筛选"
                clearable
                @change="loadTargets"
                style="width: 100%; margin-bottom: 12px;"
              >
                <el-option
                  v-for="role in roles"
                  :key="role.id"
                  :label="role.display_name"
                  :value="role.id"
                />
              </el-select>
              
              <el-input
                v-model="filters.keyword"
                placeholder="搜索用户名或姓名"
                @input="handleSearch"
                style="width: 100%;"
              >
                <template #prefix>
                  <el-icon><Search /></el-icon>
                </template>
              </el-input>
            </div>

            <!-- 目标列表 -->
            <div class="target-list">
              <el-checkbox-group v-model="selectedTargets">
                <div
                  v-for="target in filteredTargets"
                  :key="target.id"
                  class="target-item"
                >
                  <el-checkbox :label="target.id">
                    <div class="target-info">
                      <div class="target-name">{{ target.name }}</div>
                      <div class="target-meta">
                        <el-tag size="small" type="info">{{ target.organization_name }}</el-tag>
                        <el-tag v-if="target.role_name" size="small" type="success">
                          {{ target.role_name }}
                        </el-tag>
                      </div>
                    </div>
                  </el-checkbox>
                </div>
              </el-checkbox-group>
            </div>
          </el-card>
        </el-col>

        <!-- 右侧：权限/模板选择 -->
        <el-col :span="12">
          <el-card class="permission-selection">
            <template #header>
              <span>{{ getSelectionTitle() }}</span>
            </template>
            
            <!-- 权限模板选择 -->
            <div v-if="operationType === 'template'" class="template-selection">
              <el-radio-group v-model="selectedTemplate" direction="vertical">
                <el-radio
                  v-for="template in permissionTemplates"
                  :key="template.id"
                  :label="template.id"
                  class="template-radio"
                >
                  <div class="template-info">
                    <div class="template-name">{{ template.name }}</div>
                    <div class="template-desc">{{ template.description }}</div>
                    <div class="template-permissions">
                      包含 {{ template.permission_count }} 个权限
                    </div>
                  </div>
                </el-radio>
              </el-radio-group>
            </div>
            
            <!-- 权限树选择 -->
            <div v-else class="permission-tree-selection">
              <div class="tree-actions">
                <el-button type="text" @click="expandAll">展开全部</el-button>
                <el-button type="text" @click="collapseAll">收起全部</el-button>
                <el-button type="text" @click="checkAll">全选</el-button>
                <el-button type="text" @click="uncheckAll">取消全选</el-button>
              </div>
              
              <el-tree
                ref="permissionTreeRef"
                :data="permissionTree"
                :props="permissionTreeProps"
                show-checkbox
                node-key="id"
                :default-expand-all="false"
                @check="handlePermissionCheck"
              >
                <template #default="{ node, data }">
                  <div class="permission-node">
                    <span>{{ data.display_name }}</span>
                    <el-tag v-if="data.is_system" type="warning" size="small">
                      系统
                    </el-tag>
                  </div>
                </template>
              </el-tree>
            </div>
          </el-card>
        </el-col>
      </el-row>

      <!-- 操作原因 -->
      <div class="operation-reason">
        <el-form-item label="操作原因" required>
          <el-input
            v-model="operationReason"
            type="textarea"
            :placeholder="`请输入${getOperationLabel()}的原因`"
            :rows="3"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
      </div>

      <!-- 操作预览 -->
      <div v-if="selectedTargets.length && (selectedPermissions.length || selectedTemplate)" class="operation-preview">
        <h4>操作预览</h4>
        <div class="preview-content">
          <p>
            将对 <strong>{{ selectedTargets.length }}</strong> 个用户
            {{ getOperationLabel() }}
            <strong>{{ getPermissionCount() }}</strong> 个权限
          </p>
          <div class="preview-details">
            <el-collapse>
              <el-collapse-item title="查看详细信息" name="details">
                <div class="detail-section">
                  <h5>目标用户：</h5>
                  <el-tag
                    v-for="targetId in selectedTargets.slice(0, 10)"
                    :key="targetId"
                    size="small"
                    class="target-tag"
                  >
                    {{ getTargetName(targetId) }}
                  </el-tag>
                  <span v-if="selectedTargets.length > 10" class="more-count">
                    等 {{ selectedTargets.length }} 个用户
                  </span>
                </div>
                
                <div v-if="operationType !== 'template'" class="detail-section">
                  <h5>权限列表：</h5>
                  <el-tag
                    v-for="permId in selectedPermissions.slice(0, 10)"
                    :key="permId"
                    size="small"
                    type="success"
                    class="permission-tag"
                  >
                    {{ getPermissionName(permId) }}
                  </el-tag>
                  <span v-if="selectedPermissions.length > 10" class="more-count">
                    等 {{ selectedPermissions.length }} 个权限
                  </span>
                </div>
              </el-collapse-item>
            </el-collapse>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="visible = false">取消</el-button>
        <el-button
          type="primary"
          @click="executeOperation"
          :loading="executing"
          :disabled="!canExecute"
        >
          {{ getOperationLabel() }}
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search } from '@element-plus/icons-vue'

// Props & Emits
const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'success'])

// 响应式数据
const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const operationType = ref('assign')
const executing = ref(false)
const selectedTargets = ref([])
const selectedPermissions = ref([])
const selectedTemplate = ref(null)
const operationReason = ref('')

// 数据
const organizations = ref([])
const roles = ref([])
const targets = ref([])
const permissionTree = ref([])
const permissionTemplates = ref([])

// 筛选条件
const filters = reactive({
  organizationId: null,
  roleId: null,
  keyword: ''
})

// 树形配置
const permissionTreeProps = {
  children: 'children',
  label: 'display_name'
}

// 计算属性
const filteredTargets = computed(() => {
  let result = targets.value
  
  if (filters.keyword) {
    const keyword = filters.keyword.toLowerCase()
    result = result.filter(target => 
      target.name.toLowerCase().includes(keyword) ||
      target.username.toLowerCase().includes(keyword)
    )
  }
  
  return result
})

const canExecute = computed(() => {
  return selectedTargets.value.length > 0 &&
         (selectedPermissions.value.length > 0 || selectedTemplate.value) &&
         operationReason.value.trim() !== ''
})

// 方法
const getTargetLabel = () => {
  return '用户'
}

const getSelectionTitle = () => {
  const titles = {
    assign: '选择要分配的权限',
    revoke: '选择要撤销的权限',
    template: '选择权限模板'
  }
  return titles[operationType.value]
}

const getOperationLabel = () => {
  const labels = {
    assign: '分配权限',
    revoke: '撤销权限',
    template: '应用模板'
  }
  return labels[operationType.value]
}

const getPermissionCount = () => {
  if (operationType.value === 'template' && selectedTemplate.value) {
    const template = permissionTemplates.value.find(t => t.id === selectedTemplate.value)
    return template ? template.permission_count : 0
  }
  return selectedPermissions.value.length
}

const getTargetName = (targetId) => {
  const target = targets.value.find(t => t.id === targetId)
  return target ? target.name : ''
}

const getPermissionName = (permissionId) => {
  // 递归查找权限名称
  const findPermission = (permissions, id) => {
    for (const perm of permissions) {
      if (perm.id === id) return perm.display_name
      if (perm.children) {
        const found = findPermission(perm.children, id)
        if (found) return found
      }
    }
    return ''
  }
  return findPermission(permissionTree.value, permissionId)
}

const handleOperationChange = () => {
  selectedPermissions.value = []
  selectedTemplate.value = null
  operationReason.value = ''
}

const loadTargets = async () => {
  // 根据筛选条件加载目标用户
  // 模拟数据
  targets.value = [
    {
      id: 1,
      name: '张校长',
      username: 'zhang_principal',
      organization_name: '东城小学',
      role_name: '校长'
    },
    {
      id: 2,
      name: '李老师',
      username: 'li_teacher',
      organization_name: '东城小学',
      role_name: '教师'
    }
  ]
}

const handleSearch = () => {
  // 搜索逻辑已在计算属性中实现
}

const selectAll = () => {
  selectedTargets.value = filteredTargets.value.map(t => t.id)
}

const handlePermissionCheck = (data, checked) => {
  selectedPermissions.value = checked.checkedKeys
}

const expandAll = () => {
  // 展开所有节点
}

const collapseAll = () => {
  // 收起所有节点
}

const checkAll = () => {
  // 全选权限
}

const uncheckAll = () => {
  // 取消全选权限
}

const executeOperation = async () => {
  try {
    await ElMessageBox.confirm(
      `确定要对 ${selectedTargets.value.length} 个用户${getOperationLabel()}吗？`,
      '确认操作',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    executing.value = true
    
    const operationData = {
      operation_type: operationType.value,
      target_ids: selectedTargets.value,
      permission_ids: selectedPermissions.value,
      template_id: selectedTemplate.value,
      reason: operationReason.value
    }
    
    // 调用API执行批量操作
    // await permissionApi.batchOperation(operationData)
    
    ElMessage.success(`${getOperationLabel()}成功`)
    emit('success')
    visible.value = false
    
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(`${getOperationLabel()}失败`)
    }
  } finally {
    executing.value = false
  }
}

// 监听对话框打开
watch(visible, (val) => {
  if (val) {
    loadData()
  }
})

const loadData = async () => {
  // 加载基础数据
  organizations.value = [
    { id: 1, name: '廉州学区' },
    { id: 2, name: '东城小学' },
    { id: 3, name: '西城小学' }
  ]
  
  roles.value = [
    { id: 1, display_name: '校长' },
    { id: 2, display_name: '教师' },
    { id: 3, display_name: '管理员' }
  ]
  
  permissionTemplates.value = [
    {
      id: 1,
      name: '学校管理员模板',
      description: '适用于学校管理员的权限模板',
      permission_count: 15
    },
    {
      id: 2,
      name: '教师模板',
      description: '适用于普通教师的权限模板',
      permission_count: 8
    }
  ]
  
  permissionTree.value = [
    {
      id: 1,
      display_name: '用户管理',
      children: [
        { id: 2, display_name: '查看用户' },
        { id: 3, display_name: '创建用户' }
      ]
    }
  ]
  
  loadTargets()
}
</script>

<style scoped>
.batch-permission-assign {
  padding: 20px 0;
}

.operation-type {
  margin-bottom: 20px;
  text-align: center;
}

.target-selection,
.permission-selection {
  height: 500px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.filters {
  margin-bottom: 16px;
}

.target-list {
  max-height: 350px;
  overflow-y: auto;
}

.target-item {
  margin-bottom: 12px;
  padding: 8px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
}

.target-info {
  margin-left: 8px;
}

.target-name {
  font-weight: 500;
  margin-bottom: 4px;
}

.target-meta {
  display: flex;
  gap: 4px;
}

.template-radio {
  width: 100%;
  margin-bottom: 12px;
}

.template-info {
  margin-left: 8px;
}

.template-name {
  font-weight: 500;
  margin-bottom: 4px;
}

.template-desc {
  font-size: 12px;
  color: #909399;
  margin-bottom: 4px;
}

.template-permissions {
  font-size: 12px;
  color: #409eff;
}

.tree-actions {
  margin-bottom: 12px;
  text-align: right;
}

.tree-actions .el-button {
  margin-left: 8px;
}

.permission-node {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.operation-reason {
  margin: 20px 0;
}

.operation-preview {
  margin-top: 20px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 4px;
}

.operation-preview h4 {
  margin-bottom: 12px;
  color: #303133;
}

.preview-details {
  margin-top: 12px;
}

.detail-section {
  margin-bottom: 12px;
}

.detail-section h5 {
  margin-bottom: 8px;
  color: #606266;
}

.target-tag,
.permission-tag {
  margin-right: 8px;
  margin-bottom: 4px;
}

.more-count {
  color: #909399;
  font-size: 12px;
}
</style>
