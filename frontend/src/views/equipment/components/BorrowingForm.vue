<template>
  <el-dialog
    v-model="dialogVisible"
    title="申请借用设备"
    width="600px"
    :before-close="handleClose"
  >
    <el-form
      ref="formRef"
      v-loading="loading"
      :model="form"
      :rules="rules"
      label-width="120px"
    >
      <el-form-item label="设备" prop="equipment_id">
        <el-select
          v-model="form.equipment_id"
          placeholder="请选择设备"
          style="width: 100%"
          filterable
        >
          <el-option
            v-for="equipment in availableEquipment"
            :key="equipment.id"
            :label="`${equipment.name} (${equipment.code})`"
            :value="equipment.id"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="计划开始时间" prop="planned_start_time">
        <el-date-picker
          v-model="form.planned_start_time"
          type="datetime"
          placeholder="请选择计划开始时间"
          style="width: 100%"
          format="YYYY-MM-DD HH:mm:ss"
          value-format="YYYY-MM-DD HH:mm:ss"
        />
      </el-form-item>

      <el-form-item label="计划结束时间" prop="planned_end_time">
        <el-date-picker
          v-model="form.planned_end_time"
          type="datetime"
          placeholder="请选择计划结束时间"
          style="width: 100%"
          format="YYYY-MM-DD HH:mm:ss"
          value-format="YYYY-MM-DD HH:mm:ss"
        />
      </el-form-item>

      <el-form-item label="借用目的" prop="purpose">
        <el-input
          v-model="form.purpose"
          type="textarea"
          :rows="4"
          placeholder="请输入借用目的"
        />
      </el-form-item>
    </el-form>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        提交申请
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { equipmentBorrowingApi, equipmentApi } from '@/api/equipment'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const submitting = ref(false)
const formRef = ref()
const availableEquipment = ref([])

// 表单数据
const form = reactive({
  equipment_id: '',
  planned_start_time: '',
  planned_end_time: '',
  purpose: ''
})

// 表单验证规则
const rules = {
  equipment_id: [
    { required: true, message: '请选择设备', trigger: 'change' }
  ],
  planned_start_time: [
    { required: true, message: '请选择计划开始时间', trigger: 'change' }
  ],
  planned_end_time: [
    { required: true, message: '请选择计划结束时间', trigger: 'change' }
  ],
  purpose: [
    { required: true, message: '请输入借用目的', trigger: 'blur' }
  ]
}

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal) {
    loadAvailableEquipment()
    resetForm()
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

// 重置表单
const resetForm = () => {
  Object.keys(form).forEach(key => {
    form[key] = ''
  })
  formRef.value?.clearValidate()
}

// 加载可用设备
const loadAvailableEquipment = async () => {
  try {
    const response = await equipmentApi.getList({ status: 'available', per_page: 1000 })
    if (response.data.success) {
      availableEquipment.value = response.data.data.data
    }
  } catch (error) {
    console.error('加载可用设备失败:', error)
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    const valid = await formRef.value.validate()
    if (!valid) return

    submitting.value = true

    const response = await equipmentBorrowingApi.create(form)
    if (response.data.success) {
      ElMessage.success('申请提交成功')
      emit('success')
    }
  } catch (error) {
    console.error('提交申请失败:', error)
    ElMessage.error('提交申请失败')
  } finally {
    submitting.value = false
  }
}

// 关闭对话框
const handleClose = () => {
  dialogVisible.value = false
  resetForm()
}
</script>

<style scoped>
.el-form {
  max-height: 400px;
  overflow-y: auto;
}
</style>
