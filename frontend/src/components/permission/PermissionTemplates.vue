<template>
  <div class="permission-templates">
    <!-- 工具栏 -->
    <div class="toolbar">
      <div class="toolbar-left">
        <el-button type="primary" :icon="Plus" @click="showCreateDialog = true">
          创建模板
        </el-button>
        <el-button type="success" :icon="Download" @click="exportTemplates">
          导出模板
        </el-button>
        <el-button type="warning" :icon="Upload" @click="showImportDialog = true">
          导入模板
        </el-button>
      </div>

      <div class="toolbar-right">
        <el-input
          v-model="searchKeyword"
          placeholder="搜索模板名称或描述"
          :prefix-icon="Search"
          clearable
          @input="handleSearch"
          style="width: 250px"
        />
        <el-select
          v-model="filterLevel"
          placeholder="筛选级别"
          clearable
          @change="handleFilter"
          style="width: 120px; margin-left: 8px"
        >
          <el-option label="省级" :value="1" />
          <el-option label="市级" :value="2" />
          <el-option label="区县级" :value="3" />
          <el-option label="学区级" :value="4" />
          <el-option label="学校级" :value="5" />
        </el-select>
        <el-button :icon="Refresh" @click="loadTemplates">刷新</el-button>
      </div>
    </div>

    <!-- 模板列表 -->
    <div class="templates-container" v-loading="loading">
      <el-row :gutter="16">
        <el-col
          v-for="template in filteredTemplates"
          :key="template.id"
          :xs="24"
          :sm="12"
          :md="8"
          :lg="6"
          :xl="4"
        >
          <el-card class="template-card" :class="{ 'system-template': template.is_system }">
            <template #header>
              <div class="card-header">
                <div class="template-info">
                  <h4 class="template-name">{{ template.name }}</h4>
                  <div class="template-meta">
                    <el-tag
                      :type="getLevelTagType(template.level)"
                      size="small"
                    >
                      {{ getLevelText(template.level) }}
                    </el-tag>
                    <el-tag
                      v-if="template.is_system"
                      type="info"
                      size="small"
                    >
                      系统
                    </el-tag>
                    <el-tag
                      :type="template.is_active ? 'success' : 'danger'"
                      size="small"
                    >
                      {{ template.is_active ? '启用' : '禁用' }}
                    </el-tag>
                  </div>
                </div>
                <el-dropdown @command="handleTemplateAction">
                  <el-button type="text" :icon="MoreFilled" />
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item :command="{ action: 'view', template }">
                        查看详情
                      </el-dropdown-item>
                      <el-dropdown-item
                        :command="{ action: 'edit', template }"
                        :disabled="template.is_system"
                      >
                        编辑模板
                      </el-dropdown-item>
                      <el-dropdown-item :command="{ action: 'copy', template }">
                        复制模板
                      </el-dropdown-item>
                      <el-dropdown-item :command="{ action: 'apply', template }">
                        应用模板
                      </el-dropdown-item>
                      <el-dropdown-item
                        :command="{ action: 'toggle', template }"
                        :disabled="template.is_system"
                      >
                        {{ template.is_active ? '禁用' : '启用' }}
                      </el-dropdown-item>
                      <el-dropdown-item
                        :command="{ action: 'delete', template }"
                        :disabled="template.is_system"
                        divided
                      >
                        删除模板
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </div>
            </template>

            <div class="template-content">
              <p class="template-description">
                {{ template.description || '暂无描述' }}
              </p>

              <div class="template-stats">
                <div class="stat-item">
                  <span class="stat-label">权限数量:</span>
                  <span class="stat-value">{{ template.permissions_count || 0 }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">应用次数:</span>
                  <span class="stat-value">{{ template.usage_count || 0 }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">版本:</span>
                  <span class="stat-value">{{ template.version || '1.0.0' }}</span>
                </div>
              </div>

              <div class="template-footer">
                <div class="creator-info">
                  <el-avatar :size="20" :src="template.creator?.avatar" />
                  <span class="creator-name">{{ template.creator?.name || '系统' }}</span>
                </div>
                <div class="create-time">
                  {{ formatDate(template.created_at) }}
                </div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>

      <el-empty v-if="filteredTemplates.length === 0 && !loading" description="暂无权限模板" />
    </div>

    <!-- 创建/编辑模板对话框 -->
    <el-dialog
      v-model="showCreateDialog"
      :title="editingTemplate ? '编辑模板' : '创建模板'"
      width="800px"
      @close="resetForm"
    >
      <el-form
        ref="templateFormRef"
        :model="templateForm"
        :rules="templateRules"
        label-width="100px"
      >
        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="模板名称" prop="name">
              <el-input v-model="templateForm.name" placeholder="请输入模板名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="模板代码" prop="code">
              <el-input
                v-model="templateForm.code"
                placeholder="请输入模板代码"
                :disabled="!!editingTemplate"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="16">
          <el-col :span="12">
            <el-form-item label="适用级别" prop="level">
              <el-select v-model="templateForm.level" placeholder="选择适用级别">
                <el-option label="省级" :value="1" />
                <el-option label="市级" :value="2" />
                <el-option label="区县级" :value="3" />
                <el-option label="学区级" :value="4" />
                <el-option label="学校级" :value="5" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="模板版本" prop="version">
              <el-input v-model="templateForm.version" placeholder="如: 1.0.0" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="模板描述" prop="description">
          <el-input
            v-model="templateForm.description"
            type="textarea"
            :rows="3"
            placeholder="请输入模板描述"
          />
        </el-form-item>

        <el-form-item label="适用组织">
          <el-select
            v-model="templateForm.applicable_org_types"
            multiple
            placeholder="选择适用的组织类型"
            style="width: 100%"
          >
            <el-option label="省级" value="province" />
            <el-option label="市级" value="city" />
            <el-option label="区县级" value="district" />
            <el-option label="学区级" value="education_zone" />
            <el-option label="学校级" value="school" />
          </el-select>
        </el-form-item>

        <el-form-item label="权限配置">
          <div class="permission-config">
            <el-transfer
              v-model="templateForm.permission_ids"
              :data="availablePermissions"
              :titles="['可用权限', '模板权限']"
              filterable
              filter-placeholder="搜索权限"
              :props="{ key: 'id', label: 'display_name' }"
            />
          </div>
        </el-form-item>

        <el-form-item label="状态">
          <el-switch
            v-model="templateForm.is_active"
            active-text="启用"
            inactive-text="禁用"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="showCreateDialog = false">取消</el-button>
        <el-button type="primary" @click="saveTemplate" :loading="saving">
          {{ editingTemplate ? '更新' : '创建' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- 模板详情对话框 -->
    <el-dialog
      v-model="showDetailDialog"
      title="模板详情"
      width="900px"
    >
      <div class="template-detail" v-if="selectedTemplate">
        <el-descriptions :column="3" border>
          <el-descriptions-item label="模板名称">
            {{ selectedTemplate.name }}
          </el-descriptions-item>
          <el-descriptions-item label="模板代码">
            {{ selectedTemplate.code }}
          </el-descriptions-item>
          <el-descriptions-item label="适用级别">
            <el-tag :type="getLevelTagType(selectedTemplate.level)">
              {{ getLevelText(selectedTemplate.level) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="模板版本">
            {{ selectedTemplate.version }}
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="selectedTemplate.is_active ? 'success' : 'danger'">
              {{ selectedTemplate.is_active ? '启用' : '禁用' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="系统模板">
            <el-tag :type="selectedTemplate.is_system ? 'info' : 'primary'">
              {{ selectedTemplate.is_system ? '是' : '否' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="创建者">
            {{ selectedTemplate.creator?.name || '系统' }}
          </el-descriptions-item>
          <el-descriptions-item label="创建时间">
            {{ formatDate(selectedTemplate.created_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="更新时间">
            {{ formatDate(selectedTemplate.updated_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="模板描述" span="3">
            {{ selectedTemplate.description || '暂无描述' }}
          </el-descriptions-item>
        </el-descriptions>

        <div class="template-permissions">
          <h4>包含权限 ({{ selectedTemplate.permissions?.length || 0 }}个)</h4>
          <el-table :data="selectedTemplate.permissions" stripe max-height="300">
            <el-table-column prop="display_name" label="权限名称" />
            <el-table-column prop="module" label="模块" width="120" />
            <el-table-column prop="action" label="操作" width="120" />
            <el-table-column prop="min_level" label="最小级别" width="100">
              <template #default="{ row }">
                <el-tag :type="getLevelTagType(row.min_level)" size="small">
                  L{{ row.min_level }}
                </el-tag>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>
    </el-dialog>

    <!-- 应用模板对话框 -->
    <el-dialog
      v-model="showApplyDialog"
      title="应用权限模板"
      width="700px"
    >
      <div class="apply-template-content">
        <div class="template-info">
          <h4>模板信息</h4>
          <el-descriptions :column="2" size="small">
            <el-descriptions-item label="模板名称">
              {{ applyingTemplate?.name }}
            </el-descriptions-item>
            <el-descriptions-item label="权限数量">
              {{ applyingTemplate?.permissions?.length || 0 }}
            </el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="apply-targets">
          <h4>应用目标</h4>
          <el-radio-group v-model="applyForm.target_type">
            <el-radio label="user">用户</el-radio>
            <el-radio label="role">角色</el-radio>
            <el-radio label="organization">组织</el-radio>
          </el-radio-group>

          <div class="target-selection" style="margin-top: 12px;">
            <el-select
              v-if="applyForm.target_type === 'user'"
              v-model="applyForm.target_ids"
              multiple
              filterable
              placeholder="选择用户"
              style="width: 100%"
            >
              <el-option
                v-for="user in users"
                :key="user.id"
                :label="user.name"
                :value="user.id"
              />
            </el-select>

            <el-select
              v-if="applyForm.target_type === 'role'"
              v-model="applyForm.target_ids"
              multiple
              filterable
              placeholder="选择角色"
              style="width: 100%"
            >
              <el-option
                v-for="role in roles"
                :key="role.id"
                :label="role.display_name"
                :value="role.id"
              />
            </el-select>

            <el-select
              v-if="applyForm.target_type === 'organization'"
              v-model="applyForm.target_ids"
              multiple
              filterable
              placeholder="选择组织"
              style="width: 100%"
            >
              <el-option
                v-for="org in organizations"
                :key="org.id"
                :label="org.name"
                :value="org.id"
              />
            </el-select>
          </div>
        </div>

        <div class="apply-options">
          <h4>应用选项</h4>
          <el-form :model="applyForm" label-width="100px">
            <el-form-item label="应用模式">
              <el-radio-group v-model="applyForm.apply_mode">
                <el-radio label="merge">合并权限</el-radio>
                <el-radio label="replace">替换权限</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="应用原因">
              <el-input
                v-model="applyForm.reason"
                type="textarea"
                placeholder="请输入应用原因（可选）"
                :rows="3"
              />
            </el-form-item>
            <el-form-item label="有效期">
              <el-date-picker
                v-model="applyForm.expires_at"
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
        <el-button @click="showApplyDialog = false">取消</el-button>
        <el-button type="primary" @click="confirmApplyTemplate" :loading="applying">
          应用模板
        </el-button>
      </template>
    </el-dialog>

    <!-- 导入模板对话框 -->
    <el-dialog
      v-model="showImportDialog"
      title="导入权限模板"
      width="600px"
    >
      <div class="import-content">
        <el-upload
          ref="uploadRef"
          :auto-upload="false"
          :on-change="handleFileChange"
          :before-remove="handleFileRemove"
          accept=".json"
          drag
        >
          <el-icon class="el-icon--upload"><UploadFilled /></el-icon>
          <div class="el-upload__text">
            将文件拖到此处，或<em>点击上传</em>
          </div>
          <template #tip>
            <div class="el-upload__tip">
              只能上传 JSON 格式的模板文件
            </div>
          </template>
        </el-upload>

        <div class="import-options" v-if="importFile">
          <h4>导入选项</h4>
          <el-checkbox v-model="importForm.overwrite_existing">
            覆盖同名模板
          </el-checkbox>
          <el-checkbox v-model="importForm.validate_permissions">
            验证权限有效性
          </el-checkbox>
        </div>
      </div>

      <template #footer>
        <el-button @click="showImportDialog = false">取消</el-button>
        <el-button
          type="primary"
          @click="confirmImportTemplates"
          :loading="importing"
          :disabled="!importFile"
        >
          导入
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Plus,
  Download,
  Upload,
  Search,
  Refresh,
  MoreFilled,
  UploadFilled
} from '@element-plus/icons-vue'
import { permissionTemplateApi } from '../../api/permissionVisualization'
import { permissionApi } from '../../api/permission'
import { organizationApi } from '../../api/organization'
import { userApi } from '../../api/user'
import { roleApi } from '../../api/role'

// 响应式数据
const loading = ref(false)
const saving = ref(false)
const applying = ref(false)
const importing = ref(false)

const templates = ref([])
const availablePermissions = ref([])
const users = ref([])
const roles = ref([])
const organizations = ref([])

const searchKeyword = ref('')
const filterLevel = ref(null)

// 对话框状态
const showCreateDialog = ref(false)
const showDetailDialog = ref(false)
const showApplyDialog = ref(false)
const showImportDialog = ref(false)

// 编辑状态
const editingTemplate = ref(null)
const selectedTemplate = ref(null)
const applyingTemplate = ref(null)

// 表单数据
const templateForm = reactive({
  name: '',
  code: '',
  description: '',
  level: 5,
  version: '1.0.0',
  applicable_org_types: [],
  permission_ids: [],
  is_active: true
})

const applyForm = reactive({
  target_type: 'user',
  target_ids: [],
  apply_mode: 'merge',
  reason: '',
  expires_at: null
})

const importForm = reactive({
  overwrite_existing: false,
  validate_permissions: true
})

const importFile = ref(null)
const uploadRef = ref(null)
const templateFormRef = ref(null)

// 表单验证规则
const templateRules = {
  name: [
    { required: true, message: '请输入模板名称', trigger: 'blur' },
    { min: 2, max: 100, message: '长度在 2 到 100 个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入模板代码', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_-]+$/, message: '只能包含字母、数字、下划线和横线', trigger: 'blur' }
  ],
  level: [
    { required: true, message: '请选择适用级别', trigger: 'change' }
  ],
  version: [
    { required: true, message: '请输入版本号', trigger: 'blur' },
    { pattern: /^\d+\.\d+\.\d+$/, message: '版本号格式应为 x.y.z', trigger: 'blur' }
  ]
}

// 计算属性
const filteredTemplates = computed(() => {
  let result = templates.value

  // 关键词搜索
  if (searchKeyword.value) {
    const keyword = searchKeyword.value.toLowerCase()
    result = result.filter(template =>
      template.name.toLowerCase().includes(keyword) ||
      (template.description && template.description.toLowerCase().includes(keyword))
    )
  }

  // 级别筛选
  if (filterLevel.value !== null) {
    result = result.filter(template => template.level === filterLevel.value)
  }

  return result
})

// 组件挂载时初始化
onMounted(() => {
  loadBasicData()
  loadTemplates()
})

// 加载基础数据
const loadBasicData = async () => {
  try {
    const [permRes, userRes, roleRes, orgRes] = await Promise.all([
      permissionApi.getList(),
      userApi.getList(),
      roleApi.getList(),
      organizationApi.getList()
    ])

    availablePermissions.value = (permRes.data.data || []).map(p => ({
      id: p.id,
      display_name: p.display_name,
      module: p.module,
      action: p.action,
      min_level: p.min_level
    }))

    users.value = userRes.data.data || []
    roles.value = roleRes.data.data || []
    organizations.value = orgRes.data.data || []
  } catch (error) {
    console.error('加载基础数据失败:', error)
    ElMessage.error('加载基础数据失败')
  }
}

// 加载权限模板
const loadTemplates = async () => {
  loading.value = true
  try {
    const response = await permissionTemplateApi.getList()
    templates.value = response.data.data || []
  } catch (error) {
    console.error('加载权限模板失败:', error)
    ElMessage.error('加载权限模板失败')
  } finally {
    loading.value = false
  }
}

// 处理搜索
const handleSearch = () => {
  // 搜索逻辑在计算属性中处理
}

// 处理筛选
const handleFilter = () => {
  // 筛选逻辑在计算属性中处理
}

// 处理模板操作
const handleTemplateAction = async ({ action, template }) => {
  switch (action) {
    case 'view':
      await viewTemplate(template)
      break
    case 'edit':
      editTemplate(template)
      break
    case 'copy':
      copyTemplate(template)
      break
    case 'apply':
      applyTemplate(template)
      break
    case 'toggle':
      await toggleTemplate(template)
      break
    case 'delete':
      await deleteTemplate(template)
      break
  }
}

// 查看模板详情
const viewTemplate = async (template) => {
  try {
    const response = await permissionTemplateApi.getDetail(template.id)
    selectedTemplate.value = response.data.data
    showDetailDialog.value = true
  } catch (error) {
    console.error('获取模板详情失败:', error)
    ElMessage.error('获取模板详情失败')
  }
}

// 编辑模板
const editTemplate = (template) => {
  editingTemplate.value = template
  Object.assign(templateForm, {
    name: template.name,
    code: template.code,
    description: template.description || '',
    level: template.level,
    version: template.version || '1.0.0',
    applicable_org_types: template.applicable_org_types || [],
    permission_ids: template.permissions?.map(p => p.id) || [],
    is_active: template.is_active
  })
  showCreateDialog.value = true
}

// 复制模板
const copyTemplate = (template) => {
  editingTemplate.value = null
  Object.assign(templateForm, {
    name: `${template.name} - 副本`,
    code: `${template.code}_copy`,
    description: template.description || '',
    level: template.level,
    version: '1.0.0',
    applicable_org_types: template.applicable_org_types || [],
    permission_ids: template.permissions?.map(p => p.id) || [],
    is_active: true
  })
  showCreateDialog.value = true
}

// 应用模板
const applyTemplate = (template) => {
  applyingTemplate.value = template
  Object.assign(applyForm, {
    target_type: 'user',
    target_ids: [],
    apply_mode: 'merge',
    reason: '',
    expires_at: null
  })
  showApplyDialog.value = true
}

// 切换模板状态
const toggleTemplate = async (template) => {
  try {
    await permissionTemplateApi.update(template.id, {
      is_active: !template.is_active
    })

    template.is_active = !template.is_active
    ElMessage.success(`模板已${template.is_active ? '启用' : '禁用'}`)
  } catch (error) {
    console.error('切换模板状态失败:', error)
    ElMessage.error('切换模板状态失败')
  }
}

// 删除模板
const deleteTemplate = async (template) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除模板"${template.name}"吗？此操作不可恢复。`,
      '确认删除',
      { type: 'warning' }
    )

    await permissionTemplateApi.delete(template.id)
    ElMessage.success('模板删除成功')
    loadTemplates()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除模板失败:', error)
      ElMessage.error('删除模板失败')
    }
  }
}

// 保存模板
const saveTemplate = async () => {
  if (!templateFormRef.value) return

  try {
    await templateFormRef.value.validate()

    saving.value = true
    const data = { ...templateForm }

    if (editingTemplate.value) {
      await permissionTemplateApi.update(editingTemplate.value.id, data)
      ElMessage.success('模板更新成功')
    } else {
      await permissionTemplateApi.create(data)
      ElMessage.success('模板创建成功')
    }

    showCreateDialog.value = false
    loadTemplates()
  } catch (error) {
    if (error.errors) {
      // 表单验证错误
      return
    }
    console.error('保存模板失败:', error)
    ElMessage.error('保存模板失败')
  } finally {
    saving.value = false
  }
}

// 确认应用模板
const confirmApplyTemplate = async () => {
  if (applyForm.target_ids.length === 0) {
    ElMessage.warning('请选择应用目标')
    return
  }

  applying.value = true
  try {
    const data = {
      template_id: applyingTemplate.value.id,
      ...applyForm
    }

    await permissionTemplateApi.apply(data)
    ElMessage.success('模板应用成功')
    showApplyDialog.value = false
  } catch (error) {
    console.error('应用模板失败:', error)
    ElMessage.error('应用模板失败')
  } finally {
    applying.value = false
  }
}

// 导出模板
const exportTemplates = async () => {
  try {
    const response = await permissionTemplateApi.export()

    // 创建下载链接
    const blob = new Blob([JSON.stringify(response.data.data, null, 2)], {
      type: 'application/json'
    })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `permission_templates_${new Date().toISOString().slice(0, 10)}.json`
    link.click()
    window.URL.revokeObjectURL(url)

    ElMessage.success('模板导出成功')
  } catch (error) {
    console.error('导出模板失败:', error)
    ElMessage.error('导出模板失败')
  }
}

// 处理文件变更
const handleFileChange = (file) => {
  importFile.value = file
}

// 处理文件移除
const handleFileRemove = () => {
  importFile.value = null
}

// 确认导入模板
const confirmImportTemplates = async () => {
  if (!importFile.value) {
    ElMessage.warning('请选择要导入的文件')
    return
  }

  importing.value = true
  try {
    const formData = new FormData()
    formData.append('file', importFile.value.raw)
    formData.append('overwrite_existing', importForm.overwrite_existing)
    formData.append('validate_permissions', importForm.validate_permissions)

    await permissionTemplateApi.import(formData)
    ElMessage.success('模板导入成功')
    showImportDialog.value = false
    loadTemplates()
  } catch (error) {
    console.error('导入模板失败:', error)
    ElMessage.error('导入模板失败')
  } finally {
    importing.value = false
  }
}

// 重置表单
const resetForm = () => {
  editingTemplate.value = null
  Object.assign(templateForm, {
    name: '',
    code: '',
    description: '',
    level: 5,
    version: '1.0.0',
    applicable_org_types: [],
    permission_ids: [],
    is_active: true
  })

  if (templateFormRef.value) {
    templateFormRef.value.resetFields()
  }
}

// 获取级别标签类型
const getLevelTagType = (level) => {
  const typeMap = {
    1: 'danger',
    2: 'warning',
    3: 'primary',
    4: 'success',
    5: 'info'
  }
  return typeMap[level] || 'default'
}

// 获取级别文本
const getLevelText = (level) => {
  const textMap = {
    1: '省级',
    2: '市级',
    3: '区县级',
    4: '学区级',
    5: '学校级'
  }
  return textMap[level] || `L${level}`
}

// 格式化日期
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('zh-CN')
}
</script>

<style scoped>
.permission-templates {
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
  gap: 8px;
}

.toolbar-right {
  display: flex;
  align-items: center;
}

.templates-container {
  flex: 1;
  overflow-y: auto;
  padding: 0 4px;
}

.template-card {
  margin-bottom: 16px;
  transition: all 0.3s;
  cursor: pointer;
}

.template-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.template-card.system-template {
  border-left: 4px solid #409eff;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.template-info {
  flex: 1;
}

.template-name {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  line-height: 1.2;
}

.template-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.template-content {
  padding: 0;
}

.template-description {
  margin: 0 0 12px 0;
  color: #606266;
  font-size: 14px;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.template-stats {
  margin-bottom: 12px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
  font-size: 12px;
}

.stat-label {
  color: #909399;
}

.stat-value {
  color: #303133;
  font-weight: 500;
}

.template-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 8px;
  border-top: 1px solid #f0f0f0;
  font-size: 12px;
  color: #909399;
}

.creator-info {
  display: flex;
  align-items: center;
  gap: 6px;
}

.creator-name {
  font-size: 12px;
}

.permission-config {
  width: 100%;
}

.template-detail {
  max-height: 600px;
  overflow-y: auto;
}

.template-permissions {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e4e7ed;
}

.template-permissions h4 {
  margin: 0 0 12px 0;
  color: #303133;
}

.apply-template-content {
  max-height: 500px;
  overflow-y: auto;
}

.template-info,
.apply-targets,
.apply-options {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.template-info:last-child,
.apply-targets:last-child,
.apply-options:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.template-info h4,
.apply-targets h4,
.apply-options h4 {
  margin: 0 0 12px 0;
  color: #303133;
  font-size: 16px;
}

.target-selection {
  margin-top: 12px;
}

.import-content {
  text-align: center;
}

.import-options {
  margin-top: 20px;
  text-align: left;
  padding-top: 16px;
  border-top: 1px solid #e4e7ed;
}

.import-options h4 {
  margin: 0 0 12px 0;
  color: #303133;
}

.el-upload__tip {
  margin-top: 8px;
  color: #606266;
  font-size: 12px;
}
</style>