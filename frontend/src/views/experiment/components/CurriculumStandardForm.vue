<template>
  <el-dialog
    v-model="dialogVisible"
    :title="dialogTitle"
    width="800px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="120px"
      :disabled="mode === 'view'"
    >
      <el-tabs v-model="activeTab" type="border-card">
        <!-- 基本信息 -->
        <el-tab-pane label="基本信息" name="basic">
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="标准名称" prop="name">
                <el-input v-model="form.name" placeholder="请输入课程标准名称" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="标准编码" prop="code">
                <el-input v-model="form.code" placeholder="请输入标准编码" />
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="版本号" prop="version">
                <el-input v-model="form.version" placeholder="如：2022.1" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="学科" prop="subject">
                <el-select v-model="form.subject" placeholder="请选择学科" style="width: 100%">
                  <el-option
                    v-for="(label, value) in options.subjects"
                    :key="value"
                    :label="label"
                    :value="value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="年级" prop="grade">
                <el-select v-model="form.grade" placeholder="请选择年级" style="width: 100%">
                  <el-option
                    v-for="(label, value) in options.grades"
                    :key="value"
                    :label="label"
                    :value="value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="生效日期">
                <el-date-picker
                  v-model="form.effective_date"
                  type="date"
                  placeholder="选择生效日期"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="失效日期">
                <el-date-picker
                  v-model="form.expiry_date"
                  type="date"
                  placeholder="选择失效日期"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="状态" prop="status">
                <el-select v-model="form.status" placeholder="请选择状态" style="width: 100%">
                  <el-option
                    v-for="(label, value) in options.statuses"
                    :key="value"
                    :label="label"
                    :value="value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-form-item label="标准内容">
            <el-input
              v-model="form.content"
              type="textarea"
              :rows="4"
              placeholder="请输入课程标准的主要内容"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 详细内容 -->
        <el-tab-pane label="详细内容" name="content">
          <el-form-item label="学习目标">
            <el-input
              v-model="form.learning_objectives"
              type="textarea"
              :rows="4"
              placeholder="请输入学习目标"
            />
          </el-form-item>

          <el-form-item label="核心概念">
            <el-input
              v-model="form.key_concepts"
              type="textarea"
              :rows="4"
              placeholder="请输入核心概念"
            />
          </el-form-item>

          <el-form-item label="技能要求">
            <el-input
              v-model="form.skills_requirements"
              type="textarea"
              :rows="4"
              placeholder="请输入技能要求"
            />
          </el-form-item>

          <el-form-item label="评价标准">
            <el-input
              v-model="form.assessment_criteria"
              type="textarea"
              :rows="4"
              placeholder="请输入评价标准"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 关联信息 -->
        <el-tab-pane v-if="mode !== 'create'" label="关联信息" name="relation">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="创建人">
              {{ formData.creator?.name || '-' }}
            </el-descriptions-item>
            <el-descriptions-item label="创建时间">
              {{ formatDateTime(formData.created_at) }}
            </el-descriptions-item>
            <el-descriptions-item label="更新人">
              {{ formData.updater?.name || '-' }}
            </el-descriptions-item>
            <el-descriptions-item label="更新时间">
              {{ formatDateTime(formData.updated_at) }}
            </el-descriptions-item>
          </el-descriptions>

          <div v-if="formData.experiment_catalogs && formData.experiment_catalogs.length > 0" style="margin-top: 20px">
            <h4>关联的实验目录</h4>
            <el-table :data="formData.experiment_catalogs" border>
              <el-table-column prop="name" label="实验名称" />
              <el-table-column prop="code" label="实验编码" width="120" />
              <el-table-column prop="experiment_type_name" label="实验类型" width="100" />
              <el-table-column prop="status_name" label="状态" width="80">
                <template #default="{ row }">
                  <el-tag :type="getStatusType(row.status)">
                    {{ row.status_name }}
                  </el-tag>
                </template>
              </el-table-column>
            </el-table>
          </div>
          <div v-else style="margin-top: 20px">
            <el-empty description="暂无关联的实验目录" />
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-form>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">取消</el-button>
        <el-button v-if="mode !== 'view'" type="primary" @click="handleSubmit" :loading="submitting">
          {{ mode === 'create' ? '创建' : '保存' }}
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { curriculumStandardApi } from '../../../api/experiment'
import { formatDateTime } from '../../../utils/date'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  formData: {
    type: Object,
    default: () => ({})
  },
  mode: {
    type: String,
    default: 'create' // create, edit, view
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const formRef = ref()
const submitting = ref(false)
const activeTab = ref('basic')
const options = ref({
  subjects: {},
  grades: {},
  statuses: {}
})

// 表单数据
const form = reactive({
  name: '',
  code: '',
  version: '',
  subject: '',
  grade: '',
  content: '',
  learning_objectives: '',
  key_concepts: '',
  skills_requirements: '',
  assessment_criteria: '',
  effective_date: null,
  expiry_date: null,
  status: 'draft'
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入课程标准名称', trigger: 'blur' },
    { max: 200, message: '标准名称不能超过200个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入标准编码', trigger: 'blur' },
    { max: 50, message: '标准编码不能超过50个字符', trigger: 'blur' }
  ],
  version: [
    { required: true, message: '请输入版本号', trigger: 'blur' },
    { max: 50, message: '版本号不能超过50个字符', trigger: 'blur' }
  ],
  subject: [
    { required: true, message: '请选择学科', trigger: 'change' }
  ],
  grade: [
    { required: true, message: '请选择年级', trigger: 'change' }
  ]
}

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const dialogTitle = computed(() => {
  const titleMap = {
    create: '新建课程标准',
    edit: '编辑课程标准',
    view: '查看课程标准'
  }
  return titleMap[props.mode] || '课程标准'
})

// 监听器
watch(() => props.formData, (newData) => {
  if (newData && Object.keys(newData).length > 0) {
    Object.keys(form).forEach(key => {
      if (newData[key] !== undefined) {
        form[key] = newData[key]
      }
    })
  }
}, { immediate: true, deep: true })

watch(() => props.visible, (visible) => {
  if (visible) {
    activeTab.value = 'basic'
  }
})

// 方法
const loadOptions = async () => {
  try {
    const response = await curriculumStandardApi.getOptions()
    if (response.data.success) {
      options.value = response.data.data
    }
  } catch (error) {
    console.error('获取选项数据失败：', error)
  }
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    
    submitting.value = true
    
    let response
    if (props.mode === 'create') {
      response = await curriculumStandardApi.create(form)
    } else {
      response = await curriculumStandardApi.update(props.formData.id, form)
    }

    if (response.data.success) {
      ElMessage.success(props.mode === 'create' ? '创建成功' : '保存成功')
      emit('success')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(field => {
        ElMessage.error(errors[field][0])
      })
    } else {
      ElMessage.error('操作失败：' + error.message)
    }
  } finally {
    submitting.value = false
  }
}

const handleClose = () => {
  // 重置表单
  Object.keys(form).forEach(key => {
    if (typeof form[key] === 'string') {
      form[key] = ''
    } else if (form[key] instanceof Date) {
      form[key] = null
    }
  })
  form.status = 'draft'
  
  formRef.value?.clearValidate()
  emit('update:visible', false)
}

const getStatusType = (status) => {
  const typeMap = {
    active: 'success',
    inactive: 'info',
    draft: 'warning',
    archived: 'info'
  }
  return typeMap[status] || 'info'
}

// 生命周期
onMounted(() => {
  loadOptions()
})
</script>

<style scoped>
.dialog-footer {
  text-align: right;
}

:deep(.el-tabs__content) {
  padding: 20px 0;
}
</style>
