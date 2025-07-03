<template>
  <el-dialog
    v-model="dialogVisible"
    :title="dialogTitle"
    width="900px"
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
              <el-form-item label="模板名称" prop="name">
                <el-input v-model="form.name" placeholder="请输入照片模板名称" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="模板编码" prop="code">
                <el-input v-model="form.code" placeholder="请输入模板编码" />
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
            <el-col :span="12">
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
            <el-col :span="12">
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

          <el-form-item label="模板描述">
            <el-input
              v-model="form.description"
              type="textarea"
              :rows="3"
              placeholder="请输入照片模板描述"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 必需照片 -->
        <el-tab-pane label="必需照片" name="required">
          <div class="photo-config-section">
            <div class="section-header">
              <h4>必需照片配置</h4>
              <el-button type="primary" size="small" @click="addRequiredPhoto">
                <el-icon><Plus /></el-icon>
                添加必需照片
              </el-button>
            </div>

            <div v-if="form.required_photos.length === 0" class="empty-state">
              <el-empty description="暂无必需照片配置" />
            </div>

            <div v-else class="photo-list">
              <el-card
                v-for="(photo, index) in form.required_photos"
                :key="index"
                class="photo-item"
                shadow="never"
              >
                <template #header>
                  <div class="photo-item-header">
                    <span>必需照片 {{ index + 1 }}</span>
                    <el-button type="danger" link @click="removeRequiredPhoto(index)">
                      <el-icon><Delete /></el-icon>
                      删除
                    </el-button>
                  </div>
                </template>

                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="照片名称" :prop="`required_photos.${index}.name`" :rules="photoNameRule">
                      <el-input v-model="photo.name" placeholder="请输入照片名称" />
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="拍摄时机" :prop="`required_photos.${index}.timing`" :rules="photoTimingRule">
                      <el-select v-model="photo.timing" placeholder="请选择拍摄时机" style="width: 100%">
                        <el-option
                          v-for="(label, value) in options.photo_timings"
                          :key="value"
                          :label="label"
                          :value="value"
                        />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>

                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="拍摄角度" :prop="`required_photos.${index}.angle`" :rules="photoAngleRule">
                      <el-select v-model="photo.angle" placeholder="请选择拍摄角度" style="width: 100%">
                        <el-option
                          v-for="(label, value) in options.photo_angles"
                          :key="value"
                          :label="label"
                          :value="value"
                        />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>

                <el-form-item label="照片描述" :prop="`required_photos.${index}.description`" :rules="photoDescRule">
                  <el-input
                    v-model="photo.description"
                    type="textarea"
                    :rows="2"
                    placeholder="请输入照片描述"
                  />
                </el-form-item>
              </el-card>
            </div>
          </div>
        </el-tab-pane>

        <!-- 可选照片 -->
        <el-tab-pane label="可选照片" name="optional">
          <div class="photo-config-section">
            <div class="section-header">
              <h4>可选照片配置</h4>
              <el-button type="primary" size="small" @click="addOptionalPhoto">
                <el-icon><Plus /></el-icon>
                添加可选照片
              </el-button>
            </div>

            <div v-if="form.optional_photos.length === 0" class="empty-state">
              <el-empty description="暂无可选照片配置" />
            </div>

            <div v-else class="photo-list">
              <el-card
                v-for="(photo, index) in form.optional_photos"
                :key="index"
                class="photo-item"
                shadow="never"
              >
                <template #header>
                  <div class="photo-item-header">
                    <span>可选照片 {{ index + 1 }}</span>
                    <el-button type="danger" link @click="removeOptionalPhoto(index)">
                      <el-icon><Delete /></el-icon>
                      删除
                    </el-button>
                  </div>
                </template>

                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="照片名称">
                      <el-input v-model="photo.name" placeholder="请输入照片名称" />
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="拍摄时机">
                      <el-select v-model="photo.timing" placeholder="请选择拍摄时机" style="width: 100%">
                        <el-option
                          v-for="(label, value) in options.photo_timings"
                          :key="value"
                          :label="label"
                          :value="value"
                        />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>

                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="拍摄角度">
                      <el-select v-model="photo.angle" placeholder="请选择拍摄角度" style="width: 100%">
                        <el-option
                          v-for="(label, value) in options.photo_angles"
                          :key="value"
                          :label="label"
                          :value="value"
                        />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>

                <el-form-item label="照片描述">
                  <el-input
                    v-model="photo.description"
                    type="textarea"
                    :rows="2"
                    placeholder="请输入照片描述"
                  />
                </el-form-item>
              </el-card>
            </div>
          </div>
        </el-tab-pane>

        <!-- 照片规格 -->
        <el-tab-pane label="照片规格" name="specifications">
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="分辨率">
                <el-input v-model="form.photo_specifications.resolution" placeholder="如：1920x1080" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="文件格式">
                <el-select v-model="form.photo_specifications.format" placeholder="请选择格式" style="width: 100%">
                  <el-option label="JPG" value="JPG" />
                  <el-option label="PNG" value="PNG" />
                  <el-option label="JPEG" value="JPEG" />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="图片质量">
                <el-select v-model="form.photo_specifications.quality" placeholder="请选择质量" style="width: 100%">
                  <el-option label="高质量" value="high" />
                  <el-option label="中等质量" value="medium" />
                  <el-option label="低质量" value="low" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="光照要求">
                <el-input v-model="form.photo_specifications.lighting" placeholder="如：自然光优先" />
              </el-form-item>
            </el-col>
          </el-row>

          <el-form-item label="其他要求">
            <el-input
              v-model="form.photo_specifications.other_requirements"
              type="textarea"
              :rows="3"
              placeholder="请输入其他照片拍摄要求"
            />
          </el-form-item>
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
import { Plus, Delete } from '@element-plus/icons-vue'
import { photoTemplateApi } from '../../../api/experiment'

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
  experiment_types: {},
  statuses: {},
  photo_timings: {},
  photo_angles: {}
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
  required_photos: [],
  optional_photos: [],
  photo_specifications: {
    resolution: '',
    format: '',
    quality: '',
    lighting: '',
    other_requirements: ''
  },
  status: 'draft'
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入照片模板名称', trigger: 'blur' },
    { max: 200, message: '模板名称不能超过200个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入模板编码', trigger: 'blur' },
    { max: 50, message: '模板编码不能超过50个字符', trigger: 'blur' }
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
  ]
}

// 照片验证规则
const photoNameRule = [
  { required: true, message: '请输入照片名称', trigger: 'blur' }
]

const photoTimingRule = [
  { required: true, message: '请选择拍摄时机', trigger: 'change' }
]

const photoAngleRule = [
  { required: true, message: '请选择拍摄角度', trigger: 'change' }
]

const photoDescRule = [
  { required: true, message: '请输入照片描述', trigger: 'blur' }
]

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const dialogTitle = computed(() => {
  const titleMap = {
    create: '新建照片模板',
    edit: '编辑照片模板',
    view: '查看照片模板'
  }
  return titleMap[props.mode] || '照片模板'
})

// 监听器
watch(() => props.formData, (newData) => {
  if (newData && Object.keys(newData).length > 0) {
    Object.keys(form).forEach(key => {
      if (newData[key] !== undefined) {
        if (key === 'required_photos' || key === 'optional_photos') {
          form[key] = Array.isArray(newData[key]) ? [...newData[key]] : []
        } else if (key === 'photo_specifications') {
          form[key] = { ...form[key], ...(newData[key] || {}) }
        } else {
          form[key] = newData[key]
        }
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
    const response = await photoTemplateApi.getOptions()
    if (response.data.success) {
      options.value = response.data.data
    }
  } catch (error) {
    console.error('获取选项数据失败：', error)
  }
}

const addRequiredPhoto = () => {
  form.required_photos.push({
    name: '',
    description: '',
    timing: '',
    angle: ''
  })
}

const removeRequiredPhoto = (index) => {
  form.required_photos.splice(index, 1)
}

const addOptionalPhoto = () => {
  form.optional_photos.push({
    name: '',
    description: '',
    timing: '',
    angle: ''
  })
}

const removeOptionalPhoto = (index) => {
  form.optional_photos.splice(index, 1)
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()

    // 验证必需照片
    if (form.required_photos.length === 0) {
      ElMessage.error('请至少添加一个必需照片')
      activeTab.value = 'required'
      return
    }

    submitting.value = true

    let response
    if (props.mode === 'create') {
      response = await photoTemplateApi.create(form)
    } else {
      response = await photoTemplateApi.update(props.formData.id, form)
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
    } else if (Array.isArray(form[key])) {
      form[key] = []
    } else if (typeof form[key] === 'object') {
      Object.keys(form[key]).forEach(subKey => {
        form[key][subKey] = ''
      })
    }
  })
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

.photo-config-section {
  margin-bottom: 20px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h4 {
  margin: 0;
  color: #303133;
}

.empty-state {
  text-align: center;
  padding: 40px 0;
}

.photo-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.photo-item {
  border: 1px solid #e4e7ed;
}

.photo-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.photo-item-header span {
  font-weight: 500;
  color: #303133;
}
</style>
