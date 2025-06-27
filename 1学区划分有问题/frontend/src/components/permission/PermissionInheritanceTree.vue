<template>
  <div class="permission-inheritance-tree">
    <div class="tree-header">
      <div class="header-left">
        <h3>权限继承关系</h3>
        <el-text type="info" size="small">
          展示组织机构间的权限继承关系和传递路径
        </el-text>
      </div>
      <div class="header-right">
        <el-select
          v-model="selectedOrganization"
          placeholder="选择组织机构"
          clearable
          filterable
          @change="handleOrganizationChange"
          style="width: 200px; margin-right: 10px;"
        >
          <el-option
            v-for="org in organizationOptions"
            :key="org.id"
            :label="org.name"
            :value="org.id"
          />
        </el-select>
        <el-button @click="refreshTree" :loading="loading">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <el-button @click="detectConflicts" type="warning">
          <el-icon><Warning /></el-icon>
          检测冲突
        </el-button>
      </div>
    </div>

    <div class="tree-content" v-loading="loading">
      <div v-if="!treeData || treeData.length === 0" class="empty-state">
        <el-empty description="暂无权限继承关系数据" />
      </div>
      <div v-else class="tree-container">
        <!-- 树形视图 -->
        <div class="tree-view">
          <el-tree
            ref="treeRef"
            :data="treeData"
            :props="treeProps"
            :expand-on-click-node="false"
            :default-expand-all="true"
            node-key="id"
            class="inheritance-tree"
          >
            <template #default="{ node, data }">
              <div class="tree-node">
                <div class="node-header">
                  <el-icon class="node-icon">
                    <OfficeBuilding v-if="data.type === 'organization'" />
                    <Key v-else />
                  </el-icon>
                  <span class="node-title">{{ data.name }}</span>
                  <el-tag
                    v-if="data.type === 'permission'"
                    :type="getPermissionTagType(data)"
                    size="small"
                  >
                    {{ getPermissionSource(data) }}
                  </el-tag>
                </div>
                <div v-if="data.description" class="node-description">
                  {{ data.description }}
                </div>
                <div v-if="data.inheritance_path" class="inheritance-path">
                  <el-text type="info" size="small">
                    继承路径: {{ data.inheritance_path.join(' → ') }}
                  </el-text>
                </div>
              </div>
            </template>
          </el-tree>
        </div>

        <!-- 权限详情面板 -->
        <div class="detail-panel" v-if="selectedPermission">
          <div class="panel-header">
            <h4>权限详情</h4>
            <el-button text @click="selectedPermission = null">
              <el-icon><Close /></el-icon>
            </el-button>
          </div>
          <div class="panel-content">
            <el-descriptions :column="1" border>
              <el-descriptions-item label="权限名称">
                {{ selectedPermission.display_name }}
              </el-descriptions-item>
              <el-descriptions-item label="权限标识">
                {{ selectedPermission.name }}
              </el-descriptions-item>
              <el-descriptions-item label="所属模块">
                {{ selectedPermission.module }}
              </el-descriptions-item>
              <el-descriptions-item label="最小级别">
                {{ selectedPermission.min_level }}
              </el-descriptions-item>
              <el-descriptions-item label="继承类型">
                <el-tag :type="selectedPermission.inheritance_type === 'direct' ? 'success' : 'info'">
                  {{ selectedPermission.inheritance_type === 'direct' ? '直接继承' : '间接继承' }}
                </el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="是否被覆盖">
                <el-tag :type="selectedPermission.is_overridden ? 'danger' : 'success'">
                  {{ selectedPermission.is_overridden ? '已覆盖' : '正常' }}
                </el-tag>
              </el-descriptions-item>
            </el-descriptions>
          </div>
        </div>
      </div>
    </div>

    <!-- 冲突检测结果对话框 -->
    <el-dialog
      v-model="conflictDialogVisible"
      title="权限冲突检测结果"
      width="800px"
    >
      <div v-if="conflicts.length === 0" class="no-conflicts">
        <el-result
          icon="success"
          title="未发现权限冲突"
          sub-title="当前权限配置正常，没有检测到冲突"
        />
      </div>
      <div v-else>
        <el-alert
          :title="`检测到 ${conflicts.length} 个权限冲突`"
          type="warning"
          :closable="false"
          style="margin-bottom: 20px;"
        />
        <el-table :data="conflicts" style="width: 100%">
          <el-table-column prop="conflict_type" label="冲突类型" width="120">
            <template #default="{ row }">
              <el-tag :type="getConflictTypeTag(row.conflict_type)">
                {{ getConflictTypeText(row.conflict_type) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="permission_name" label="权限" />
          <el-table-column prop="priority" label="优先级" width="80">
            <template #default="{ row }">
              <el-tag :type="getPriorityTag(row.priority)">
                {{ row.priority }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="120">
            <template #default="{ row }">
              <el-button size="small" @click="viewConflictDetail(row)">
                查看详情
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <template #footer>
        <el-button @click="conflictDialogVisible = false">关闭</el-button>
        <el-button v-if="conflicts.length > 0" type="primary" @click="goToConflictManagement">
          前往冲突管理
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { permissionVisualizationApi } from '@/api/permissionVisualization'
import { organizationApi } from '@/api/organization'

// 响应式数据
const loading = ref(false)
const treeData = ref([])
const selectedOrganization = ref(null)
const organizationOptions = ref([])
const selectedPermission = ref(null)
const conflictDialogVisible = ref(false)
const conflicts = ref([])

// 树形组件配置
const treeProps = {
  children: 'children',
  label: 'name'
}

// 组件引用
const treeRef = ref(null)

// 计算属性
const hasTreeData = computed(() => {
  return treeData.value && treeData.value.length > 0
})

// 方法
const loadOrganizations = async () => {
  try {
    const response = await organizationApi.getTree()
    organizationOptions.value = flattenOrganizations(response.data)
  } catch (error) {
    console.error('加载组织机构失败:', error)
    ElMessage.error('加载组织机构失败')
  }
}

const flattenOrganizations = (orgs, result = []) => {
  orgs.forEach(org => {
    result.push({
      id: org.id,
      name: org.name,
      level: org.level
    })
    if (org.children && org.children.length > 0) {
      flattenOrganizations(org.children, result)
    }
  })
  return result
}

const loadInheritanceTree = async () => {
  loading.value = true
  try {
    const params = {}
    if (selectedOrganization.value) {
      params.organization_id = selectedOrganization.value
    }
    
    const response = await permissionVisualizationApi.getInheritanceTree(params)
    treeData.value = transformTreeData(response.data)
  } catch (error) {
    console.error('加载权限继承关系失败:', error)
    ElMessage.error('加载权限继承关系失败')
  } finally {
    loading.value = false
  }
}

const transformTreeData = (data) => {
  if (!Array.isArray(data)) {
    data = [data]
  }
  
  return data.map(item => ({
    id: `org_${item.organization.id}`,
    name: item.organization.name,
    type: 'organization',
    description: item.organization.description,
    children: [
      ...item.inherited_permissions.map(perm => ({
        id: `inherited_${perm.permission.id}`,
        name: perm.permission.display_name,
        type: 'permission',
        source: 'inherited',
        inheritance_type: 'direct',
        is_overridden: perm.is_overridden,
        inheritance_path: perm.inheritance_path,
        permission: perm.permission
      })),
      ...item.direct_permissions.map(perm => ({
        id: `direct_${perm.id}`,
        name: perm.display_name,
        type: 'permission',
        source: 'direct',
        permission: perm
      })),
      ...item.children.map(child => transformTreeData([child])[0])
    ]
  }))
}

const handleOrganizationChange = () => {
  loadInheritanceTree()
}

const refreshTree = () => {
  loadInheritanceTree()
}

const detectConflicts = async () => {
  loading.value = true
  try {
    const params = {}
    if (selectedOrganization.value) {
      params.organization_id = selectedOrganization.value
    }
    
    const response = await permissionVisualizationApi.detectConflicts(params)
    conflicts.value = response.data.conflicts || []
    conflictDialogVisible.value = true
  } catch (error) {
    console.error('检测权限冲突失败:', error)
    ElMessage.error('检测权限冲突失败')
  } finally {
    loading.value = false
  }
}

const getPermissionTagType = (data) => {
  if (data.source === 'inherited') {
    return data.is_overridden ? 'danger' : 'success'
  }
  return 'primary'
}

const getPermissionSource = (data) => {
  if (data.source === 'inherited') {
    return data.is_overridden ? '继承(已覆盖)' : '继承'
  }
  return '直接分配'
}

const getConflictTypeTag = (type) => {
  const typeMap = {
    'role_user': 'warning',
    'role_role': 'danger',
    'inheritance': 'info',
    'explicit_deny': 'danger'
  }
  return typeMap[type] || 'info'
}

const getConflictTypeText = (type) => {
  const typeMap = {
    'role_user': '角色用户冲突',
    'role_role': '角色间冲突',
    'inheritance': '继承冲突',
    'explicit_deny': '显式拒绝'
  }
  return typeMap[type] || type
}

const getPriorityTag = (priority) => {
  const priorityMap = {
    'high': 'danger',
    'medium': 'warning',
    'low': 'info'
  }
  return priorityMap[priority] || 'info'
}

const viewConflictDetail = (conflict) => {
  // 这里可以实现查看冲突详情的逻辑
  ElMessage.info('查看冲突详情功能待实现')
}

const goToConflictManagement = () => {
  conflictDialogVisible.value = false
  // 这里可以跳转到冲突管理页面
  ElMessage.info('跳转到冲突管理页面')
}

// 生命周期
onMounted(() => {
  loadOrganizations()
  loadInheritanceTree()
})

// 暴露给父组件的方法
defineExpose({
  refreshTree,
  detectConflicts
})
</script>

<style scoped>
.permission-inheritance-tree {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.tree-header {
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
}

.tree-content {
  flex: 1;
  padding: 20px;
  overflow: hidden;
}

.tree-container {
  display: flex;
  height: 100%;
  gap: 20px;
}

.tree-view {
  flex: 1;
  overflow: auto;
}

.inheritance-tree {
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 4px;
}

.tree-node {
  flex: 1;
  padding: 5px 0;
}

.node-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.node-icon {
  color: #409eff;
}

.node-title {
  font-weight: 500;
  color: #303133;
}

.node-description {
  margin-top: 4px;
  font-size: 12px;
  color: #909399;
}

.inheritance-path {
  margin-top: 4px;
}

.detail-panel {
  width: 300px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  background: #fff;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #ebeef5;
}

.panel-header h4 {
  margin: 0;
  color: #303133;
}

.panel-content {
  padding: 15px;
}

.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 300px;
}

.no-conflicts {
  text-align: center;
  padding: 20px;
}
</style>
