<template>
  <el-dialog
    v-model="dialogVisible"
    :title="isEdit ? '编辑材料' : '添加材料'"
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
          <el-form-item label="材料名称" prop="name">
            <el-input v-model="form.name" placeholder="请输入材料名称" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="材料编码" prop="code">
            <el-input v-model="form.code" placeholder="请输入材料编码" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="材料分类" prop="category_id">
            <el-select
              v-model="form.category_id"
              placeholder="请选择材料分类"
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
          <el-form-item label="规格型号">
            <el-input v-model="form.specification" placeholder="请输入规格型号" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="品牌">
            <el-input v-model="form.brand" placeholder="请输入品牌" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="计量单位" prop="unit">
            <el-select
              v-model="form.unit"
              placeholder="请选择计量单位"
              style="width: 100%"
              filterable
              allow-create
            >
              <el-option label="个" value="piece" />
              <el-option label="套" value="set" />
              <el-option label="千克" value="kg" />
              <el-option label="克" value="g" />
              <el-option label="升" value="l" />
              <el-option label="毫升" value="ml" />
              <el-option label="盒" value="box" />
              <el-option label="瓶" value="bottle" />
              <el-option label="包" value="pack" />
              <el-option label="米" value="meter" />
              <el-option label="厘米" value="cm" />
              <el-option label="毫米" value="mm" />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="单价">
            <el-input-number
              v-model="form.unit_price"
              :min="0"
              :precision="2"
              placeholder="请输入单价"
              style="width: 100%"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="当前库存" prop="current_stock">
            <el-input-number
              v-model="form.current_stock"
              :min="0"
              placeholder="请输入当前库存"
              style="width: 100%"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="最低库存" prop="min_stock">
            <el-input-number
              v-model="form.min_stock"
              :min="0"
              placeholder="请输入最低库存"
              style="width: 100%"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="最高库存">
            <el-input-number
              v-model="form.max_stock"
              :min="0"
              placeholder="请输入最高库存"
              style="width: 100%"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="存储位置">
            <el-input v-model="form.storage_location" placeholder="请输入存储位置" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="有效期至">
            <el-date-picker
              v-model="form.expiry_date"
              type="date"
              placeholder="请选择有效期至"
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
        <el-col v-if="isEdit" :span="12">
          <el-form-item label="材料状态" prop="status">
            <el-select
              v-model="form.status"
              placeholder="请选择材料状态"
              style="width: 100%"
            >
              <el-option label="正常" value="active" />
              <el-option label="停用" value="inactive" />
              <el-option label="过期" value="expired" />
              <el-option label="缺货" value="out_of_stock" />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="材料描述">
            <el-input
              v-model="form.description"
              type="textarea"
              :rows="3"
              placeholder="请输入材料描述"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="存储条件">
            <el-input
              v-model="form.storage_conditions"
              type="textarea"
              :rows="2"
              placeholder="请输入存储条件"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="安全注意事项">
            <el-input
              v-model="form.safety_notes"
              type="textarea"
              :rows="2"
              placeholder="请输入安全注意事项"
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
import { materialApi } from '@/api/equipment'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  materialId: {
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
  specification: '',
  brand: '',
  description: '',
  category_id: '',
  unit: '',
  unit_price: null,
  current_stock: 0,
  min_stock: 0,
  max_stock: null,
  storage_location: '',
  storage_conditions: '',
  expiry_date: '',
  safety_notes: '',
  status: 'active',
  supplier: ''
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入材料名称', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入材料编码', trigger: 'blur' }
  ],
  category_id: [
    { required: true, message: '请选择材料分类', trigger: 'change' }
  ],
  unit: [
    { required: true, message: '请选择计量单位', trigger: 'change' }
  ],
  current_stock: [
    { required: true, message: '请输入当前库存', trigger: 'blur' }
  ],
  min_stock: [
    { required: true, message: '请输入最低库存', trigger: 'blur' }
  ],
  status: [
    { required: true, message: '请选择材料状态', trigger: 'change' }
  ]
}

// 计算属性
const isEdit = computed(() => !!props.materialId)

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal) {
    if (props.materialId) {
      loadMaterialDetail()
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
      form[key] = 'active'
    } else if (key === 'current_stock' || key === 'min_stock') {
      form[key] = 0
    } else if (typeof form[key] === 'number') {
      form[key] = null
    } else {
      form[key] = ''
    }
  })
  formRef.value?.clearValidate()
}

// 加载材料详情
const loadMaterialDetail = async () => {
  try {
    loading.value = true
    const response = await materialApi.getDetail(props.materialId)
    if (response.data.success) {
      const material = response.data.data
      Object.keys(form).forEach(key => {
        if (material[key] !== undefined) {
          form[key] = material[key]
        }
      })
    }
  } catch (error) {
    console.error('加载材料详情失败:', error)
    ElMessage.error('加载材料详情失败')
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
      response = await materialApi.update(props.materialId, formData)
    } else {
      response = await materialApi.create(formData)
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
