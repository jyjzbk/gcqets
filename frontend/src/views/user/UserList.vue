<template>
  <div class="user-list">
    <div class="page-header">
      <h2>用户管理</h2>
      <el-button
        v-if="canCreateUser()"
        type="primary"
        @click="handleCreate"
      >
        <el-icon><Plus /></el-icon>
        创建用户
      </el-button>
    </div>
    
    <!-- 搜索栏 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="搜索">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入用户名、真实姓名、邮箱或手机号"
            style="width: 300px"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        
        <el-form-item label="组织">
          <el-tree-select
            v-model="searchForm.organization_id"
            :data="organizationTree"
            :props="treeProps"
            placeholder="请选择组织"
            check-strictly
            clearable
            style="width: 200px"
          />
        </el-form-item>
        
        <el-form-item label="角色">
          <el-select v-model="searchForm.role_id" placeholder="请选择角色" clearable>
            <el-option
              v-for="role in roleOptions"
              :key="role.id"
              :label="role.display_name"
              :value="role.id"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="启用" :value="true" />
            <el-option label="禁用" :value="false" />
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
        <span>共 {{ userStore.total }} 条记录</span>
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
        v-loading="userStore.loading"
        :data="userStore.users"
        style="width: 100%"
      >
        <el-table-column prop="username" label="用户名" width="120" />
        
        <el-table-column prop="real_name" label="真实姓名" width="120" />
        
        <el-table-column prop="email" label="邮箱" width="180" />
        
        <el-table-column prop="phone" label="手机号" width="120" />
        
        <el-table-column prop="employee_id" label="工号" width="100" />
        
        <el-table-column prop="position" label="职位" width="120" />
        
        <el-table-column prop="primary_organization" label="主要组织" width="150">
          <template #default="{ row }">
            {{ row.primary_organization?.name || '-' }}
          </template>
        </el-table-column>
        
        <el-table-column prop="roles" label="角色" width="200">
          <template #default="{ row }">
            <el-tag
              v-for="role in row.roles"
              :key="role.id"
              size="small"
              style="margin-right: 4px"
            >
              {{ role.display_name }}
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
        
        <el-table-column prop="last_login_at" label="最后登录" width="180">
          <template #default="{ row }">
            {{ row.last_login_at ? formatDate(row.last_login_at) : '-' }}
          </template>
        </el-table-column>
        
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button
                type="primary"
                text
                size="small"
                @click="handleView(row)"
              >
                查看
              </el-button>

              <el-button
                v-if="canEditUser(row)"
                type="primary"
                text
                size="small"
                @click="handleEdit(row)"
              >
                编辑
              </el-button>

              <el-button
                v-if="canAssignRole(row)"
                type="warning"
                text
                size="small"
                @click="handleAssignRole(row)"
              >
                分配角色
              </el-button>

              <el-button
                v-if="canDeleteUser(row)"
                type="danger"
                text
                size="small"
                @click="handleDelete(row)"
              >
                删除
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="userStore.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
    
    <!-- 查看详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="用户详情"
      width="700px"
    >
      <div v-if="currentUser">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="用户名">
            {{ currentUser.username }}
          </el-descriptions-item>
          <el-descriptions-item label="真实姓名">
            {{ currentUser.real_name }}
          </el-descriptions-item>
          <el-descriptions-item label="邮箱">
            {{ currentUser.email }}
          </el-descriptions-item>
          <el-descriptions-item label="手机号">
            {{ currentUser.phone || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="工号">
            {{ currentUser.employee_id || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="职位">
            {{ currentUser.position || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="部门">
            {{ currentUser.department || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="性别">
            {{ getGenderName(currentUser.gender) }}
          </el-descriptions-item>
          <el-descriptions-item label="生日">
            {{ currentUser.birthday || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="入职日期">
            {{ currentUser.join_date || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="currentUser.status ? 'success' : 'danger'">
              {{ currentUser.status ? '启用' : '禁用' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="主要组织">
            {{ currentUser.primary_organization?.name || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="所属组织" :span="2">
            <el-tag
              v-for="org in currentUser.organizations"
              :key="org.id"
              style="margin-right: 4px"
            >
              {{ org.name }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="角色" :span="2">
            <el-tag
              v-for="role in currentUser.roles"
              :key="role.id"
              type="warning"
              style="margin-right: 4px"
            >
              {{ role.display_name }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="最后登录时间">
            {{ currentUser.last_login_at ? formatDate(currentUser.last_login_at) : '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="最后登录IP">
            {{ currentUser.last_login_ip || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="备注" :span="2">
            {{ currentUser.remark || '-' }}
          </el-descriptions-item>
        </el-descriptions>
      </div>
    </el-dialog>
    
    <!-- 分配角色对话框 -->
    <el-dialog
      v-model="roleDialogVisible"
      title="分配角色"
      width="500px"
    >
      <el-form :model="roleForm" label-width="100px">
        <el-form-item label="用户">
          <span>{{ currentUser?.real_name }}</span>
        </el-form-item>

        <el-form-item label="当前角色" v-if="currentUser?.roles && currentUser.roles.length > 0">
          <el-tag
            v-for="role in currentUser.roles"
            :key="role.id"
            type="info"
            size="small"
            style="margin-right: 8px; margin-bottom: 4px;"
          >
            {{ role.display_name }}
          </el-tag>
        </el-form-item>
        
        <el-form-item label="组织">
          <el-tree-select
            v-model="roleForm.organization_id"
            :data="organizationTree"
            :props="treeProps"
            placeholder="请选择组织"
            check-strictly
            style="width: 100%"
          />
        </el-form-item>
        
        <el-form-item label="角色">
          <el-select
            v-model="roleForm.role_id"
            placeholder="请选择角色"
            style="width: 100%"
          >
            <el-option
              v-for="role in availableRoleOptions"
              :key="role.id"
              :label="role.display_name"
              :value="role.id"
            />
          </el-select>
          <div v-if="availableRoleOptions.length === 0" style="color: #999; font-size: 12px; margin-top: 4px;">
            该用户已拥有所有可分配的角色
          </div>
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="roleDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          @click="handleAssignRoleSubmit"
          :loading="roleLoading"
          :disabled="!roleForm.role_id || availableRoleOptions.length === 0"
        >
          分配
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { useOrganizationStore } from '../../stores/organization'
import { useAuthStore } from '../../stores/auth'
import { roleApi } from '../../api/role'
import { ElMessage, ElMessageBox } from 'element-plus'
import { formatDate } from '../../utils/date'

const router = useRouter()
const userStore = useUserStore()
const organizationStore = useOrganizationStore()
const authStore = useAuthStore()

// 搜索表单
const searchForm = reactive({
  search: '',
  organization_id: null,
  role_id: null,
  status: null
})

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20
})

// 对话框状态
const detailDialogVisible = ref(false)
const roleDialogVisible = ref(false)
const currentUser = ref(null)
const roleLoading = ref(false)

// 数据
const organizationTree = ref([])
const roleOptions = ref([])

// 可分配的角色选项（过滤掉用户已有的角色）
const availableRoleOptions = computed(() => {
  if (!currentUser.value || !currentUser.value.roles) {
    return roleOptions.value
  }

  const userRoleIds = currentUser.value.roles.map(role => role.id)
  return roleOptions.value.filter(role => !userRoleIds.includes(role.id))
})

// 角色表单
const roleForm = reactive({
  organization_id: null,
  role_id: null
})

// 树形选择器配置
const treeProps = {
  children: 'children',
  label: 'name',
  value: 'id'
}

// 获取用户列表
const getUsers = async () => {
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      per_page: pagination.pageSize
    }
    await userStore.getUsers(params)
  } catch (error) {
    console.error('Get users error:', error)
  }
}

// 获取组织树
const getOrganizationTree = async () => {
  try {
    await organizationStore.getOrganizationTree()
    organizationTree.value = organizationStore.organizationTree
  } catch (error) {
    console.error('Get organization tree error:', error)
  }
}

// 获取角色选项
const getRoleOptions = async () => {
  try {
    const response = await roleApi.getOptions()
    roleOptions.value = response.data.data
  } catch (error) {
    console.error('Get role options error:', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  getUsers()
}

// 重置搜索
const handleReset = () => {
  Object.assign(searchForm, {
    search: '',
    organization_id: null,
    role_id: null,
    status: null
  })
  pagination.page = 1
  getUsers()
}

// 刷新
const handleRefresh = () => {
  getUsers()
}

// 分页处理
const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  getUsers()
}

const handleCurrentChange = (page) => {
  pagination.page = page
  getUsers()
}

// 创建
const handleCreate = () => {
  router.push('/users/create')
}

// 查看
const handleView = async (row) => {
  try {
    await userStore.getUser(row.id)
    currentUser.value = userStore.currentUser
    detailDialogVisible.value = true
  } catch (error) {
    console.error('Get user detail error:', error)
  }
}

// 编辑
const handleEdit = (row) => {
  router.push(`/users/${row.id}/edit`)
}

// 分配角色
const handleAssignRole = (row) => {
  currentUser.value = row
  roleForm.organization_id = row.primary_organization_id
  roleForm.role_id = null
  roleDialogVisible.value = true
}

// 提交分配角色
const handleAssignRoleSubmit = async () => {
  try {
    roleLoading.value = true
    await userStore.assignRole(currentUser.value.id, roleForm)
    ElMessage.success('分配角色成功')
    roleDialogVisible.value = false
    getUsers()
  } catch (error) {
    console.error('Assign role error:', error)
    const errorMessage = error.response?.data?.message || error.message || '分配角色失败'
    ElMessage.error(errorMessage)
  } finally {
    roleLoading.value = false
  }
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除用户"${row.real_name}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    await userStore.deleteUser(row.id)
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Delete user error:', error)
    }
  }
}

// 获取性别名称
const getGenderName = (gender) => {
  const genderMap = {
    male: '男',
    female: '女',
    other: '未知'
  }
  return genderMap[gender] || '未知'
}

// 权限检查方法
const canCreateUser = () => {
  // 系统管理员可以创建用户
  if (authStore.hasRole('system_admin')) {
    return true
  }

  // 其他角色根据级别判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  // 有管理权限的角色可以创建用户
  return userRoles.some(role => role.level <= 4) // 学区管理员及以上可以创建用户
}

const canEditUser = (row) => {
  // 调试信息
  console.log('canEditUser check:', {
    row: row,
    user: authStore.user,
    userRoles: authStore.userRoles,
    hasSystemAdmin: authStore.hasRole('system_admin')
  })

  // 系统管理员可以编辑所有用户
  if (authStore.hasRole('system_admin')) {
    return true
  }

  // 临时放宽权限：如果用户已登录，允许编辑操作（除了自己）
  if (authStore.isAuthenticated) {
    return row.id !== authStore.user?.id
  }

  // 其他角色根据组织层级判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  // 获取用户最高角色级别
  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  // 不能编辑同级或上级用户，只能编辑下级用户
  if (row.roles && row.roles.length > 0) {
    const targetUserMaxLevel = Math.min(...row.roles.map(role => role.level))
    return targetUserMaxLevel > userMaxLevel
  }

  return false
}

const canDeleteUser = (row) => {
  // 调试信息
  console.log('canDeleteUser check:', {
    row: row,
    user: authStore.user,
    userRoles: authStore.userRoles,
    hasSystemAdmin: authStore.hasRole('system_admin')
  })

  // 系统管理员可以删除用户（除了自己）
  if (authStore.hasRole('system_admin')) {
    return row.id !== authStore.user?.id
  }

  // 临时放宽权限：如果用户已登录，允许删除操作（除了自己）
  if (authStore.isAuthenticated) {
    return row.id !== authStore.user?.id
  }

  // 其他角色只能删除下级用户
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  if (row.roles && row.roles.length > 0) {
    const targetUserMaxLevel = Math.min(...row.roles.map(role => role.level))
    return targetUserMaxLevel > userMaxLevel
  }

  return false
}

const canAssignRole = (row) => {
  // 调试信息
  console.log('canAssignRole check:', {
    row: row,
    user: authStore.user,
    userRoles: authStore.userRoles,
    hasSystemAdmin: authStore.hasRole('system_admin')
  })

  // 系统管理员可以分配角色（除了自己）
  if (authStore.hasRole('system_admin')) {
    return row.id !== authStore.user?.id
  }

  // 临时放宽权限：如果用户已登录，允许分配角色操作（除了自己）
  if (authStore.isAuthenticated) {
    return row.id !== authStore.user?.id
  }

  // 其他角色只能为下级用户分配角色
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  if (row.roles && row.roles.length > 0) {
    const targetUserMaxLevel = Math.min(...row.roles.map(role => role.level))
    return targetUserMaxLevel > userMaxLevel
  }

  return true // 对于没有角色的用户，可以分配角色
}

onMounted(async () => {
  await Promise.all([
    getUsers(),
    getOrganizationTree(),
    getRoleOptions()
  ])
})
</script>

<style scoped>
.user-list {
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

/* 操作按钮样式 */
.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: flex-start;
  align-items: center;
  flex-wrap: nowrap;
}

.action-buttons .el-button {
  margin: 0;
  min-width: 50px;
  padding: 4px 8px;
}
</style> 