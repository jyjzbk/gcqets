<template>
  <el-dialog
    v-model="dialogVisible"
    title="设备详情"
    width="800px"
    :before-close="handleClose"
  >
    <div v-loading="loading" class="equipment-detail">
      <template v-if="equipment">
        <!-- 基本信息 -->
        <el-card class="info-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span>基本信息</span>
              <el-tag :type="getStatusType(equipment.status)">
                {{ getStatusText(equipment.status) }}
              </el-tag>
            </div>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="info-item">
                <label>设备名称：</label>
                <span>{{ equipment.name }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>设备编码：</label>
                <span>{{ equipment.code }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>设备分类：</label>
                <span>{{ equipment.category?.name }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>品牌：</label>
                <span>{{ equipment.brand || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>型号：</label>
                <span>{{ equipment.model || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>序列号：</label>
                <span>{{ equipment.serial_number || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>存放位置：</label>
                <span>{{ equipment.location || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>采购价格：</label>
                <span>{{ equipment.purchase_price ? `¥${equipment.purchase_price}` : '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>采购日期：</label>
                <span>{{ equipment.purchase_date || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>供应商：</label>
                <span>{{ equipment.supplier || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>保修期至：</label>
                <span>{{ equipment.warranty_date || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>创建时间：</label>
                <span>{{ formatDateTime(equipment.created_at) }}</span>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="info-item">
                <label>设备描述：</label>
                <span>{{ equipment.description || '-' }}</span>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 使用统计 -->
        <el-card class="info-card" shadow="never">
          <template #header>
            <span>使用统计</span>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="8">
              <div class="stat-item">
                <div class="stat-number">{{ equipment.total_usage_count || 0 }}</div>
                <div class="stat-label">总使用次数</div>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="stat-item">
                <div class="stat-number">{{ equipment.total_usage_hours || 0 }}</div>
                <div class="stat-label">总使用时长(小时)</div>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="stat-item">
                <div class="stat-number">{{ equipment.borrowings?.length || 0 }}</div>
                <div class="stat-label">借用记录数</div>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 维护信息 -->
        <el-card class="info-card" shadow="never">
          <template #header>
            <span>维护信息</span>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="info-item">
                <label>最后维护日期：</label>
                <span>{{ equipment.last_maintenance_date || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>下次维护日期：</label>
                <span>{{ equipment.next_maintenance_date || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="info-item">
                <label>使用说明：</label>
                <span>{{ equipment.usage_notes || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="info-item">
                <label>维护记录：</label>
                <span>{{ equipment.maintenance_notes || '-' }}</span>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 当前借用信息 -->
        <el-card v-if="equipment.current_borrowing" class="info-card" shadow="never">
          <template #header>
            <span>当前借用信息</span>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="info-item">
                <label>借用人：</label>
                <span>{{ equipment.current_borrowing.borrower?.real_name || equipment.current_borrowing.borrower?.name }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>借用状态：</label>
                <el-tag :type="getBorrowingStatusType(equipment.current_borrowing.status)">
                  {{ getBorrowingStatusText(equipment.current_borrowing.status) }}
                </el-tag>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>计划开始时间：</label>
                <span>{{ formatDateTime(equipment.current_borrowing.planned_start_time) }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>计划结束时间：</label>
                <span>{{ formatDateTime(equipment.current_borrowing.planned_end_time) }}</span>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="info-item">
                <label>借用目的：</label>
                <span>{{ equipment.current_borrowing.purpose }}</span>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 借用历史 -->
        <el-card v-if="equipment.borrowings && equipment.borrowings.length > 0" class="info-card" shadow="never">
          <template #header>
            <span>借用历史</span>
          </template>
          
          <el-table :data="equipment.borrowings.slice(0, 5)" size="small">
            <el-table-column prop="borrower.real_name" label="借用人" width="100" />
            <el-table-column label="状态" width="80">
              <template #default="{ row }">
                <el-tag :type="getBorrowingStatusType(row.status)" size="small">
                  {{ getBorrowingStatusText(row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="planned_start_time" label="计划开始时间" width="150">
              <template #default="{ row }">
                {{ formatDateTime(row.planned_start_time) }}
              </template>
            </el-table-column>
            <el-table-column prop="planned_end_time" label="计划结束时间" width="150">
              <template #default="{ row }">
                {{ formatDateTime(row.planned_end_time) }}
              </template>
            </el-table-column>
            <el-table-column prop="purpose" label="借用目的" min-width="150" />
          </el-table>
          
          <div v-if="equipment.borrowings.length > 5" class="more-records">
            <el-text type="info">仅显示最近5条记录</el-text>
          </div>
        </el-card>
      </template>
    </div>

    <template #footer>
      <el-button @click="handleClose">关闭</el-button>
      <el-button type="primary" @click="handleEdit">编辑设备</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
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
  }
})

// Emits
const emit = defineEmits(['update:visible', 'edit', 'refresh'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const equipment = ref(null)

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal && props.equipmentId) {
    loadEquipmentDetail()
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

// 加载设备详情
const loadEquipmentDetail = async () => {
  try {
    loading.value = true
    const response = await equipmentApi.getDetail(props.equipmentId)
    if (response.data.success) {
      equipment.value = response.data.data
    }
  } catch (error) {
    console.error('加载设备详情失败:', error)
    ElMessage.error('加载设备详情失败')
  } finally {
    loading.value = false
  }
}

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    available: 'success',
    borrowed: 'warning',
    maintenance: 'info',
    damaged: 'danger',
    scrapped: 'info'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    available: '可用',
    borrowed: '已借出',
    maintenance: '维护中',
    damaged: '损坏',
    scrapped: '报废'
  }
  return texts[status] || status
}

// 获取借用状态类型
const getBorrowingStatusType = (status) => {
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

// 获取借用状态文本
const getBorrowingStatusText = (status) => {
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
  equipment.value = null
}

// 编辑设备
const handleEdit = () => {
  emit('edit', equipment.value)
  handleClose()
}
</script>

<style scoped>
.equipment-detail {
  max-height: 600px;
  overflow-y: auto;
}

.info-card {
  margin-bottom: 16px;
}

.info-card:last-child {
  margin-bottom: 0;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-item {
  margin-bottom: 12px;
  display: flex;
  align-items: flex-start;
}

.info-item label {
  font-weight: 500;
  color: #606266;
  min-width: 100px;
  margin-right: 8px;
}

.info-item span {
  color: #303133;
  word-break: break-all;
}

.stat-item {
  text-align: center;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 4px;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  color: #409EFF;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #909399;
}

.more-records {
  text-align: center;
  margin-top: 12px;
}
</style>
