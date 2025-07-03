<template>
  <el-dialog
    v-model="dialogVisible"
    title="使用记录详情"
    width="600px"
    :before-close="handleClose"
  >
    <div v-loading="loading" class="usage-detail">
      <template v-if="usage">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="使用编号">{{ usage.usage_code }}</el-descriptions-item>
          <el-descriptions-item label="材料名称">{{ usage.material?.name }}</el-descriptions-item>
          <el-descriptions-item label="使用人">{{ usage.user?.real_name }}</el-descriptions-item>
          <el-descriptions-item label="使用数量">{{ usage.quantity_used }} {{ usage.material?.unit }}</el-descriptions-item>
          <el-descriptions-item label="使用类型">{{ getUsageTypeText(usage.usage_type) }}</el-descriptions-item>
          <el-descriptions-item label="使用时间">{{ formatDateTime(usage.used_at) }}</el-descriptions-item>
          <el-descriptions-item label="班级名称">{{ usage.class_name || '-' }}</el-descriptions-item>
          <el-descriptions-item label="学生人数">{{ usage.student_count || '-' }}</el-descriptions-item>
          <el-descriptions-item label="使用目的" :span="2">{{ usage.purpose }}</el-descriptions-item>
          <el-descriptions-item label="备注说明" :span="2">{{ usage.notes || '-' }}</el-descriptions-item>
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
import { materialUsageApi } from '@/api/equipment'

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
const emit = defineEmits(['update:visible', 'refresh'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const usage = ref(null)

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal && props.usageId) {
    loadUsageDetail()
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

// 加载使用记录详情
const loadUsageDetail = async () => {
  try {
    loading.value = true
    const response = await materialUsageApi.getDetail(props.usageId)
    if (response.data.success) {
      usage.value = response.data.data
    }
  } catch (error) {
    console.error('加载使用记录详情失败:', error)
    ElMessage.error('加载使用记录详情失败')
  } finally {
    loading.value = false
  }
}

// 获取使用类型文本
const getUsageTypeText = (type) => {
  const texts = {
    experiment: '实验教学',
    maintenance: '设备维护',
    teaching: '课堂教学',
    other: '其他用途'
  }
  return texts[type] || type
}

// 格式化日期时间
const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 关闭对话框
const handleClose = () => {
  dialogVisible.value = false
  usage.value = null
}
</script>

<style scoped>
.usage-detail {
  max-height: 400px;
  overflow-y: auto;
}
</style>
