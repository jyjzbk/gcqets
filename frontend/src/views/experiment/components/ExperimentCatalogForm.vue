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
              <el-form-item label="实验名称" prop="name">
                <el-input v-model="form.name" placeholder="请输入实验名称" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="实验编码" prop="code">
                <el-input v-model="form.code" placeholder="请输入实验编码" />
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
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
            <el-col :span="8">
              <el-form-item label="教材版本" prop="textbook_version">
                <el-input v-model="form.textbook_version" placeholder="请输入教材版本" />
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="实验类型" prop="experiment_type">
                <el-select v-model="form.experiment_type" placeholder="请选择实验类型" style="width: 100%">
                  <el-option
                    v-for="(label, value) in options.experiment_types"
                    :key="value"
                    :label="label"
                    :value="value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="难度等级" prop="difficulty_level">
                <el-select v-model="form.difficulty_level" placeholder="请选择难度等级" style="width: 100%">
                  <el-option
                    v-for="(label, value) in options.difficulty_levels"
                    :key="value"
                    :label="label"
                    :value="value"
                  />
                </el-select>
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

          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="实验时长" prop="duration_minutes">
                <el-input-number
                  v-model="form.duration_minutes"
                  :min="1"
                  :max="300"
                  placeholder="分钟"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="学生数量" prop="student_count">
                <el-input-number
                  v-model="form.student_count"
                  :min="1"
                  :max="100"
                  placeholder="人"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="课程标准">
                <el-select
                  v-model="form.curriculum_standard_id"
                  placeholder="请选择课程标准"
                  style="width: 100%"
                  clearable
                  filterable
                >
                  <el-option
                    v-for="standard in curriculumStandards"
                    :key="standard.id"
                    :label="`${standard.name} (${standard.version})`"
                    :value="standard.id"
                  />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-form-item label="实验描述">
            <el-input
              v-model="form.description"
              type="textarea"
              :rows="3"
              placeholder="请输入实验描述"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 详细内容 -->
        <el-tab-pane label="详细内容" name="content">
          <el-form-item label="实验目标">
            <el-input
              v-model="form.objectives"
              type="textarea"
              :rows="4"
              placeholder="请输入实验目标"
            />
          </el-form-item>

          <el-form-item label="实验材料">
            <el-input
              v-model="form.materials"
              type="textarea"
              :rows="4"
              placeholder="请输入实验材料清单"
            />
          </el-form-item>

          <el-form-item label="实验步骤">
            <el-input
              v-model="form.procedures"
              type="textarea"
              :rows="6"
              placeholder="请输入实验步骤"
            />
          </el-form-item>

          <el-form-item label="安全注意事项">
            <el-input
              v-model="form.safety_notes"
              type="textarea"
              :rows="4"
              placeholder="请输入安全注意事项"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 版本信息 -->
        <el-tab-pane v-if="mode !== 'create'" label="版本信息" name="version">
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

          <div v-if="mode === 'edit'" style="margin-top: 20px">
            <el-form-item label="变更原因">
              <el-input
                v-model="changeReason"
                type="textarea"
                :rows="2"
                placeholder="请输入本次变更的原因（可选）"
              />
            </el-form-item>
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
import { experimentCatalogApi, curriculumStandardApi } from '../../../api/experiment'
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
const changeReason = ref('')
const curriculumStandards = ref([])
const options = ref({
  subjects: {},
  grades: {},
  experiment_types: {},
  difficulty_levels: {},
  statuses: {}
})

// 表单数据
const form = reactive({
  name: '',
  code: '',
  subject: '',
  grade: '',
  textbook_version: '',
  experiment_type: '',
  description: '',
  objectives: '',
  materials: '',
  procedures: '',
  safety_notes: '',
  duration_minutes: null,
  student_count: null,
  difficulty_level: 'medium',
  status: 'draft',
  curriculum_standard_id: null
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入实验名称', trigger: 'blur' },
    { max: 200, message: '实验名称不能超过200个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入实验编码', trigger: 'blur' },
    { max: 50, message: '实验编码不能超过50个字符', trigger: 'blur' }
  ],
  subject: [
    { required: true, message: '请选择学科', trigger: 'change' }
  ],
  grade: [
    { required: true, message: '请选择年级', trigger: 'change' }
  ],
  textbook_version: [
    { required: true, message: '请输入教材版本', trigger: 'blur' },
    { max: 100, message: '教材版本不能超过100个字符', trigger: 'blur' }
  ],
  experiment_type: [
    { required: true, message: '请选择实验类型', trigger: 'change' }
  ],
  difficulty_level: [
    { required: true, message: '请选择难度等级', trigger: 'change' }
  ]
}

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const dialogTitle = computed(() => {
  const titleMap = {
    create: '新建实验目录',
    edit: '编辑实验目录',
    view: '查看实验目录'
  }
  return titleMap[props.mode] || '实验目录'
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
    changeReason.value = ''
    loadCurriculumStandards()
  }
})

// 方法
const loadOptions = async () => {
  try {
    const response = await experimentCatalogApi.getOptions()
    if (response.data.success) {
      options.value = response.data.data
    }
  } catch (error) {
    console.error('获取选项数据失败：', error)
  }
}

const loadCurriculumStandards = async () => {
  try {
    const params = {}
    if (form.subject) params.subject = form.subject
    if (form.grade) params.grade = form.grade
    
    const response = await curriculumStandardApi.getValidStandards(params)
    if (response.data.success) {
      curriculumStandards.value = response.data.data
    }
  } catch (error) {
    console.error('获取课程标准失败：', error)
  }
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    
    submitting.value = true
    
    const submitData = { ...form }
    if (props.mode === 'edit' && changeReason.value) {
      submitData.change_reason = changeReason.value
    }

    let response
    if (props.mode === 'create') {
      response = await experimentCatalogApi.create(submitData)
    } else {
      response = await experimentCatalogApi.update(props.formData.id, submitData)
    }

    if (response.data.success) {
      ElMessage.success(props.mode === 'create' ? '创建成功' : '保存成功')
      emit('success')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      // 处理验证错误
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
    } else if (typeof form[key] === 'number') {
      form[key] = null
    }
  })
  form.difficulty_level = 'medium'
  form.status = 'draft'
  
  formRef.value?.clearValidate()
  emit('update:visible', false)
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
