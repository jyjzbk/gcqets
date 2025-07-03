<template>
  <el-dialog
    v-model="dialogVisible"
    title="借用记录详情"
    width="600px"
    :before-close="handleClose"
  >
    <div v-loading="loading" class="borrowing-detail">
      <template v-if="borrowing">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="借用编号">{{ borrowing.borrowing_code }}</el-descriptions-item>
          <el-descriptions-item label="设备名称">{{ borrowing.equipment?.name }}</el-descriptions-item>
          <el-descriptions-item label="借用人">{{ borrowing.borrower?.real_name }}</el-descriptions-item>
          <el-descriptions-item label="审批人">{{ borrowing.approver?.real_name || '-' }}</el-descriptions-item>
          <el-descriptions-item label="借用状态">
            <el-tag :type="getStatusType(borrowing.status)">
              {{ getStatusText(borrowing.status) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="计划开始时间">{{ formatDateTime(borrowing.planned_start_time) }}</el-descriptions-item>
          <el-descriptions-item label="计划结束时间">{{ formatDateTime(borrowing.planned_end_time) }}</el-descriptions-item>
          <el-descriptions-item label="实际开始时间">{{ formatDateTime(borrowing.actual_start_time) }}</el-descriptions-item>
          <el-descriptions-item label="实际结束时间">{{ formatDateTime(borrowing.actual_end_time) }}</el-descriptions-item>
          <el-descriptions-item label="借用目的" :span="2">{{ borrowing.purpose }}</el-descriptions-item>
          <el-descriptions-item label="审批备注" :span="2">{{ borrowing.approval_notes || '-' }}</el-descriptions-item>
          <el-descriptions-item label="借用备注" :span="2">{{ borrowing.borrowing_notes || '-' }}</el-descriptions-item>
          <el-descriptions-item label="归还备注" :span="2">{{ borrowing.return_notes || '-' }}</el-descriptions-item>
        </el-descriptions>
      </template>
    </div>

    <template #footer>
      <el-button @click="handleClose">关闭</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
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
const emit = defineEmits(['update:visible', 'refresh'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const borrowing = ref(null)

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal && props.borrowingId) {
    loadBorrowingDetail()
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

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

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    borrowed: 'primary',
    returned: 'success',
    overdue: 'danger',
    cancelled: 'info'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    pending: '待审批',
    approved: '已批准',
    rejected: '已拒绝',
    borrowed: '已借出',
    returned: '已归还',
    overdue: '已逾期',
    cancelled: '已取消'
  }
  return texts[status] || status
}

// 格式化日期时间
const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 关闭对话框
const handleClose = () => {
  dialogVisible.value = false
  borrowing.value = null
}
</script>

<style scoped>
.borrowing-detail {
  max-height: 400px;
  overflow-y: auto;
}
</style>
