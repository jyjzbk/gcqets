<template>
  <el-dialog
    v-model="dialogVisible"
    :title="isEdit ? '编辑实验记录' : '新建实验记录'"
    width="900px"
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
              <el-form-item label="实验计划" prop="experiment_plan_id">
                <el-select
                  v-model="formData.experiment_plan_id"
                  placeholder="选择实验计划"
                  filterable
                  style="width: 100%"
                  @change="handlePlanChange"
                >
                  <el-option
                    v-for="plan in planOptions"
                    :key="plan.id"
                    :label="`${plan.name} (${plan.class_name})`"
                    :value="plan.id"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="执行日期" prop="execution_date">
                <el-date-picker
                  v-model="formData.execution_date"
                  type="date"
                  placeholder="选择执行日期"
                  format="YYYY-MM-DD"
                  value-format="YYYY-MM-DD"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="开始时间" prop="start_time">
                <el-time-picker
                  v-model="formData.start_time"
                  placeholder="选择开始时间"
                  format="HH:mm"
                  value-format="HH:mm"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="结束时间" prop="end_time">
                <el-time-picker
                  v-model="formData.end_time"
                  placeholder="选择结束时间"
                  format="HH:mm"
                  value-format="HH:mm"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="实际学生数" prop="actual_student_count">
                <el-input-number
                  v-model="formData.actual_student_count"
                  :min="1"
                  :max="100"
                  placeholder="实际参与学生数"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
          </el-row>

          <el-form-item label="完成状态" prop="completion_status">
            <el-radio-group v-model="formData.completion_status">
              <el-radio
                v-for="option in completionStatusOptions"
                :key="option.value"
                :label="option.value"
              >
                {{ option.label }}
              </el-radio>
            </el-radio-group>
          </el-form-item>

          <el-form-item label="执行说明" prop="execution_notes">
            <el-input
              v-model="formData.execution_notes"
              type="textarea"
              :rows="3"
              placeholder="请描述实验执行的具体情况"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 问题与反思 -->
        <el-tab-pane label="问题与反思" name="reflection">
          <el-form-item label="遇到的问题" prop="problems_encountered">
            <el-input
              v-model="formData.problems_encountered"
              type="textarea"
              :rows="4"
              placeholder="请描述实验过程中遇到的问题"
            />
          </el-form-item>

          <el-form-item label="解决方案" prop="solutions_applied">
            <el-input
              v-model="formData.solutions_applied"
              type="textarea"
              :rows="4"
              placeholder="请描述采用的解决方案"
            />
          </el-form-item>

          <el-form-item label="教学反思" prop="teaching_reflection">
            <el-input
              v-model="formData.teaching_reflection"
              type="textarea"
              :rows="4"
              placeholder="请填写教学反思"
            />
          </el-form-item>

          <el-form-item label="学生反馈" prop="student_feedback">
            <el-input
              v-model="formData.student_feedback"
              type="textarea"
              :rows="4"
              placeholder="请记录学生的反馈意见"
            />
          </el-form-item>

          <el-form-item label="安全事件" prop="safety_incidents">
            <el-input
              v-model="formData.safety_incidents"
              type="textarea"
              :rows="3"
              placeholder="如有安全事件请详细记录"
            />
          </el-form-item>
        </el-tab-pane>

        <!-- 资源使用 -->
        <el-tab-pane label="资源使用" name="resources">
          <el-form-item label="使用的设备">
            <div class="resource-list">
              <div
                v-for="(equipment, index) in formData.equipment_used"
                :key="index"
                class="resource-item"
              >
                <el-input v-model="equipment.name" placeholder="设备名称" style="width: 200px" />
                <el-input-number v-model="equipment.quantity" :min="1" placeholder="数量" style="width: 100px; margin: 0 10px" />
                <el-input v-model="equipment.condition" placeholder="使用状况" style="width: 150px; margin-right: 10px" />
                <el-input v-model="equipment.notes" placeholder="备注" style="width: 150px" />
                <el-button type="danger" size="small" @click="removeEquipment(index)">删除</el-button>
              </div>
              <el-button type="primary" size="small" @click="addEquipment">添加设备</el-button>
            </div>
          </el-form-item>

          <el-form-item label="消耗的材料">
            <div class="resource-list">
              <div
                v-for="(material, index) in formData.materials_consumed"
                :key="index"
                class="resource-item"
              >
                <el-input v-model="material.name" placeholder="材料名称" style="width: 200px" />
                <el-input-number v-model="material.quantity" :min="1" placeholder="数量" style="width: 100px; margin: 0 10px" />
                <el-input v-model="material.unit" placeholder="单位" style="width: 80px; margin-right: 10px" />
                <el-input v-model="material.notes" placeholder="备注" style="width: 150px" />
                <el-button type="danger" size="small" @click="removeMaterial(index)">删除</el-button>
              </div>
              <el-button type="primary" size="small" @click="addMaterial">添加材料</el-button>
            </div>
          </el-form-item>
        </el-tab-pane>

        <!-- 实验数据 -->
        <el-tab-pane label="实验数据" name="data">
          <el-form-item label="实验数据记录">
            <div class="data-records">
              <div
                v-for="(record, index) in formData.data_records"
                :key="index"
                class="data-record-item"
              >
                <el-row :gutter="10">
                  <el-col :span="6">
                    <el-input v-model="record.parameter" placeholder="参数名称" />
                  </el-col>
                  <el-col :span="6">
                    <el-input v-model="record.value" placeholder="测量值" />
                  </el-col>
                  <el-col :span="4">
                    <el-input v-model="record.unit" placeholder="单位" />
                  </el-col>
                  <el-col :span="6">
                    <el-input v-model="record.notes" placeholder="备注" />
                  </el-col>
                  <el-col :span="2">
                    <el-button type="danger" size="small" @click="removeDataRecord(index)">删除</el-button>
                  </el-col>
                </el-row>
              </div>
              <el-button type="primary" size="small" @click="addDataRecord">添加数据记录</el-button>
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
import { experimentRecordApi, completionStatusOptions } from '@/api/experimentRecord'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  recordData: {
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
const planOptions = ref([])

// 表单数据
const formData = reactive({
  experiment_plan_id: null,
  execution_date: '',
  start_time: '',
  end_time: '',
  actual_student_count: null,
  completion_status: 'not_started',
  execution_notes: '',
  problems_encountered: '',
  solutions_applied: '',
  teaching_reflection: '',
  student_feedback: '',
  safety_incidents: '',
  equipment_used: [],
  materials_consumed: [],
  data_records: []
})

// 表单验证规则
const formRules = {
  experiment_plan_id: [
    { required: true, message: '请选择实验计划', trigger: 'change' }
  ],
  execution_date: [
    { required: true, message: '请选择执行日期', trigger: 'change' }
  ],
  completion_status: [
    { required: true, message: '请选择完成状态', trigger: 'change' }
  ],
  actual_student_count: [
    { type: 'number', min: 1, max: 100, message: '学生人数应在 1-100 之间', trigger: 'blur' }
  ]
}

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const isEdit = computed(() => !!props.recordData?.id)

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal) {
    loadPlanOptions()
    if (props.recordData) {
      Object.assign(formData, {
        ...props.recordData,
        equipment_used: props.recordData.equipment_used || [],
        materials_consumed: props.recordData.materials_consumed || [],
        data_records: props.recordData.data_records || []
      })
    } else {
      resetForm()
    }
  }
})

// 方法
const loadPlanOptions = async () => {
  try {
    const response = await experimentRecordApi.getPlanOptions()
    if (response.data.success) {
      planOptions.value = response.data.data
    }
  } catch (error) {
    console.error('获取实验计划选项失败：', error)
  }
}

const handlePlanChange = (planId) => {
  const plan = planOptions.value.find(p => p.id === planId)
  if (plan && plan.planned_date) {
    formData.execution_date = plan.planned_date
  }
}

const addEquipment = () => {
  formData.equipment_used.push({
    name: '',
    quantity: 1,
    condition: '',
    notes: ''
  })
}

const removeEquipment = (index) => {
  formData.equipment_used.splice(index, 1)
}

const addMaterial = () => {
  formData.materials_consumed.push({
    name: '',
    quantity: 1,
    unit: '',
    notes: ''
  })
}

const removeMaterial = (index) => {
  formData.materials_consumed.splice(index, 1)
}

const addDataRecord = () => {
  formData.data_records.push({
    parameter: '',
    value: '',
    unit: '',
    notes: ''
  })
}

const removeDataRecord = (index) => {
  formData.data_records.splice(index, 1)
}

const resetForm = () => {
  Object.assign(formData, {
    experiment_plan_id: null,
    execution_date: '',
    start_time: '',
    end_time: '',
    actual_student_count: null,
    completion_status: 'not_started',
    execution_notes: '',
    problems_encountered: '',
    solutions_applied: '',
    teaching_reflection: '',
    student_feedback: '',
    safety_incidents: '',
    equipment_used: [],
    materials_consumed: [],
    data_records: []
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
    
    // 过滤空的资源和数据记录
    submitData.equipment_used = submitData.equipment_used.filter(
      item => item.name && item.quantity
    )
    submitData.materials_consumed = submitData.materials_consumed.filter(
      item => item.name && item.quantity
    )
    submitData.data_records = submitData.data_records.filter(
      item => item.parameter && item.value
    )
    
    let response
    if (isEdit.value) {
      response = await experimentRecordApi.update(props.recordData.id, submitData)
    } else {
      response = await experimentRecordApi.create(submitData)
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
.resource-list,
.data-records {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 15px;
  background-color: #fafafa;
}

.resource-item,
.data-record-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.resource-item:last-of-type,
.data-record-item:last-of-type {
  margin-bottom: 15px;
}

.dialog-footer {
  text-align: right;
}
</style>
