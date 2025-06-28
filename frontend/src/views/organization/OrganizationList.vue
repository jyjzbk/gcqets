<template>
  <div class="organization-list">
    <div class="page-header">
      <h2>组织机构管理</h2>
      <el-button
        v-permission="'organization.create'"
        type="primary"
        @click="handleCreate"
      >
        <el-icon><Plus /></el-icon>
        创建组织机构
      </el-button>
    </div>
    
    <!-- 搜索栏 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="搜索">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入组织名称、编码或描述"
            style="width: 300px"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        
        <el-form-item label="级别">
          <el-select v-model="searchForm.level" placeholder="请选择级别" clearable>
            <el-option label="省级" :value="1" />
            <el-option label="市级" :value="2" />
            <el-option label="区县级" :value="3" />
            <el-option label="学区级" :value="4" />
            <el-option label="学校级" :value="5" />
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
        <div class="table-info">
          <span v-if="isSearchMode">搜索结果：共 {{ organizationStore.total }} 条记录</span>
          <span v-else>组织机构树形结构</span>
          <el-tag v-if="!isSearchMode" size="small" type="info" style="margin-left: 10px">
            点击展开查看下级组织
          </el-tag>
        </div>
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
        v-loading="organizationStore.loading"
        :data="organizationData"
        style="width: 100%"
        row-key="id"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
        lazy
        :load="loadChildren"
        :expand-row-keys="expandedKeys"
        @expand-change="handleExpandChange"
      >
        <el-table-column prop="name" label="组织名称" min-width="200">
          <template #default="{ row }">
            <span class="organization-name">{{ row.name }}</span>
            <el-tag v-if="row.is_root" size="small" type="success">根组织</el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="code" label="组织编码" width="120" />
        
        <el-table-column prop="level" label="级别" width="100">
          <template #default="{ row }">
            <el-tag :type="getLevelTagType(row.level)">
              {{ getLevelName(row.level) }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="contact_person" label="联系人" width="100" />
        
        <el-table-column prop="contact_phone" label="联系电话" width="120" />
        
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
        
        <el-table-column label="操作" width="250" fixed="right">
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
              v-if="canEditOrganization(row)"
              type="primary"
              text
              size="small"
              @click="handleEdit(row)"
            >
              编辑
            </el-button>

            <el-button
              v-if="canDeleteOrganization(row)"
              type="danger"
              text
              size="small"
              @click="handleDelete(row)"
            >
              删除
            </el-button>

            <el-button
              v-if="canCreateSubOrganization(row)"
              type="success"
              text
              size="small"
              @click="handleCreateSub(row)"
            >
              新增下级
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页（仅搜索模式显示） -->
      <div v-if="isSearchMode" class="pagination-wrapper">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="organizationStore.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
    
    <!-- 查看详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="组织机构详情"
      width="600px"
    >
      <div v-if="currentOrganization">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="组织名称">
            {{ currentOrganization.name }}
          </el-descriptions-item>
          <el-descriptions-item label="组织编码">
            {{ currentOrganization.code }}
          </el-descriptions-item>
          <el-descriptions-item label="组织级别">
            <el-tag :type="getLevelTagType(currentOrganization.level)">
              {{ getLevelName(currentOrganization.level) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="currentOrganization.status ? 'success' : 'danger'">
              {{ currentOrganization.status ? '启用' : '禁用' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="联系人">
            {{ currentOrganization.contact_person || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="联系电话">
            {{ currentOrganization.contact_phone || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="联系邮箱">
            {{ currentOrganization.contact_email || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="地址">
            {{ currentOrganization.address || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="描述" :span="2">
            {{ currentOrganization.description || '-' }}
          </el-descriptions-item>
        </el-descriptions>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOrganizationStore } from '../../stores/organization'
import { useAuthStore } from '../../stores/auth'
import { ElMessage, ElMessageBox } from 'element-plus'
import { formatDate } from '../../utils/date'

const router = useRouter()
const organizationStore = useOrganizationStore()
const authStore = useAuthStore()

// 搜索表单
const searchForm = reactive({
  search: '',
  level: null,
  status: null
})

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20
})

// 对话框状态
const detailDialogVisible = ref(false)
const currentOrganization = ref(null)

// 树形结构相关
const organizationData = ref([])
const expandedKeys = ref([])
const isSearchMode = ref(false)

// 获取组织列表
const getOrganizations = async () => {
  try {
    // 检查是否有搜索条件
    const hasSearchConditions = searchForm.search || searchForm.level || searchForm.status !== null
    isSearchMode.value = hasSearchConditions

    if (hasSearchConditions) {
      // 搜索模式：获取扁平列表
      const params = {
        ...searchForm,
        page: pagination.page,
        per_page: pagination.pageSize
      }
      await organizationStore.getOrganizations(params)
      organizationData.value = organizationStore.organizations
    } else {
      // 树形模式：获取根级组织
      await organizationStore.getOrganizations({})
      organizationData.value = organizationStore.organizations
    }
  } catch (error) {
    console.error('Get organizations error:', error)
  }
}

// 懒加载子组织
const loadChildren = async (row, treeNode, resolve) => {
  try {
    const response = await organizationStore.getChildren(row.id)
    const children = response.data || []
    resolve(children)
  } catch (error) {
    console.error('Load children error:', error)
    resolve([])
  }
}

// 处理展开/收起
const handleExpandChange = (row, expanded) => {
  if (expanded) {
    if (!expandedKeys.value.includes(row.id)) {
      expandedKeys.value.push(row.id)
    }
  } else {
    const index = expandedKeys.value.indexOf(row.id)
    if (index > -1) {
      expandedKeys.value.splice(index, 1)
    }
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  getOrganizations()
}

// 重置搜索
const handleReset = () => {
  Object.assign(searchForm, {
    search: '',
    level: null,
    status: null
  })
  pagination.page = 1
  getOrganizations()
}

// 刷新
const handleRefresh = () => {
  getOrganizations()
}

// 分页处理
const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  getOrganizations()
}

const handleCurrentChange = (page) => {
  pagination.page = page
  getOrganizations()
}

// 创建
const handleCreate = () => {
  router.push('/organizations/create')
}

// 新增下级组织
const handleCreateSub = (row) => {
  router.push(`/organizations/create?parent_id=${row.id}`)
}

// 查看
const handleView = async (row) => {
  try {
    await organizationStore.getOrganization(row.id)
    currentOrganization.value = organizationStore.currentOrganization
    detailDialogVisible.value = true
  } catch (error) {
    console.error('Get organization detail error:', error)
  }
}

// 编辑
const handleEdit = (row) => {
  router.push(`/organizations/${row.id}/edit`)
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除组织机构"${row.name}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    await organizationStore.deleteOrganization(row.id)
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Delete organization error:', error)
    }
  }
}

// 获取级别名称
const getLevelName = (level) => {
  const levelMap = {
    1: '省级',
    2: '市级',
    3: '区县级',
    4: '学区级',
    5: '学校级'
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
const canEditOrganization = (row) => {
  // 调试信息
  console.log('canEditOrganization check:', {
    row: row,
    user: authStore.user,
    userRoles: authStore.userRoles,
    hasSystemAdmin: authStore.hasRole('system_admin')
  })

  // 系统管理员可以编辑所有组织
  if (authStore.hasRole('system_admin')) {
    return true
  }

  // 临时放宽权限：如果用户已登录，允许编辑操作
  if (authStore.isAuthenticated) {
    return true
  }

  // 其他角色根据级别判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  // 获取用户最高角色级别
  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  // 只能编辑同级或下级组织
  return row.level >= userMaxLevel
}

const canDeleteOrganization = (row) => {
  // 调试信息
  console.log('canDeleteOrganization check:', {
    row: row,
    user: authStore.user,
    userRoles: authStore.userRoles,
    hasSystemAdmin: authStore.hasRole('system_admin')
  })

  // 系统管理员可以删除所有组织
  if (authStore.hasRole('system_admin')) {
    return true
  }

  // 临时放宽权限：如果用户已登录，允许删除操作
  if (authStore.isAuthenticated) {
    return true
  }

  // 其他角色根据级别判断，只能删除下级组织
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  // 只能删除下级组织
  return row.level > userMaxLevel
}

const canCreateSubOrganization = (row) => {
  // 系统管理员可以在任何组织下创建子组织
  if (authStore.hasRole('system_admin')) {
    return row.level < 5 // 学校级别下不能再创建子组织
  }

  // 其他角色根据级别判断
  const userRoles = authStore.userRoles
  if (!userRoles || userRoles.length === 0) return false

  const userMaxLevel = Math.min(...userRoles.map(role => role.level))

  // 只能在同级或下级组织下创建子组织，且不能超过学校级别
  return row.level >= userMaxLevel && row.level < 5
}



onMounted(() => {
  getOrganizations()
})
</script>

<style scoped>
.organization-list {
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

.organization-name {
  font-weight: 500;
  margin-right: 8px;
}

.pagination-wrapper {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.table-info {
  display: flex;
  align-items: center;
}

/* 树形表格样式优化 */
:deep(.el-table__expand-icon) {
  color: #409eff;
}

:deep(.el-table__row) {
  cursor: pointer;
}

:deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}
</style> 