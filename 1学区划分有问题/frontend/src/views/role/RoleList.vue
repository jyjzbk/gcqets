<template>
  <div class="role-list">
    <div class="page-header">
      <h2>角色管理</h2>
      <el-button
        v-if="canCreateRole()"
        type="primary"
        @click="handleCreate"
      >
        <el-icon><Plus /></el-icon>
        创建角色
      </el-button>
    </div>
    
    <!-- 搜索栏 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="搜索">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入角色名称或描述"
            style="width: 300px"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        
        <el-form-item label="级别">
          <el-select v-model="searchForm.level" placeholder="请选择级别" clearable>
            <el-option label="系统管理员" :value="1" />
            <el-option label="组织管理员" :value="2" />
            <el-option label="部门管理员" :value="3" />
            <el-option label="普通用户" :value="4" />
            <el-option label="受限用户" :value="5" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="启用" :value="true" />
            <el-option label="禁用" :value="false" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="类型">
          <el-select v-model="searchForm.is_system" placeholder="请选择类型" clearable>
            <el-option label="系统角色" :value="true" />
            <el-option label="自定义角色" :value="false" />
          </el-select>
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
    
    <!-- 数据表格 -->
    <el-card>
      <div class="table-header">
        <span>共 {{ roleStore.total }} 条记录</span>
        <el-button
          type="primary"
          text
          @click="handleRefresh"
        >
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
      </div>
      
      <el-table
        v-loading="roleStore.loading"
        :data="roleStore.roles"
        style="width: 100%"
      >
        <el-table-column prop="name" label="角色名称" width="150" />
        
        <el-table-column prop="display_name" label="显示名称" width="150" />
        
        <el-table-column prop="level" label="级别" width="120">
          <template #default="{ row }">
            <el-tag :type="getLevelTagType(row.level)">
              {{ getLevelName(row.level) }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        
        <el-table-column prop="permissions" label="权限数量" width="100">
          <template #default="{ row }">
            <el-tag size="small" type="info">
              {{ row.permissions?.length || 0 }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="is_system" label="类型" width="100">
          <template #default="{ row }">
            <el-tag :type="row.is_system ? 'danger' : 'success'">
              {{ row.is_system ? '系统角色' : '自定义角色' }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status ? 'success' : 'danger'">
              {{ row.status ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="created_at" label="创建时间" width="180">
          <template #default="{ row }">
            {{ formatDate(row.created_at) }}
          </template>
        </el-table-column>
        
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button
              type="primary"
              text
              size="small"
              @click="handleView(row)"
            >
              查看
            </el-button>

            <el-button
              v-if="canEditRole(row)"
              type="primary"
              text
              size="small"
              @click="handleEdit(row)"
              :disabled="row.is_system"
            >
              编辑
            </el-button>

            <el-button
              v-if="canAssignPermissions(row)"
              type="warning"
              text
              size="small"
              @click="handleAssignPermissions(row)"
            >
              分配权限
            </el-button>

            <el-button
              v-if="canDeleteRole(row)"
              type="danger"
              text
              size="small"
              @click="handleDelete(row)"
              :disabled="row.is_system"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="roleStore.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
    
    <!-- 查看详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="角色详情"
      width="600px"
    >
      <div v-if="currentRole">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="角色名称">
            {{ currentRole.name }}
          </el-descriptions-item>
          <el-descriptions-item label="显示名称">
            {{ currentRole.display_name }}
          </el-descriptions-item>
          <el-descriptions-item label="级别">
            <el-tag :type="getLevelTagType(currentRole.level)">
              {{ getLevelName(currentRole.level) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="类型">
            <el-tag :type="currentRole.is_system ? 'danger' : 'success'">
              {{ currentRole.is_system ? '系统角色' : '自定义角色' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="currentRole.status ? 'success' : 'danger'">
              {{ currentRole.status ? '启用' : '禁用' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="描述" :span="2">
            {{ currentRole.description || '-' }}
          </el-descriptions-item>
        </el-descriptions>
        
        <div class="permissions-section">
          <h4>权限列表</h4>
          <el-tag
            v-for="permission in currentRole.permissions"
            :key="permission.id"
            style="margin-right: 8px; margin-bottom: 8px"
          >
            {{ permission.display_name }}
          </el-tag>
        </div>
      </div>
    </el-dialog>
    
    <!-- 分配权限对话框 -->
    <el-dialog
      v-model="permissionDialogVisible"
      title="分配权限"
      width="800px"
    >
      <div v-if="currentRole">
        <el-form :model="permissionForm" label-width="100px">
          <el-form-item label="角色">
            <span>{{ currentRole.display_name }}</span>
          </el-form-item>
          
          <el-form-item label="权限">
            <el-tree
              ref="permissionTreeRef"
              :data="permissionTree"
              :props="treeProps"
              show-checkbox
              node-key="id"
              :default-checked-keys="selectedPermissions"
              style="max-height: 400px; overflow-y: auto"
            />
          </el-form-item>
        </el-form>
      </div>
      
      <template #footer>
        <el-button @click="permissionDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleAssignPermissionsSubmit" :loading="permissionLoading">
          分配
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useRoleStore } from '../../stores/role'
import { useAuthStore } from '../../stores/auth'
import { permissionApi } from '../../api/permission'
import { ElMessage, ElMessageBox } from 'element-plus'
import { formatDate } from '../../utils/date'

const router = useRouter()
const roleStore = useRoleStore()
const authStore = useAuthStore()

// 搜索表单
const searchForm = reactive({
  search: '',
  level: null,
  status: null,
  is_system: null
})

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20
})

// 对话框状态
const detailDialogVisible = ref(false)
const permissionDialogVisible = ref(false)
const currentRole = ref(null)
const permissionLoading = ref(false)

// 权限相关
const permissionTree = ref([])
const selectedPermissions = ref([])
const permissionTreeRef = ref()

// 权限表单
const permissionForm = reactive({
  permission_ids: []
})

// 树形配置
const treeProps = {
  children: 'children',
  label: 'display_name',
  value: 'id'
}

// 获取角色列表
const getRoles = async () => {
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      per_page: pagination.pageSize
    }
    await roleStore.getRoles(params)
  } catch (error) {
    console.error('Get roles error:', error)
  }
}

// 获取权限树
const getPermissionTree = async () => {
  try {
    const response = await permissionApi.getTree()
    permissionTree.value = response.data.data
  } catch (error) {
    console.error('Get permission tree error:', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  getRoles()
}

// 重置搜索
const handleReset = () => {
  Object.assign(searchForm, {
    search: '',
    level: null,
    status: null,
    is_system: null
  })
  pagination.page = 1
  getRoles()
}

// 刷新
const handleRefresh = () => {
  getRoles()
}

// 分页处理
const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  getRoles()
}

const handleCurrentChange = (page) => {
  pagination.page = page
  getRoles()
}

// 创建
const handleCreate = () => {
  router.push('/roles/create')
}

// 查看
const handleView = async (row) => {
  try {
    await roleStore.getRole(row.id)
    currentRole.value = roleStore.currentRole
    detailDialogVisible.value = true
  } catch (error) {
    console.error('Get role detail error:', error)
  }
}

// 编辑
const handleEdit = (row) => {
  router.push(`/roles/${row.id}/edit`)
}

// 分配权限
const handleAssignPermissions = async (row) => {
  currentRole.value = row
  selectedPermissions.value = row.permissions?.map(p => p.id) || []
  permissionDialogVisible.value = true
}

// 提交分配权限
const handleAssignPermissionsSubmit = async () => {
  try {
    permissionLoading.value = true
    
    const checkedNodes = permissionTreeRef.value.getCheckedNodes()
    const permissionIds = checkedNodes.map(node => node.id)
    
    await roleStore.assignPermissions(currentRole.value.id, {
      permission_ids: permissionIds
    })
    
    ElMessage.success('分配权限成功')
    permissionDialogVisible.value = false
    getRoles()
  } catch (error) {
    console.error('Assign permissions error:', error)
  } finally {
    permissionLoading.value = false
  }
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除角色"${row.display_name}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    await roleStore.deleteRole(row.id)
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Delete role error:', error)
    }
  }
}

// 获取级别名称
const getLevelName = (level) => {
  const levelMap = {
    1: '系统管理员',
    2: '组织管理员',
    3: '部门管理员',
    4: '普通用户',
    5: '受限用户'
  }
  return levelMap[level] || '未知'
}

// 获取级别标签类型
const getLevelTagType = (level) => {
  const typeMap = {
    1: 'danger',
    2: 'warning',
    3: 'info',
    4: 'success',
    5: 'primary'
  }
  return typeMap[level] || 'info'
}

// 权限检查方法
const canCreateRole = () => {
  // 系统管理员可以创建角色
  if (authStore.hasRole('system_admin')) {
    return true
  }

  // 其他角色根据级别判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  // 只有高级管理员可以创建角色
  return userRoles.some(role => role.level <= 3) // 区县管理员及以上可以创建角色
}

const canEditRole = (row) => {
  // 系统管理员可以编辑所有非系统角色
  if (authStore.hasRole('system_admin')) {
    return !row.is_system
  }

  // 其他角色根据级别判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  // 获取用户最高角色级别
  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  // 只能编辑下级角色
  return row.level > userMaxLevel && !row.is_system
}

const canDeleteRole = (row) => {
  // 系统管理员可以删除非系统角色
  if (authStore.hasRole('system_admin')) {
    return !row.is_system
  }

  // 其他角色根据级别判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  // 只能删除下级角色
  return row.level > userMaxLevel && !row.is_system
}

const canAssignPermissions = (row) => {
  // 系统管理员可以为所有角色分配权限
  if (authStore.hasRole('system_admin')) {
    return true
  }

  // 其他角色根据级别判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  // 只能为同级或下级角色分配权限
  return row.level >= userMaxLevel
}

onMounted(async () => {
  await Promise.all([
    getRoles(),
    getPermissionTree()
  ])
})
</script>

<style scoped>
.role-list {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #333;
}

.search-card {
  margin-bottom: 20px;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.pagination-wrapper {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.permissions-section {
  margin-top: 20px;
}

.permissions-section h4 {
  margin-bottom: 12px;
  color: #333;
}
</style> 