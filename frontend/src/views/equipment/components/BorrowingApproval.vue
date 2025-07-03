<template>
  <el-dialog
    v-model="dialogVisible"
    title="审批借用申请"
    width="500px"
    :before-close="handleClose"
  >
    <div v-loading="loading">
      <!-- 借用信息 -->
      <el-card v-if="borrowing" class="borrowing-info" shadow="never">
        <div class="info-row">
          <span class="label">借用编号：</span>
          <span class="value">{{ borrowing.borrowing_code }}</span>
        </div>
        <div class="info-row">
          <span class="label">设备名称：</span>
          <span class="value">{{ borrowing.equipment?.name }}</span>
        </div>
        <div class="info-row">
          <span class="label">借用人：</span>
          <span class="value">{{ borrowing.borrower?.real_name }}</span>
        </div>
        <div class="info-row">
          <span class="label">借用目的：</span>
          <span class="value">{{ borrowing.purpose }}</span>
        </div>
      </el-card>

      <!-- 审批表单 -->
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
        style="margin-top: 20px"
      >
        <el-form-item label="审批结果" prop="action">
          <el-radio-group v-model="form.action">
            <el-radio value="approve">批准</el-radio>
            <el-radio value="reject">拒绝</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="审批意见">
          <el-input
            v-model="form.notes"
            type="textarea"
            :rows="3"
            placeholder="请输入审批意见（可选）"
          />
        </el-form-item>
      </el-form>
    </div>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="submitting" @click="handleSubmit">
        确认审批
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { equipmentBorrowingApi } from '@/api/equipment'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  borrowingId: {
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
const borrowing = ref(null)

// 表单数据
const form = reactive({
  action: 'approve',
  notes: ''
})

// 表单验证规则
const rules = {
  action: [
    { required: true, message: '请选择审批结果', trigger: 'change' }
  ]
}

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal && props.borrowingId) {
    loadBorrowingDetail()
    resetForm()
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

// 重置表单
const resetForm = () => {
  form.action = 'approve'
  form.notes = ''
  formRef.value?.clearValidate()
}

// 加载借用记录详情
const loadBorrowingDetail = async () => {
  try {
    loading.value = true
    const response = await equipmentBorrowingApi.getDetail(props.borrowingId)
    if (response.data.success) {
      borrowing.value = response.data.data
    }
  } catch (error) {
    console.error('加载借用记录详情失败:', error)
    ElMessage.error('加载借用记录详情失败')
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

    const response = await equipmentBorrowingApi.approve(props.borrowingId, form)
    if (response.data.success) {
      ElMessage.success('审批成功')
      emit('success')
    }
  } catch (error) {
    console.error('审批失败:', error)
    ElMessage.error('审批失败')
  } finally {
    submitting.value = false
  }
}

// 关闭对话框
const handleClose = () => {
  dialogVisible.value = false
  resetForm()
  borrowing.value = null
}
</script>

<style scoped>
.borrowing-info {
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
</style>
