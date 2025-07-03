<template>
  <el-dialog
    v-model="dialogVisible"
    title="材料详情"
    width="800px"
    :before-close="handleClose"
  >
    <div v-loading="loading" class="material-detail">
      <template v-if="material">
        <!-- 基本信息 -->
        <el-card class="info-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span>基本信息</span>
              <el-tag :type="getStatusType(material.status)">
                {{ getStatusText(material.status) }}
              </el-tag>
            </div>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="info-item">
                <label>材料名称：</label>
                <span>{{ material.name }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>材料编码：</label>
                <span>{{ material.code }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>材料分类：</label>
                <span>{{ material.category?.name }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>规格型号：</label>
                <span>{{ material.specification || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>品牌：</label>
                <span>{{ material.brand || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>计量单位：</label>
                <span>{{ material.unit }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>单价：</label>
                <span>{{ material.unit_price ? `¥${material.unit_price}` : '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>供应商：</label>
                <span>{{ material.supplier || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>有效期至：</label>
                <span :class="getExpiryClass(material.expiry_date)">
                  {{ material.expiry_date || '-' }}
                </span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>创建时间：</label>
                <span>{{ formatDateTime(material.created_at) }}</span>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="info-item">
                <label>材料描述：</label>
                <span>{{ material.description || '-' }}</span>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 库存信息 -->
        <el-card class="info-card" shadow="never">
          <template #header>
            <span>库存信息</span>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number" :class="getStockClass(material)">
                  {{ material.current_stock || 0 }}
                </div>
                <div class="stat-label">当前库存</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number">{{ material.min_stock || 0 }}</div>
                <div class="stat-label">最低库存</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number">{{ material.max_stock || 0 }}</div>
                <div class="stat-label">最高库存</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number">{{ material.total_purchased || 0 }}</div>
                <div class="stat-label">累计采购</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number">{{ material.total_consumed || 0 }}</div>
                <div class="stat-label">累计消耗</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number">{{ material.usages?.length || 0 }}</div>
                <div class="stat-label">使用记录数</div>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>最后采购日期：</label>
                <span>{{ material.last_purchase_date || '-' }}</span>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 存储信息 -->
        <el-card class="info-card" shadow="never">
          <template #header>
            <span>存储信息</span>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="info-item">
                <label>存储位置：</label>
                <span>{{ material.storage_location || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="info-item">
                <label>存储条件：</label>
                <span>{{ material.storage_conditions || '-' }}</span>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="info-item">
                <label>安全注意事项：</label>
                <span>{{ material.safety_notes || '-' }}</span>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 使用记录 -->
        <el-card v-if="material.usages && material.usages.length > 0" class="info-card" shadow="never">
          <template #header>
            <span>最近使用记录</span>
          </template>
          
          <el-table :data="material.usages.slice(0, 5)" size="small">
            <el-table-column prop="user.real_name" label="使用人" width="100" />
            <el-table-column prop="quantity_used" label="使用数量" width="80">
              <template #default="{ row }">
                {{ row.quantity_used }} {{ material.unit }}
              </template>
            </el-table-column>
            <el-table-column prop="usage_type" label="使用类型" width="100">
              <template #default="{ row }">
                {{ getUsageTypeText(row.usage_type) }}
              </template>
            </el-table-column>
            <el-table-column prop="used_at" label="使用时间" width="150">
              <template #default="{ row }">
                {{ formatDateTime(row.used_at) }}
              </template>
            </el-table-column>
            <el-table-column prop="purpose" label="使用目的" min-width="150" />
          </el-table>
          
          <div v-if="material.usages.length > 5" class="more-records">
            <el-text type="info">仅显示最近5条记录</el-text>
          </div>
        </el-card>

        <!-- 库存变动记录 -->
        <el-card v-if="material.stock_logs && material.stock_logs.length > 0" class="info-card" shadow="never">
          <template #header>
            <span>最近库存变动</span>
          </template>
          
          <el-table :data="material.stock_logs.slice(0, 5)" size="small">
            <el-table-column prop="operation_type" label="操作类型" width="100">
              <template #default="{ row }">
                {{ getOperationTypeText(row.operation_type) }}
              </template>
            </el-table-column>
            <el-table-column label="变动数量" width="100">
              <template #default="{ row }">
                <span :class="row.quantity_change > 0 ? 'text-success' : 'text-danger'">
                  {{ row.quantity_change > 0 ? '+' : '' }}{{ row.quantity_change }}
                </span>
              </template>
            </el-table-column>
            <el-table-column prop="quantity_after" label="变动后库存" width="100" />
            <el-table-column prop="operator.real_name" label="操作人" width="100" />
            <el-table-column prop="operated_at" label="操作时间" width="150">
              <template #default="{ row }">
                {{ formatDateTime(row.operated_at) }}
              </template>
            </el-table-column>
            <el-table-column prop="reason" label="操作原因" min-width="150" />
          </el-table>
          
          <div v-if="material.stock_logs.length > 5" class="more-records">
            <el-text type="info">仅显示最近5条记录</el-text>
          </div>
        </el-card>
      </template>
    </div>

    <template #footer>
      <el-button @click="handleClose">关闭</el-button>
      <el-button type="primary" @click="handleEdit">编辑材料</el-button>
      <el-button type="warning" @click="handleAdjustStock">库存调整</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
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
  }
})

// Emits
const emit = defineEmits(['update:visible', 'edit', 'adjust-stock', 'refresh'])

// 响应式数据
const dialogVisible = ref(false)
const loading = ref(false)
const material = ref(null)

// 监听visible变化
watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
  if (newVal && props.materialId) {
    loadMaterialDetail()
  }
})

// 监听dialogVisible变化
watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

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

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    active: 'success',
    inactive: 'info',
    expired: 'danger',
    out_of_stock: 'warning'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    active: '正常',
    inactive: '停用',
    expired: '过期',
    out_of_stock: '缺货'
  }
  return texts[status] || status
}

// 获取库存样式
const getStockClass = (material) => {
  if (material.current_stock <= 0) return 'text-danger'
  if (material.current_stock <= material.min_stock) return 'text-warning'
  return 'text-success'
}

// 获取有效期样式
const getExpiryClass = (expiryDate) => {
  if (!expiryDate) return ''
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'text-danger'
  if (diffDays <= 30) return 'text-warning'
  return ''
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

// 获取操作类型文本
const getOperationTypeText = (type) => {
  const texts = {
    purchase: '采购入库',
    usage: '使用消耗',
    adjustment: '库存调整',
    expired: '过期处理',
    damaged: '损坏处理'
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
  material.value = null
}

// 编辑材料
const handleEdit = () => {
  emit('edit', material.value)
  handleClose()
}

// 库存调整
const handleAdjustStock = () => {
  emit('adjust-stock', material.value)
  handleClose()
}
</script>

<style scoped>
.material-detail {
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

.text-success { color: #67C23A; }
.text-warning { color: #E6A23C; }
.text-danger { color: #F56C6C; }

.more-records {
  text-align: center;
  margin-top: 12px;
}
</style>
