<template>
  <el-dialog
    v-model="dialogVisible"
    :title="isEdit ? '编辑设备' : '添加设备'"
    width="800px"
    :before-close="handleClose"
  >
    <el-form
      ref="formRef"
      v-loading="loading"
      :model="form"
      :rules="rules"
      label-width="120px"
    >
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="设备名称" prop="name">
            <el-input v-model="form.name" placeholder="请输入设备名称" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="设备编码" prop="code">
            <el-input v-model="form.code" placeholder="请输入设备编码" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="设备分类" prop="category_id">
            <el-select
              v-model="form.category_id"
              placeholder="请选择设备分类"
              style="width: 100%"
            >
              <el-option
                v-for="category in categories"
                :key="category.id"
                :label="category.name"
                :value="category.id"
              />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="品牌">
            <el-input v-model="form.brand" placeholder="请输入品牌" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="型号">
            <el-input v-model="form.model" placeholder="请输入型号" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="序列号">
            <el-input v-model="form.serial_number" placeholder="请输入序列号" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="采购价格">
            <el-input-number
              v-model="form.purchase_price"
              :min="0"
              :precision="2"
              placeholder="请输入采购价格"
              style="width: 100%"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="采购日期">
            <el-date-picker
              v-model="form.purchase_date"
              type="date"
              placeholder="请选择采购日期"
              style="width: 100%"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="供应商">
            <el-input v-model="form.supplier" placeholder="请输入供应商" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="保修期至">
            <el-date-picker
              v-model="form.warranty_date"
              type="date"
              placeholder="请选择保修期至"
              style="width: 100%"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
            />
          </el-form-item>
        </el-col>
        <el-col v-if="isEdit" :span="12">
          <el-form-item label="设备状态" prop="status">
            <el-select
              v-model="form.status"
              placeholder="请选择设备状态"
              style="width: 100%"
            >
              <el-option label="可用" value="available" />
              <el-option label="已借出" value="borrowed" />
              <el-option label="维护中" value="maintenance" />
              <el-option label="损坏" value="damaged" />
              <el-option label="报废" value="scrapped" />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="存放位置">
            <el-input v-model="form.location" placeholder="请输入存放位置" />
          </el-form-item>
        </el-col>
        <el-col v-if="isEdit" :span="12">
          <el-form-item label="最后维护日期">
            <el-date-picker
              v-model="form.last_maintenance_date"
              type="date"
              placeholder="请选择最后维护日期"
              style="width: 100%"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
            />
          </el-form-item>
        </el-col>
        <el-col v-if="isEdit" :span="12">
          <el-form-item label="下次维护日期">
            <el-date-picker
              v-model="form.next_maintenance_date"
              type="date"
              placeholder="请选择下次维护日期"
              style="width: 100%"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="设备描述">
            <el-input
              v-model="form.description"
              type="textarea"
              :rows="3"
              placeholder="请输入设备描述"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="使用说明">
            <el-input
              v-model="form.usage_notes"
              type="textarea"
              :rows="3"
              placeholder="请输入使用说明"
            />
          </el-form-item>
        </el-col>
        <el-col v-if="isEdit" :span="24">
          <el-form-item label="维护记录">
            <el-input
              v-model="form.maintenance_notes"
              type="textarea"
              :rows="3"
              placeholder="请输入维护记录"
            />
          </el-form-item>
        </el-col>
      </el-row>
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
import { ref, reactive, watch, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { equipmentApi } from '@/api/equipment'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  equipmentId: {
    type: [String, Number],
    default: null
  },
  categories: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const submitting = ref(false)
const formRef = ref()

// 表单数据
const form = reactive({
  name: '',
  code: '',
  model: '',
  brand: '',
  description: '',
  category_id: '',
  serial_number: '',
  purchase_price: null,
  purchase_date: '',
  supplier: '',
  warranty_date: '',
  status: 'available',
  location: '',
  usage_notes: '',
  maintenance_notes: '',
  last_maintenance_date: '',
  next_maintenance_date: ''
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入设备名称', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入设备编码', trigger: 'blur' }
  ],
  category_id: [
    { required: true, message: '请选择设备分类', trigger: 'change' }
  ],
  status: [
    { required: true, message: '请选择设备状态', trigger: 'change' }
  ]
}

// 计算属性
const isEdit = computed(() => !!props.equipmentId)

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal) {
    if (props.equipmentId) {
      loadEquipmentDetail()
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
    if (key === 'status') {
      form[key] = 'available'
    } else if (typeof form[key] === 'number') {
      form[key] = null
    } else {
      form[key] = ''
    }
  })
  formRef.value?.clearValidate()
}

// 加载设备详情
const loadEquipmentDetail = async () => {
  try {
    loading.value = true
    const response = await equipmentApi.getDetail(props.equipmentId)
    if (response.data.success) {
      const equipment = response.data.data
      Object.keys(form).forEach(key => {
        if (equipment[key] !== undefined) {
          form[key] = equipment[key]
        }
      })
    }
  } catch (error) {
    console.error('加载设备详情失败:', error)
    ElMessage.error('加载设备详情失败')
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
      response = await equipmentApi.update(props.equipmentId, formData)
    } else {
      response = await equipmentApi.create(formData)
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
  max-height: 500px;
  overflow-y: auto;
}
</style>
