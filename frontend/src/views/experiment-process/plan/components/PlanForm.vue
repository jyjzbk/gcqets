<template>
  <el-dialog
    v-model="dialogVisible"
    :title="isEdit ? '编辑实验计划' : '新建实验计划'"
    width="800px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form
      ref="formRef"
      :model="formData"
      :rules="formRules"
      label-width="120px"
      @submit.prevent
    >
      <el-tabs v-model="activeTab">
        <!-- 基本信息 -->
        <el-tab-pane label="基本信息" name="basic">
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="计划名称" prop="name">
                <el-input v-model="formData.name" placeholder="请输入计划名称" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="班级名称" prop="class_name">
                <el-input v-model="formData.class_name" placeholder="请输入班级名称" />
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="学生人数" prop="student_count">
                <el-input-number
                  v-model="formData.student_count"
                  :min="1"
                  :max="100"
                  placeholder="学生人数"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="计划日期" prop="planned_date">
                <el-date-picker
                  v-model="formData.planned_date"
                  type="date"
                  placeholder="选择计划执行日期"
                  format="YYYY-MM-DD"
                  value-format="YYYY-MM-DD"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="计划时长" prop="planned_duration">
                <el-input-number
                  v-model="formData.planned_duration"
                  :min="10"
                  :max="300"
                  placeholder="分钟"
                  style="width: 100%"
                />
                <span class="form-tip">单位：分钟</span>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="优先级" prop="priority">
                <el-select v-model="formData.priority" placeholder="选择优先级" style="width: 100%">
                  <el-option
                    v-for="option in priorityOptions"
                    :key="option.value"
                    :label="option.label"
                    :value="option.value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-form-item label="实验目录" prop="experiment_catalog_id">
            <el-select
              v-model="formData.experiment_catalog_id"
              placeholder="选择实验目录"
              filterable
              style="width: 100%"
              @change="handleCatalogChange"
            >
              <el-option
                v-for="catalog in catalogOptions"
                :key="catalog.id"
                :label="`${catalog.name} (${getSubjectLabel(catalog.subject)} - ${getGradeLabel(catalog.grade)})`"
                :value="catalog.id"
              />
            </el-select>
          </el-form-item>

          <el-form-item label="课程标准" prop="curriculum_standard_id">
            <el-select
              v-model="formData.curriculum_standard_id"
              placeholder="选择课程标准（可选）"
              filterable
              clearable
              style="width: 100%"
            >
              <el-option
                v-for="standard in standardOptions"
                :key="standard.id"
                :label="`${standard.name} (${standard.version})`"
                :value="standard.id"
              />
            </el-select>
          </el-form-item>

          <el-form-item label="计划描述" prop="description">
            <el-input
              v-model="formData.description"
              type="textarea"
              :rows="3"
              placeholder="请输入计划描述"
            />
          </el-form-item>

          <el-form-item>
            <el-checkbox v-model="formData.is_public">设为公开计划</el-checkbox>
          </el-form-item>
        </el-tab-pane>

        <!-- 教学设计 -->
        <el-tab-pane label="教学设计" name="teaching">
          <el-form-item label="教学目标" prop="objectives">
            <el-input
              v-model="formData.objectives"
              type="textarea"
              :rows="4"
              placeholder="请输入教学目标"
            />
          </el-form-item>

          <el-form-item label="重点难点" prop="key_points">
            <el-input
              v-model="formData.key_points"
              type="textarea"
              :rows="4"
              placeholder="请输入重点难点"
            />
          </el-form-item>

          <el-form-item label="安全要求" prop="safety_requirements">
            <el-input
              v-model="formData.safety_requirements"
              type="textarea"
              :rows="4"
              placeholder="请输入安全要求和注意事项"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 资源需求 -->
        <el-tab-pane label="资源需求" name="resources">
          <el-form-item label="设备需求">
            <div class="resource-list">
              <div
                v-for="(equipment, index) in formData.equipment_requirements"
                :key="index"
                class="resource-item"
              >
                <el-input v-model="equipment.name" placeholder="设备名称" style="width: 200px" />
                <el-input-number v-model="equipment.quantity" :min="1" placeholder="数量" style="width: 100px; margin: 0 10px" />
                <el-input v-model="equipment.specification" placeholder="规格要求" style="width: 200px" />
                <el-button type="danger" size="small" @click="removeEquipment(index)">删除</el-button>
              </div>
              <el-button type="primary" size="small" @click="addEquipment">添加设备</el-button>
            </div>
          </el-form-item>

          <el-form-item label="材料需求">
            <div class="resource-list">
              <div
                v-for="(material, index) in formData.material_requirements"
                :key="index"
                class="resource-item"
              >
                <el-input v-model="material.name" placeholder="材料名称" style="width: 200px" />
                <el-input-number v-model="material.quantity" :min="1" placeholder="数量" style="width: 100px; margin: 0 10px" />
                <el-input v-model="material.unit" placeholder="单位" style="width: 80px; margin-right: 10px" />
                <el-input v-model="material.specification" placeholder="规格要求" style="width: 150px" />
                <el-button type="danger" size="small" @click="removeMaterial(index)">删除</el-button>
              </div>
              <el-button type="primary" size="small" @click="addMaterial">添加材料</el-button>
            </div>
          </el-form-item>
        </el-tab-pane>
      </el-tabs>
    </el-form>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="handleSubmit">
          {{ isEdit ? '更新' : '创建' }}
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import { experimentPlanApi, priorityOptions, getSubjectLabel, getGradeLabel } from '@/api/experimentPlan'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  planData: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const formRef = ref()
const submitting = ref(false)
const activeTab = ref('basic')
const catalogOptions = ref([])
const standardOptions = ref([])

// 表单数据
const formData = reactive({
  name: '',
  class_name: '',
  student_count: null,
  planned_date: '',
  planned_duration: null,
  priority: 'medium',
  experiment_catalog_id: null,
  curriculum_standard_id: null,
  description: '',
  objectives: '',
  key_points: '',
  safety_requirements: '',
  equipment_requirements: [],
  material_requirements: [],
  is_public: false
})

// 表单验证规则
const formRules = {
  name: [
    { required: true, message: '请输入计划名称', trigger: 'blur' },
    { min: 2, max: 200, message: '长度在 2 到 200 个字符', trigger: 'blur' }
  ],
  experiment_catalog_id: [
    { required: true, message: '请选择实验目录', trigger: 'change' }
  ],
  class_name: [
    { max: 100, message: '长度不能超过 100 个字符', trigger: 'blur' }
  ],
  student_count: [
    { type: 'number', min: 1, max: 100, message: '学生人数应在 1-100 之间', trigger: 'blur' }
  ],
  planned_duration: [
    { type: 'number', min: 10, max: 300, message: '计划时长应在 10-300 分钟之间', trigger: 'blur' }
  ]
}

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const isEdit = computed(() => !!props.planData?.id)

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal) {
    loadCatalogOptions()
    loadStandardOptions()
    if (props.planData) {
      Object.assign(formData, {
        ...props.planData,
        equipment_requirements: props.planData.equipment_requirements || [],
        material_requirements: props.planData.material_requirements || []
      })
    } else {
      resetForm()
    }
  }
})

// 方法
const loadCatalogOptions = async () => {
  try {
    const response = await experimentPlanApi.getCatalogOptions()
    if (response.data.success) {
      catalogOptions.value = response.data.data
    }
  } catch (error) {
    console.error('获取实验目录选项失败：', error)
  }
}

const loadStandardOptions = async (subject = null, grade = null) => {
  try {
    const params = {}
    if (subject) params.subject = subject
    if (grade) params.grade = grade
    
    const response = await experimentPlanApi.getStandardOptions(params)
    if (response.data.success) {
      standardOptions.value = response.data.data
    }
  } catch (error) {
    console.error('获取课程标准选项失败：', error)
  }
}

const handleCatalogChange = (catalogId) => {
  const catalog = catalogOptions.value.find(c => c.id === catalogId)
  if (catalog) {
    // 根据选择的实验目录过滤课程标准
    loadStandardOptions(catalog.subject, catalog.grade)
    // 清空之前选择的课程标准
    formData.curriculum_standard_id = null
  }
}

const addEquipment = () => {
  formData.equipment_requirements.push({
    name: '',
    quantity: 1,
    specification: ''
  })
}

const removeEquipment = (index) => {
  formData.equipment_requirements.splice(index, 1)
}

const addMaterial = () => {
  formData.material_requirements.push({
    name: '',
    quantity: 1,
    unit: '',
    specification: ''
  })
}

const removeMaterial = (index) => {
  formData.material_requirements.splice(index, 1)
}

const resetForm = () => {
  Object.assign(formData, {
    name: '',
    class_name: '',
    student_count: null,
    planned_date: '',
    planned_duration: null,
    priority: 'medium',
    experiment_catalog_id: null,
    curriculum_standard_id: null,
    description: '',
    objectives: '',
    key_points: '',
    safety_requirements: '',
    equipment_requirements: [],
    material_requirements: [],
    is_public: false
  })
  activeTab.value = 'basic'
  nextTick(() => {
    formRef.value?.clearValidate()
  })
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    
    submitting.value = true
    
    const submitData = { ...formData }
    
    // 过滤空的设备和材料需求
    submitData.equipment_requirements = submitData.equipment_requirements.filter(
      item => item.name && item.quantity
    )
    submitData.material_requirements = submitData.material_requirements.filter(
      item => item.name && item.quantity
    )
    
    let response
    if (isEdit.value) {
      response = await experimentPlanApi.update(props.planData.id, submitData)
    } else {
      response = await experimentPlanApi.create(submitData)
    }
    
    if (response.data.success) {
      ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
      emit('success')
    }
  } catch (error) {
    if (error.errors) {
      // 表单验证错误
      return
    }
    ElMessage.error((isEdit.value ? '更新' : '创建') + '失败：' + error.message)
  } finally {
    submitting.value = false
  }
}

const handleClose = () => {
  emit('update:visible', false)
}
</script>

<style scoped>
.form-tip {
  font-size: 12px;
  color: #909399;
  margin-left: 8px;
}

.resource-list {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 15px;
  background-color: #fafafa;
}

.resource-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.resource-item:last-of-type {
  margin-bottom: 15px;
}

.dialog-footer {
  text-align: right;
}
</style>
