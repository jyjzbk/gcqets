<template>
  <div class="permission-templates">
    <div class="templates-header">
      <div class="header-left">
        <h3>权限模板管理</h3>
        <el-text type="info" size="small">
          创建和管理权限模板，快速应用预定义的权限配置
        </el-text>
      </div>
      <div class="header-right">
        <el-button @click="refreshData" :loading="loading">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <el-button @click="showCreateDialog" type="primary">
          <el-icon><Plus /></el-icon>
          创建模板
        </el-button>
      </div>
    </div>

    <div class="templates-content">
      <!-- 筛选条件 -->
      <div class="filter-bar">
        <el-form :model="filters" inline>
          <el-form-item label="模板类型">
            <el-select v-model="filters.template_type" clearable @change="loadTemplates">
              <el-option label="角色模板" value="role" />
              <el-option label="用户模板" value="user" />
              <el-option label="组织模板" value="organization" />
            </el-select>
          </el-form-item>
          <el-form-item label="目标级别">
            <el-select v-model="filters.target_level" clearable @change="loadTemplates">
              <el-option label="省级" :value="1" />
              <el-option label="市级" :value="2" />
              <el-option label="区县级" :value="3" />
              <el-option label="学区级" :value="4" />
              <el-option label="学校级" :value="5" />
            </el-select>
          </el-form-item>
          <el-form-item label="模板状态">
            <el-select v-model="filters.status" @change="loadTemplates">
              <el-option label="全部" value="" />
              <el-option label="启用" value="active" />
              <el-option label="禁用" value="inactive" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-input
              v-model="filters.search"
              placeholder="搜索模板名称"
              clearable
              @clear="loadTemplates"
              @keyup.enter="loadTemplates"
              style="width: 200px;"
            >
              <template #append>
                <el-button @click="loadTemplates">
                  <el-icon><Search /></el-icon>
                </el-button>
              </template>
            </el-input>
          </el-form-item>
        </el-form>
      </div>

      <!-- 模板列表 -->
      <div class="templates-grid" v-loading="loading">
        <div
          v-for="template in templates"
          :key="template.id"
          class="template-card"
          :class="{ 'system-template': template.is_system }"
        >
          <div class="card-header">
            <div class="template-info">
              <h4 class="template-name">{{ template.display_name }}</h4>
              <div class="template-meta">
                <el-tag :type="getTemplateTypeTag(template.template_type)" size="small">
                  {{ getTemplateTypeText(template.template_type) }}
                </el-tag>
                <el-tag v-if="template.target_level" size="small">
                  {{ getLevelText(template.target_level) }}
                </el-tag>
                <el-tag v-if="template.is_system" type="info" size="small">
                  系统模板
                </el-tag>
                <el-tag v-if="template.is_default" type="success" size="small">
                  默认模板
                </el-tag>
              </div>
            </div>
            <div class="card-actions">
              <el-dropdown @command="handleTemplateAction">
                <el-button text>
                  <el-icon><MoreFilled /></el-icon>
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item :command="{ action: 'view', template }">
                      查看详情
                    </el-dropdown-item>
                    <el-dropdown-item :command="{ action: 'apply', template }">
                      应用模板
                    </el-dropdown-item>
                    <el-dropdown-item :command="{ action: 'duplicate', template }">
                      复制模板
                    </el-dropdown-item>
                    <el-dropdown-item
                      v-if="!template.is_system"
                      :command="{ action: 'edit', template }"
                    >
                      编辑模板
                    </el-dropdown-item>
                    <el-dropdown-item
                      v-if="!template.is_system"
                      :command="{ action: 'delete', template }"
                      divided
                    >
                      删除模板
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </div>
          
          <div class="card-content">
            <p class="template-description">{{ template.description || '暂无描述' }}</p>
            <div class="template-stats">
              <div class="stat-item">
                <span class="stat-label">权限数量:</span>
                <span class="stat-value">{{ template.permission_count || 0 }}</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">创建时间:</span>
                <span class="stat-value">{{ formatDate(template.created_at) }}</span>
              </div>
            </div>
          </div>
          
          <div class="card-footer">
            <el-button size="small" @click="viewTemplate(template)">
              查看详情
            </el-button>
            <el-button size="small" type="primary" @click="applyTemplate(template)">
              应用模板
            </el-button>
          </div>
        </div>
      </div>

      <!-- 分页 -->
      <div class="pagination">
        <el-pagination
          v-model:current-page="pagination.current_page"
          v-model:page-size="pagination.per_page"
          :total="pagination.total"
          :page-sizes="[12, 24, 48]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="loadTemplates"
          @current-change="loadTemplates"
        />
      </div>
    </div>

    <!-- 创建/编辑模板对话框 -->
    <el-dialog
      v-model="templateDialogVisible"
      :title="isEditing ? '编辑权限模板' : '创建权限模板'"
      width="800px"
    >
      <el-form :model="templateForm" :rules="templateRules" ref="templateFormRef" label-width="100px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="模板名称" prop="name">
              <el-input v-model="templateForm.name" placeholder="请输入模板名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="显示名称" prop="display_name">
              <el-input v-model="templateForm.display_name" placeholder="请输入显示名称" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="模板类型" prop="template_type">
              <el-select v-model="templateForm.template_type" placeholder="请选择模板类型">
                <el-option label="角色模板" value="role" />
                <el-option label="用户模板" value="user" />
                <el-option label="组织模板" value="organization" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="目标级别">
              <el-select v-model="templateForm.target_level" placeholder="请选择目标级别" clearable>
                <el-option label="省级" :value="1" />
                <el-option label="市级" :value="2" />
                <el-option label="区县级" :value="3" />
                <el-option label="学区级" :value="4" />
                <el-option label="学校级" :value="5" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="模板描述">
          <el-input
            v-model="templateForm.description"
            type="textarea"
            :rows="3"
            placeholder="请输入模板描述"
          />
        </el-form-item>
        
        <el-form-item label="权限配置" prop="permission_ids">
          <div class="permission-selection">
            <el-tree
              ref="permissionTreeRef"
              :data="permissionTree"
              :props="{ children: 'children', label: 'display_name' }"
              show-checkbox
              node-key="id"
              :default-checked-keys="templateForm.permission_ids"
              @check="handlePermissionCheck"
            />
          </div>
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="是否默认">
              <el-switch v-model="templateForm.is_default" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="状态">
              <el-select v-model="templateForm.status">
                <el-option label="启用" value="active" />
                <el-option label="禁用" value="inactive" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      
      <template #footer>
        <el-button @click="templateDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveTemplate" :loading="saving">
          {{ isEditing ? '更新' : '创建' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- 应用模板对话框 -->
    <el-dialog
      v-model="applyDialogVisible"
      title="应用权限模板"
      width="600px"
    >
      <div v-if="selectedTemplate">
        <el-alert
          :title="`将应用模板: ${selectedTemplate.display_name}`"
          type="info"
          :closable="false"
          style="margin-bottom: 20px;"
        />
        
        <el-form :model="applyForm" label-width="100px">
          <el-form-item label="应用到" required>
            <el-radio-group v-model="applyForm.target_type">
              <el-radio label="role">角色</el-radio>
              <el-radio label="user">用户</el-radio>
            </el-radio-group>
          </el-form-item>
          
          <el-form-item :label="applyForm.target_type === 'role' ? '选择角色' : '选择用户'" required>
            <el-select
              v-model="applyForm.target_id"
              :placeholder="`请选择${applyForm.target_type === 'role' ? '角色' : '用户'}`"
              filterable
              style="width: 100%;"
            >
              <el-option
                v-for="target in applyTargets"
                :key="target.id"
                :label="target.name"
                :value="target.id"
              />
            </el-select>
          </el-form-item>
          
          <el-form-item v-if="applyForm.target_type === 'user'" label="组织范围">
            <el-select v-model="applyForm.organization_id" placeholder="请选择组织" clearable>
              <el-option
                v-for="org in organizationOptions"
                :key="org.id"
                :label="org.name"
                :value="org.id"
              />
            </el-select>
          </el-form-item>
        </el-form>
      </div>
      
      <template #footer>
        <el-button @click="applyDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          @click="confirmApplyTemplate"
          :disabled="!applyForm.target_id"
          :loading="applying"
        >
          应用模板
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { permissionTemplateApi } from '@/api/permissionVisualization'
import { permissionApi } from '@/api/permission'
import { roleApi } from '@/api/role'
import { userApi } from '@/api/user'
import { organizationApi } from '@/api/organization'
import dayjs from 'dayjs'

// 响应式数据
const loading = ref(false)
const saving = ref(false)
const applying = ref(false)
const templates = ref([])
const permissionTree = ref([])
const applyTargets = ref([])
const organizationOptions = ref([])
const templateDialogVisible = ref(false)
const applyDialogVisible = ref(false)
const isEditing = ref(false)
const selectedTemplate = ref(null)

// 表单数据
const filters = reactive({
  template_type: '',
  target_level: null,
  status: 'active',
  search: ''
})

const templateForm = reactive({
  name: '',
  display_name: '',
  description: '',
  template_type: 'role',
  target_level: null,
  permission_ids: [],
  is_default: false,
  status: 'active'
})

const applyForm = reactive({
  target_type: 'role',
  target_id: null,
  organization_id: null
})

// 分页
const pagination = reactive({
  current_page: 1,
  per_page: 12,
  total: 0
})

// 表单验证规则
const templateRules = {
  name: [
    { required: true, message: '请输入模板名称', trigger: 'blur' }
  ],
  display_name: [
    { required: true, message: '请输入显示名称', trigger: 'blur' }
  ],
  template_type: [
    { required: true, message: '请选择模板类型', trigger: 'change' }
  ],
  permission_ids: [
    { required: true, message: '请选择权限', trigger: 'change' }
  ]
}

// 组件引用
const templateFormRef = ref(null)
const permissionTreeRef = ref(null)

// 监听器
watch(() => applyForm.target_type, () => {
  applyForm.target_id = null
  loadApplyTargets()
})

// 方法
const loadTemplates = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      per_page: pagination.per_page,
      ...filters
    }
    
    const response = await permissionTemplateApi.getList(params)
    templates.value = response.data.data
    pagination.total = response.data.total
    pagination.current_page = response.data.current_page
  } catch (error) {
    console.error('加载权限模板失败:', error)
    ElMessage.error('加载权限模板失败')
  } finally {
    loading.value = false
  }
}

const loadPermissionTree = async () => {
  try {
    const response = await permissionApi.getTree()
    permissionTree.value = response.data
  } catch (error) {
    console.error('加载权限树失败:', error)
  }
}

const loadApplyTargets = async () => {
  try {
    let response
    if (applyForm.target_type === 'role') {
      response = await roleApi.getList({ per_page: 100 })
      applyTargets.value = response.data.data.map(role => ({
        id: role.id,
        name: role.display_name
      }))
    } else {
      response = await userApi.getList({ per_page: 100 })
      applyTargets.value = response.data.data.map(user => ({
        id: user.id,
        name: user.name || user.username
      }))
    }
  } catch (error) {
    console.error('加载应用目标失败:', error)
  }
}

const loadOrganizations = async () => {
  try {
    const response = await organizationApi.getTree()
    organizationOptions.value = flattenOrganizations(response.data)
  } catch (error) {
    console.error('加载组织列表失败:', error)
  }
}

const flattenOrganizations = (orgs, result = []) => {
  orgs.forEach(org => {
    result.push({
      id: org.id,
      name: org.name
    })
    if (org.children && org.children.length > 0) {
      flattenOrganizations(org.children, result)
    }
  })
  return result
}

const refreshData = () => {
  loadTemplates()
}

const showCreateDialog = () => {
  isEditing.value = false
  resetTemplateForm()
  templateDialogVisible.value = true
}

const resetTemplateForm = () => {
  Object.assign(templateForm, {
    name: '',
    display_name: '',
    description: '',
    template_type: 'role',
    target_level: null,
    permission_ids: [],
    is_default: false,
    status: 'active'
  })
}

const handlePermissionCheck = (data, checked) => {
  const checkedKeys = permissionTreeRef.value.getCheckedKeys()
  templateForm.permission_ids = checkedKeys
}

const saveTemplate = async () => {
  try {
    await templateFormRef.value.validate()
    
    saving.value = true
    
    if (isEditing.value) {
      await permissionTemplateApi.update(selectedTemplate.value.id, templateForm)
      ElMessage.success('更新权限模板成功')
    } else {
      await permissionTemplateApi.create(templateForm)
      ElMessage.success('创建权限模板成功')
    }
    
    templateDialogVisible.value = false
    loadTemplates()
  } catch (error) {
    if (error.errors) {
      // 表单验证错误
      return
    }
    console.error('保存权限模板失败:', error)
    ElMessage.error('保存权限模板失败')
  } finally {
    saving.value = false
  }
}

const viewTemplate = (template) => {
  // 实现查看模板详情
  ElMessage.info('查看模板详情功能待实现')
}

const applyTemplate = (template) => {
  selectedTemplate.value = template
  applyForm.target_type = template.template_type
  applyForm.target_id = null
  applyForm.organization_id = null
  applyDialogVisible.value = true
  loadApplyTargets()
}

const confirmApplyTemplate = async () => {
  try {
    applying.value = true
    
    const data = {}
    if (applyForm.target_type === 'role') {
      data.role_id = applyForm.target_id
      await permissionTemplateApi.applyToRole(selectedTemplate.value.id, data)
    } else {
      data.user_id = applyForm.target_id
      if (applyForm.organization_id) {
        data.organization_id = applyForm.organization_id
      }
      await permissionTemplateApi.applyToUser(selectedTemplate.value.id, data)
    }
    
    ElMessage.success('应用权限模板成功')
    applyDialogVisible.value = false
  } catch (error) {
    console.error('应用权限模板失败:', error)
    ElMessage.error('应用权限模板失败')
  } finally {
    applying.value = false
  }
}

const handleTemplateAction = ({ action, template }) => {
  switch (action) {
    case 'view':
      viewTemplate(template)
      break
    case 'apply':
      applyTemplate(template)
      break
    case 'duplicate':
      duplicateTemplate(template)
      break
    case 'edit':
      editTemplate(template)
      break
    case 'delete':
      deleteTemplate(template)
      break
  }
}

const editTemplate = (template) => {
  isEditing.value = true
  selectedTemplate.value = template
  Object.assign(templateForm, template)
  templateDialogVisible.value = true
}

const duplicateTemplate = async (template) => {
  try {
    const { value: newName } = await ElMessageBox.prompt(
      '请输入新模板名称',
      '复制权限模板',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        inputValue: template.name + '_copy'
      }
    )
    
    await permissionTemplateApi.duplicate(template.id, {
      name: newName,
      display_name: template.display_name + ' (副本)'
    })
    
    ElMessage.success('复制权限模板成功')
    loadTemplates()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('复制权限模板失败:', error)
      ElMessage.error('复制权限模板失败')
    }
  }
}

const deleteTemplate = async (template) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除权限模板 "${template.display_name}" 吗？`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    await permissionTemplateApi.delete(template.id)
    ElMessage.success('删除权限模板成功')
    loadTemplates()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除权限模板失败:', error)
      ElMessage.error('删除权限模板失败')
    }
  }
}

// 工具方法
const getTemplateTypeTag = (type) => {
  const tagMap = {
    role: 'primary',
    user: 'success',
    organization: 'warning'
  }
  return tagMap[type] || 'info'
}

const getTemplateTypeText = (type) => {
  const textMap = {
    role: '角色模板',
    user: '用户模板',
    organization: '组织模板'
  }
  return textMap[type] || type
}

const getLevelText = (level) => {
  const levelMap = {
    1: '省级',
    2: '市级',
    3: '区县级',
    4: '学区级',
    5: '学校级'
  }
  return levelMap[level] || `${level}级`
}

const formatDate = (date) => {
  return dayjs(date).format('YYYY-MM-DD')
}

// 生命周期
onMounted(() => {
  loadTemplates()
  loadPermissionTree()
  loadOrganizations()
})

// 暴露给父组件的方法
defineExpose({
  refreshData
})
</script>

<style scoped>
.permission-templates {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.templates-header {
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
  gap: 10px;
}

.templates-content {
  flex: 1;
  padding: 20px;
  overflow: auto;
}

.filter-bar {
  margin-bottom: 20px;
  padding: 15px;
  background: #f5f7fa;
  border-radius: 4px;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.template-card {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  background: #fff;
  transition: all 0.3s;
}

.template-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.template-card.system-template {
  border-color: #409eff;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 15px;
  border-bottom: 1px solid #f5f7fa;
}

.template-name {
  margin: 0 0 8px 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
}

.template-meta {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
}

.card-content {
  padding: 15px;
}

.template-description {
  margin: 0 0 15px 0;
  color: #606266;
  line-height: 1.5;
  font-size: 14px;
}

.template-stats {
  display: flex;
  justify-content: space-between;
}

.stat-item {
  font-size: 12px;
}

.stat-label {
  color: #909399;
}

.stat-value {
  color: #303133;
  font-weight: 500;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  padding: 15px;
  border-top: 1px solid #f5f7fa;
}

.pagination {
  text-align: center;
}

.permission-selection {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 10px;
}
</style>
