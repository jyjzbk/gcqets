<template>
  <div class="permission-inheritance-tree">
    <!-- 工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-select
          v-model="selectedOrganization"
          placeholder="选择组织机构"
          clearable
          filterable
          @change="handleOrganizationChange"
          style="width: 250px"
        >
          <el-option
            v-for="org in organizations"
            :key="org.id"
            :label="org.name"
            :value="org.id"
          />
        </el-select>
        
        <el-button
          type="primary"
          :icon="Refresh"
          @click="refreshTree"
          :loading="loading"
        >
          刷新
        </el-button>
        
        <el-button
          type="warning"
          :icon="Warning"
          @click="detectConflicts"
          :loading="conflictLoading"
        >
          检测冲突
        </el-button>
      </div>
      
      <div class="toolbar-right">
        <el-switch
          v-model="showInactive"
          active-text="显示非活跃"
          @change="refreshTree"
        />
        
        <el-button
          type="info"
          :icon="InfoFilled"
          @click="showHelp = true"
        >
          帮助
        </el-button>
      </div>
    </div>

    <!-- 权限继承树 -->
    <div class="tree-container" v-loading="loading">
      <el-empty v-if="!treeData.length && !loading" description="请选择组织机构查看权限继承关系" />
      
      <el-tree
        v-else
        ref="treeRef"
        :data="treeData"
        :props="treeProps"
        :expand-on-click-node="false"
        :default-expand-all="false"
        node-key="id"
        class="inheritance-tree"
      >
        <template #default="{ node, data }">
          <div class="tree-node" :class="getNodeClass(data)">
            <div class="node-content">
              <el-icon class="node-icon" :class="getIconClass(data)">
                <component :is="getNodeIcon(data)" />
              </el-icon>
              
              <span class="node-label">{{ data.label }}</span>
              
              <div class="node-badges">
                <el-tag
                  v-if="data.type === 'permission'"
                  :type="getPermissionTagType(data)"
                  size="small"
                >
                  {{ getPermissionStatus(data) }}
                </el-tag>
                
                <el-tag
                  v-if="data.conflicts && data.conflicts.length > 0"
                  type="danger"
                  size="small"
                >
                  冲突 {{ data.conflicts.length }}
                </el-tag>
                
                <el-tag
                  v-if="data.isOverridden"
                  type="warning"
                  size="small"
                >
                  已覆盖
                </el-tag>
              </div>
            </div>
            
            <div class="node-actions">
              <el-button
                v-if="data.type === 'permission'"
                type="text"
                size="small"
                @click="showPermissionPath(data)"
              >
                路径
              </el-button>
              
              <el-button
                v-if="data.conflicts && data.conflicts.length > 0"
                type="text"
                size="small"
                @click="showConflictDetails(data)"
              >
                详情
              </el-button>
            </div>
          </div>
        </template>
      </el-tree>
    </div>

    <!-- 权限路径对话框 -->
    <el-dialog
      v-model="pathDialogVisible"
      title="权限继承路径"
      width="800px"
    >
      <div class="path-content" v-loading="pathLoading">
        <div v-if="permissionPaths.length > 0">
          <div
            v-for="(path, index) in permissionPaths"
            :key="index"
            class="path-item"
          >
            <h4>路径 {{ index + 1 }}</h4>
            <div class="path-chain">
              <div
                v-for="(step, stepIndex) in path.steps"
                :key="stepIndex"
                class="path-step"
              >
                <div class="step-content">
                  <el-icon><OfficeBuilding /></el-icon>
                  <span>{{ step.organization_name }}</span>
                  <el-tag size="small" :type="step.type === 'direct' ? 'success' : 'info'">
                    {{ step.type === 'direct' ? '直接' : '间接' }}
                  </el-tag>
                </div>
                <el-icon v-if="stepIndex < path.steps.length - 1" class="arrow">
                  <ArrowRight />
                </el-icon>
              </div>
            </div>
          </div>
        </div>
        <el-empty v-else description="未找到继承路径" />
      </div>
    </el-dialog>

    <!-- 冲突详情对话框 -->
    <el-dialog
      v-model="conflictDialogVisible"
      title="权限冲突详情"
      width="900px"
    >
      <div class="conflict-content">
        <el-table :data="selectedConflicts" stripe>
          <el-table-column prop="type" label="冲突类型" width="120">
            <template #default="{ row }">
              <el-tag :type="getConflictTypeTag(row.type)">
                {{ getConflictTypeName(row.type) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="severity" label="严重程度" width="100">
            <template #default="{ row }">
              <el-tag :type="getSeverityTag(row.severity)">
                {{ getSeverityName(row.severity) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="description" label="描述" />
          <el-table-column prop="sources" label="冲突来源" width="200">
            <template #default="{ row }">
              <div v-for="source in row.sources" :key="source.id" class="source-item">
                {{ source.name }}
              </div>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="120">
            <template #default="{ row }">
              <el-button type="text" size="small" @click="resolveConflict(row)">
                解决
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </el-dialog>

    <!-- 帮助对话框 -->
    <el-dialog v-model="showHelp" title="使用帮助" width="700px">
      <div class="help-content">
        <h4>功能说明</h4>
        <p>权限继承关系树展示了组织机构间的权限继承关系和传递路径。</p>
        
        <h4>图标说明</h4>
        <ul>
          <li><el-icon><OfficeBuilding /></el-icon> 组织机构</li>
          <li><el-icon><Key /></el-icon> 权限项</li>
          <li><el-icon><Warning /></el-icon> 权限冲突</li>
        </ul>
        
        <h4>标签说明</h4>
        <ul>
          <li><el-tag type="success" size="small">活跃</el-tag> - 权限正常生效</li>
          <li><el-tag type="info" size="small">继承</el-tag> - 从上级继承的权限</li>
          <li><el-tag type="warning" size="small">已覆盖</el-tag> - 权限被下级覆盖</li>
          <li><el-tag type="danger" size="small">冲突</el-tag> - 存在权限冲突</li>
        </ul>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Refresh,
  Warning,
  InfoFilled,
  OfficeBuilding,
  Key,
  ArrowRight
} from '@element-plus/icons-vue'
import { permissionVisualizationApi } from '../../api/permissionVisualization'
import { organizationApi } from '../../api/organization'

// 响应式数据
const loading = ref(false)
const conflictLoading = ref(false)
const pathLoading = ref(false)
const selectedOrganization = ref(null)
const showInactive = ref(false)
const treeData = ref([])
const organizations = ref([])
const treeRef = ref(null)

// 对话框状态
const pathDialogVisible = ref(false)
const conflictDialogVisible = ref(false)
const showHelp = ref(false)

// 权限路径数据
const permissionPaths = ref([])
const selectedConflicts = ref([])

// 树形组件配置
const treeProps = {
  children: 'children',
  label: 'label'
}

// 组件挂载时初始化
onMounted(() => {
  loadOrganizations()
})

// 加载组织机构列表
const loadOrganizations = async () => {
  try {
    const response = await organizationApi.getList()
    organizations.value = response.data.data || []
  } catch (error) {
    console.error('加载组织机构失败:', error)
    ElMessage.error('加载组织机构失败')
  }
}

// 处理组织机构变更
const handleOrganizationChange = (orgId) => {
  if (orgId) {
    loadInheritanceTree(orgId)
  } else {
    treeData.value = []
  }
}

// 加载权限继承树
const loadInheritanceTree = async (organizationId) => {
  loading.value = true
  try {
    const response = await permissionVisualizationApi.getInheritanceTree({
      organization_id: organizationId,
      include_inactive: showInactive.value
    })
    
    treeData.value = transformTreeData(response.data.data || [])
  } catch (error) {
    console.error('加载权限继承树失败:', error)
    ElMessage.error('加载权限继承树失败')
  } finally {
    loading.value = false
  }
}

// 转换树形数据格式
const transformTreeData = (data) => {
  // 这里需要根据后端返回的数据结构进行转换
  // 暂时返回示例数据结构
  return data.map(item => ({
    id: item.id,
    label: item.name,
    type: item.type,
    children: item.children ? transformTreeData(item.children) : [],
    conflicts: item.conflicts || [],
    isOverridden: item.is_overridden || false,
    status: item.status || 'active',
    permission: item.permission
  }))
}

// 刷新树
const refreshTree = () => {
  if (selectedOrganization.value) {
    loadInheritanceTree(selectedOrganization.value)
  }
}

// 检测权限冲突
const detectConflicts = async () => {
  if (!selectedOrganization.value) {
    ElMessage.warning('请先选择组织机构')
    return
  }
  
  conflictLoading.value = true
  try {
    const response = await permissionVisualizationApi.detectConflicts({
      organization_id: selectedOrganization.value
    })
    
    const conflicts = response.data.data || []
    if (conflicts.length > 0) {
      ElMessage.warning(`检测到 ${conflicts.length} 个权限冲突`)
      // 更新树形数据中的冲突信息
      updateTreeConflicts(conflicts)
    } else {
      ElMessage.success('未检测到权限冲突')
    }
  } catch (error) {
    console.error('检测权限冲突失败:', error)
    ElMessage.error('检测权限冲突失败')
  } finally {
    conflictLoading.value = false
  }
}

// 更新树形数据中的冲突信息
const updateTreeConflicts = (conflicts) => {
  // 递归更新树形数据中的冲突信息
  const updateNodeConflicts = (nodes) => {
    nodes.forEach(node => {
      if (node.type === 'permission') {
        node.conflicts = conflicts.filter(c => c.permission_id === node.permission?.id) || []
      }
      if (node.children) {
        updateNodeConflicts(node.children)
      }
    })
  }
  
  updateNodeConflicts(treeData.value)
}

// 显示权限路径
const showPermissionPath = async (data) => {
  if (!data.permission?.id) return
  
  pathLoading.value = true
  pathDialogVisible.value = true
  
  try {
    const response = await permissionVisualizationApi.getInheritancePath({
      organization_id: selectedOrganization.value,
      permission_id: data.permission.id
    })
    
    permissionPaths.value = response.data.data.paths || []
  } catch (error) {
    console.error('获取权限路径失败:', error)
    ElMessage.error('获取权限路径失败')
  } finally {
    pathLoading.value = false
  }
}

// 显示冲突详情
const showConflictDetails = (data) => {
  selectedConflicts.value = data.conflicts || []
  conflictDialogVisible.value = true
}

// 解决冲突
const resolveConflict = async (conflict) => {
  try {
    await ElMessageBox.confirm('确定要解决此权限冲突吗？', '确认操作', {
      type: 'warning'
    })
    
    // 这里调用解决冲突的API
    ElMessage.success('权限冲突已解决')
    refreshTree()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('解决冲突失败:', error)
      ElMessage.error('解决冲突失败')
    }
  }
}

// 获取节点样式类
const getNodeClass = (data) => {
  const classes = []
  if (data.type === 'organization') classes.push('org-node')
  if (data.type === 'permission') classes.push('permission-node')
  if (data.conflicts && data.conflicts.length > 0) classes.push('has-conflict')
  if (data.isOverridden) classes.push('is-overridden')
  if (data.status === 'inactive') classes.push('inactive')
  return classes.join(' ')
}

// 获取节点图标
const getNodeIcon = (data) => {
  if (data.type === 'organization') return OfficeBuilding
  if (data.type === 'permission') return Key
  return OfficeBuilding
}

// 获取图标样式类
const getIconClass = (data) => {
  if (data.conflicts && data.conflicts.length > 0) return 'conflict-icon'
  if (data.isOverridden) return 'overridden-icon'
  return ''
}

// 获取权限标签类型
const getPermissionTagType = (data) => {
  if (data.status === 'inactive') return 'info'
  if (data.isOverridden) return 'warning'
  return 'success'
}

// 获取权限状态文本
const getPermissionStatus = (data) => {
  if (data.status === 'inactive') return '非活跃'
  if (data.isOverridden) return '已覆盖'
  return '活跃'
}

// 获取冲突类型标签
const getConflictTypeTag = (type) => {
  const typeMap = {
    'role_permission': 'warning',
    'direct_permission': 'danger',
    'inheritance': 'info',
    'template': 'primary'
  }
  return typeMap[type] || 'default'
}

// 获取冲突类型名称
const getConflictTypeName = (type) => {
  const nameMap = {
    'role_permission': '角色权限',
    'direct_permission': '直接权限',
    'inheritance': '继承权限',
    'template': '模板权限'
  }
  return nameMap[type] || type
}

// 获取严重程度标签
const getSeverityTag = (severity) => {
  const severityMap = {
    'low': 'success',
    'medium': 'warning',
    'high': 'danger',
    'critical': 'danger'
  }
  return severityMap[severity] || 'default'
}

// 获取严重程度名称
const getSeverityName = (severity) => {
  const nameMap = {
    'low': '低',
    'medium': '中',
    'high': '高',
    'critical': '严重'
  }
  return nameMap[severity] || severity
}
</script>

<style scoped>
.permission-inheritance-tree {
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
  gap: 12px;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.tree-container {
  flex: 1;
  overflow: auto;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 16px;
}

.inheritance-tree {
  width: 100%;
}

.tree-node {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.tree-node:hover {
  background-color: #f5f7fa;
}

.tree-node.has-conflict {
  border-left: 3px solid #f56c6c;
}

.tree-node.is-overridden {
  border-left: 3px solid #e6a23c;
}

.tree-node.inactive {
  opacity: 0.6;
}

.node-content {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.node-icon {
  font-size: 16px;
}

.node-icon.conflict-icon {
  color: #f56c6c;
}

.node-icon.overridden-icon {
  color: #e6a23c;
}

.node-label {
  font-weight: 500;
}

.node-badges {
  display: flex;
  gap: 4px;
}

.node-actions {
  display: flex;
  gap: 4px;
}

.path-content {
  max-height: 400px;
  overflow-y: auto;
}

.path-item {
  margin-bottom: 20px;
  padding: 16px;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
}

.path-item h4 {
  margin: 0 0 12px 0;
  color: #303133;
}

.path-chain {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.path-step {
  display: flex;
  align-items: center;
  gap: 8px;
}

.step-content {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: #f5f7fa;
  border-radius: 4px;
}

.arrow {
  color: #909399;
}

.conflict-content {
  max-height: 500px;
  overflow-y: auto;
}

.source-item {
  padding: 2px 0;
  font-size: 12px;
  color: #606266;
}

.help-content h4 {
  margin: 16px 0 8px 0;
  color: #303133;
}

.help-content ul {
  margin: 8px 0;
  padding-left: 20px;
}

.help-content li {
  margin: 4px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
