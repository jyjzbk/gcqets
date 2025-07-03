<template>
  <el-dialog
    v-model="dialogVisible"
    :title="isEdit ? '编辑使用记录' : '添加使用记录'"
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
      <el-form-item label="材料" prop="material_id">
        <el-select
          v-model="form.material_id"
          placeholder="请选择材料"
          style="width: 100%"
          filterable
        >
          <el-option
            v-for="material in materials"
            :key="material.id"
            :label="`${material.name} (库存: ${material.current_stock} ${material.unit})`"
            :value="material.id"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="使用数量" prop="quantity_used">
        <el-input-number
          v-model="form.quantity_used"
          :min="1"
          placeholder="请输入使用数量"
          style="width: 100%"
        />
      </el-form-item>

      <el-form-item label="使用类型" prop="usage_type">
        <el-select
          v-model="form.usage_type"
          placeholder="请选择使用类型"
          style="width: 100%"
        >
          <el-option label="实验教学" value="experiment" />
          <el-option label="设备维护" value="maintenance" />
          <el-option label="课堂教学" value="teaching" />
          <el-option label="其他用途" value="other" />
        </el-select>
      </el-form-item>

      <el-form-item label="使用时间" prop="used_at">
        <el-date-picker
          v-model="form.used_at"
          type="datetime"
          placeholder="请选择使用时间"
          style="width: 100%"
          format="YYYY-MM-DD HH:mm:ss"
          value-format="YYYY-MM-DD HH:mm:ss"
        />
      </el-form-item>

      <el-form-item label="班级名称">
        <el-input v-model="form.class_name" placeholder="请输入班级名称" />
      </el-form-item>

      <el-form-item label="学生人数">
        <el-input-number
          v-model="form.student_count"
          :min="1"
          placeholder="请输入学生人数"
          style="width: 100%"
        />
      </el-form-item>

      <el-form-item label="使用目的" prop="purpose">
        <el-input
          v-model="form.purpose"
          type="textarea"
          :rows="3"
          placeholder="请输入使用目的"
        />
      </el-form-item>

      <el-form-item label="备注说明">
        <el-input
          v-model="form.notes"
          type="textarea"
          :rows="2"
          placeholder="请输入备注说明"
        />
      </el-form-item>
    </el-form>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        {{ isEdit ? '更新' : '创建' }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { materialUsageApi, materialApi } from '@/api/equipment'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  usageId: {
    type: [String, Number],
    default: null
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const submitting = ref(false)
const formRef = ref()
const materials = ref([])

// 表单数据
const form = reactive({
  material_id: '',
  quantity_used: null,
  usage_type: '',
  used_at: '',
  class_name: '',
  student_count: null,
  purpose: '',
  notes: ''
})

// 表单验证规则
const rules = {
  material_id: [
    { required: true, message: '请选择材料', trigger: 'change' }
  ],
  quantity_used: [
    { required: true, message: '请输入使用数量', trigger: 'blur' }
  ],
  usage_type: [
    { required: true, message: '请选择使用类型', trigger: 'change' }
  ],
  used_at: [
    { required: true, message: '请选择使用时间', trigger: 'change' }
  ],
  purpose: [
    { required: true, message: '请输入使用目的', trigger: 'blur' }
  ]
}

// 计算属性
const isEdit = computed(() => !!props.usageId)

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal) {
    loadMaterials()
    if (props.usageId) {
      loadUsageDetail()
    } else {
      resetForm()
    }
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

// 重置表单
const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (typeof form[key] === 'number') {
      form[key] = null
    } else {
      form[key] = ''
    }
  })
  formRef.value?.clearValidate()
}

// 加载材料列表
const loadMaterials = async () => {
  try {
    const response = await materialApi.getList({ per_page: 1000, status: 'active' })
    if (response.data.success) {
      materials.value = response.data.data.data
    }
  } catch (error) {
    console.error('加载材料列表失败:', error)
  }
}

// 加载使用记录详情
const loadUsageDetail = async () => {
  try {
    loading.value = true
    const response = await materialUsageApi.getDetail(props.usageId)
    if (response.data.success) {
      const usage = response.data.data
      Object.keys(form).forEach(key => {
        if (usage[key] !== undefined) {
          form[key] = usage[key]
        }
      })
    }
  } catch (error) {
    console.error('加载使用记录详情失败:', error)
    ElMessage.error('加载使用记录详情失败')
  } finally {
    loading.value = false
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    const valid = await formRef.value.validate()
    if (!valid) return

    submitting.value = true

    const formData = { ...form }
    // 清理空值
    Object.keys(formData).forEach(key => {
      if (formData[key] === '' || formData[key] === null) {
        delete formData[key]
      }
    })

    let response
    if (isEdit.value) {
      response = await materialUsageApi.update(props.usageId, formData)
    } else {
      response = await materialUsageApi.create(formData)
    }

    if (response.data.success) {
      ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
      emit('success')
    }
  } catch (error) {
    console.error('提交失败:', error)
    ElMessage.error('提交失败')
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
