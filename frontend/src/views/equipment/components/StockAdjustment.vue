<template>
  <el-dialog
    v-model="dialogVisible"
    title="库存调整"
    width="500px"
    :before-close="handleClose"
  >
    <div v-loading="loading">
      <!-- 材料信息 -->
      <el-card v-if="material" class="material-info" shadow="never">
        <div class="info-row">
          <span class="label">材料名称：</span>
          <span class="value">{{ material.name }}</span>
        </div>
        <div class="info-row">
          <span class="label">材料编码：</span>
          <span class="value">{{ material.code }}</span>
        </div>
        <div class="info-row">
          <span class="label">当前库存：</span>
          <span class="value" :class="getStockClass(material)">
            {{ material.current_stock }} {{ material.unit }}
          </span>
        </div>
        <div class="info-row">
          <span class="label">最低库存：</span>
          <span class="value">{{ material.min_stock }} {{ material.unit }}</span>
        </div>
      </el-card>

      <!-- 调整表单 -->
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
        style="margin-top: 20px"
      >
        <el-form-item label="调整类型" prop="adjustment_type">
          <el-radio-group v-model="form.adjustment_type" @change="handleTypeChange">
            <el-radio value="increase">增加库存</el-radio>
            <el-radio value="decrease">减少库存</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="调整数量" prop="quantity">
          <el-input-number
            v-model="form.quantity"
            :min="1"
            :max="form.adjustment_type === 'decrease' ? material?.current_stock : undefined"
            placeholder="请输入调整数量"
            style="width: 100%"
          />
          <div class="quantity-hint">
            <span v-if="form.adjustment_type === 'increase'">
              调整后库存：{{ (material?.current_stock || 0) + (form.quantity || 0) }} {{ material?.unit }}
            </span>
            <span v-else-if="form.adjustment_type === 'decrease'">
              调整后库存：{{ Math.max(0, (material?.current_stock || 0) - (form.quantity || 0)) }} {{ material?.unit }}
            </span>
          </div>
        </el-form-item>

        <el-form-item label="调整原因" prop="reason">
          <el-select
            v-model="form.reason"
            placeholder="请选择调整原因"
            style="width: 100%"
            filterable
            allow-create
          >
            <el-option label="采购入库" value="采购入库" />
            <el-option label="盘点调整" value="盘点调整" />
            <el-option label="损耗调整" value="损耗调整" />
            <el-option label="过期处理" value="过期处理" />
            <el-option label="损坏处理" value="损坏处理" />
            <el-option label="退货处理" value="退货处理" />
            <el-option label="其他调整" value="其他调整" />
          </el-select>
        </el-form-item>

        <el-form-item label="备注说明">
          <el-input
            v-model="form.notes"
            type="textarea"
            :rows="3"
            placeholder="请输入备注说明（可选）"
          />
        </el-form-item>
      </el-form>
    </div>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        确认调整
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
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
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const submitting = ref(false)
const formRef = ref()
const material = ref(null)

// 表单数据
const form = reactive({
  adjustment_type: 'increase',
  quantity: null,
  reason: '',
  notes: ''
})

// 表单验证规则
const rules = computed(() => ({
  adjustment_type: [
    { required: true, message: '请选择调整类型', trigger: 'change' }
  ],
  quantity: [
    { required: true, message: '请输入调整数量', trigger: 'blur' },
    { 
      validator: (rule, value, callback) => {
        if (!value || value <= 0) {
          callback(new Error('调整数量必须大于0'))
        } else if (form.adjustment_type === 'decrease' && value > (material.value?.current_stock || 0)) {
          callback(new Error('减少数量不能超过当前库存'))
        } else {
          callback()
        }
      }, 
      trigger: 'blur' 
    }
  ],
  reason: [
    { required: true, message: '请选择调整原因', trigger: 'change' }
  ]
}))

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal && props.materialId) {
    loadMaterialDetail()
    resetForm()
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

// 重置表单
const resetForm = () => {
  form.adjustment_type = 'increase'
  form.quantity = null
  form.reason = ''
  form.notes = ''
  formRef.value?.clearValidate()
}

// 加载材料详情
const loadMaterialDetail = async () => {
  try {
    loading.value = true
    const response = await materialApi.getDetail(props.materialId)
    if (response.data.success) {
      material.value = response.data.data
    }
  } catch (error) {
    console.error('加载材料详情失败:', error)
    ElMessage.error('加载材料详情失败')
  } finally {
    loading.value = false
  }
}

// 获取库存样式
const getStockClass = (material) => {
  if (material.current_stock <= 0) return 'stock-danger'
  if (material.current_stock <= material.min_stock) return 'stock-warning'
  return 'stock-normal'
}

// 调整类型改变
const handleTypeChange = () => {
  form.quantity = null
  formRef.value?.clearValidate('quantity')
}

// 提交表单
const handleSubmit = async () => {
  try {
    const valid = await formRef.value.validate()
    if (!valid) return

    // 计算实际调整数量（增加为正数，减少为负数）
    const actualQuantity = form.adjustment_type === 'increase' ? form.quantity : -form.quantity
    const newStock = (material.value?.current_stock || 0) + actualQuantity

    // 确认对话框
    const confirmMessage = `
      确认要进行库存调整吗？
      
      材料名称：${material.value?.name}
      调整类型：${form.adjustment_type === 'increase' ? '增加库存' : '减少库存'}
      调整数量：${form.quantity} ${material.value?.unit}
      调整原因：${form.reason}
      
      当前库存：${material.value?.current_stock} ${material.value?.unit}
      调整后库存：${newStock} ${material.value?.unit}
    `

    await ElMessageBox.confirm(confirmMessage, '确认库存调整', {
      confirmButtonText: '确认调整',
      cancelButtonText: '取消',
      type: 'warning'
    })

    submitting.value = true

    const response = await materialApi.adjustStock(props.materialId, {
      quantity: actualQuantity,
      reason: form.reason,
      notes: form.notes
    })

    if (response.data.success) {
      ElMessage.success('库存调整成功')
      emit('success')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('库存调整失败:', error)
      ElMessage.error('库存调整失败')
    }
  } finally {
    submitting.value = false
  }
}

// 关闭对话框
const handleClose = () => {
  dialogVisible.value = false
  resetForm()
  material.value = null
}
</script>

<style scoped>
.material-info {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
}

.info-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.info-row:last-child {
  margin-bottom: 0;
}

.label {
  font-weight: 500;
  color: #606266;
  min-width: 80px;
}

.value {
  color: #303133;
}

.stock-normal { color: #67C23A; font-weight: bold; }
.stock-warning { color: #E6A23C; font-weight: bold; }
.stock-danger { color: #F56C6C; font-weight: bold; }

.quantity-hint {
  margin-top: 8px;
  font-size: 12px;
  color: #909399;
}
</style>
