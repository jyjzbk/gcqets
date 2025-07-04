<template>
  <div class="experiment-detail">
    <!-- 基本信息 -->
    <div class="detail-section">
      <h3>基本信息</h3>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="计划名称">
          {{ experiment.plan.name }}
        </el-descriptions-item>
        <el-descriptions-item label="计划编码">
          {{ experiment.plan.code }}
        </el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(experiment.plan.status)">
            {{ experiment.plan.statusLabel }}
          </el-tag>
          <el-tag 
            v-if="experiment.plan.isOverdue" 
            type="danger" 
            size="small" 
            style="margin-left: 8px"
          >
            逾期 {{ Math.abs(experiment.plan.daysUntilDue) }} 天
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="计划日期">
          {{ formatDate(experiment.plan.plannedDate) }}
        </el-descriptions-item>
        <el-descriptions-item label="计划时长">
          {{ experiment.plan.plannedDuration }} 分钟
        </el-descriptions-item>
        <el-descriptions-item label="学生人数">
          {{ experiment.plan.studentCount }} 人
        </el-descriptions-item>
        <el-descriptions-item label="班级">
          {{ experiment.plan.className }}
        </el-descriptions-item>
        <el-descriptions-item label="任课教师">
          {{ experiment.plan.teacher.name }}
        </el-descriptions-item>
        <el-descriptions-item label="实验目录" span="2">
          {{ experiment.plan.catalog?.name || '未指定' }}
        </el-descriptions-item>
      </el-descriptions>
    </div>

    <!-- 实验内容 -->
    <div class="detail-section">
      <h3>实验内容</h3>
      <el-row :gutter="20">
        <el-col :span="24">
          <div class="content-item">
            <h4>实验描述</h4>
            <p>{{ experiment.plan.description || '暂无描述' }}</p>
          </div>
        </el-col>
        <el-col :span="12">
          <div class="content-item">
            <h4>教学目标</h4>
            <p>{{ experiment.plan.objectives || '暂无目标' }}</p>
          </div>
        </el-col>
        <el-col :span="12">
          <div class="content-item">
            <h4>重点难点</h4>
            <p>{{ experiment.plan.keyPoints || '暂无重点难点' }}</p>
          </div>
        </el-col>
        <el-col :span="24">
          <div class="content-item">
            <h4>安全要求</h4>
            <p>{{ experiment.plan.safetyRequirements || '暂无安全要求' }}</p>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 执行记录 -->
    <div class="detail-section">
      <h3>
        执行记录 
        <el-tag size="small" type="info">{{ experiment.records.length }} 条</el-tag>
      </h3>
      
      <div v-if="experiment.records.length === 0" class="no-records">
        <el-empty description="暂无执行记录" />
      </div>
      
      <el-table v-else :data="experiment.records" border>
        <el-table-column prop="executionDate" label="执行日期" width="120">
          <template #default="{ row }">
            {{ formatDate(row.executionDate) }}
          </template>
        </el-table-column>
        <el-table-column prop="completionStatus" label="完成状态" width="120">
          <template #default="{ row }">
            <el-tag :type="getCompletionStatusType(row.completionStatus)">
              {{ getCompletionStatusLabel(row.completionStatus) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="actualDuration" label="实际时长" width="100">
          <template #default="{ row }">
            {{ row.actualDuration || '-' }} 分钟
          </template>
        </el-table-column>
        <el-table-column prop="actualStudentCount" label="实际人数" width="100">
          <template #default="{ row }">
            {{ row.actualStudentCount || '-' }} 人
          </template>
        </el-table-column>
        <el-table-column prop="creator" label="填报人" width="120" />
        <el-table-column prop="photoCount" label="照片数量" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.photoCount > 0" type="success" size="small">
              {{ row.photoCount }} 张
            </el-tag>
            <span v-else class="text-muted">无照片</span>
          </template>
        </el-table-column>
        <el-table-column prop="createdAt" label="创建时间" width="160">
          <template #default="{ row }">
            {{ formatDateTime(row.createdAt) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <el-button 
              type="primary" 
              size="small" 
              @click="viewRecord(row.id)"
            >
              查看
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 操作按钮 -->
    <div class="detail-actions">
      <el-button @click="$emit('close')">关闭</el-button>
      <el-button 
        v-if="canEdit" 
        type="primary" 
        @click="editPlan"
      >
        编辑计划
      </el-button>
      <el-button 
        v-if="canCreateRecord" 
        type="success" 
        @click="createRecord"
      >
        创建记录
      </el-button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  experiment: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const router = useRouter()
const authStore = useAuthStore()

// 计算属性
const canEdit = computed(() => {
  const user = authStore.user
  const plan = props.experiment.plan
  
  // 系统管理员可以编辑所有计划
  if (user.user_type === 'admin') return true
  
  // 计划创建者可以编辑自己的计划（草稿状态）
  if (plan.teacher.id === user.id && plan.status === 'draft') return true
  
  return false
})

const canCreateRecord = computed(() => {
  const user = authStore.user
  const plan = props.experiment.plan
  
  // 只有已批准的计划才能创建记录
  if (plan.status !== 'approved') return false
  
  // 系统管理员或计划创建者可以创建记录
  return user.user_type === 'admin' || plan.teacher.id === user.id
})

// 方法
const getStatusType = (status) => {
  const types = {
    'draft': 'info',
    'pending': 'warning',
    'approved': 'success',
    'rejected': 'danger',
    'executing': 'primary',
    'completed': 'info',
    'cancelled': 'danger'
  }
  return types[status] || 'info'
}

const getCompletionStatusType = (status) => {
  const types = {
    'not_started': 'info',
    'in_progress': 'warning',
    'partial': 'warning',
    'completed': 'success',
    'cancelled': 'danger'
  }
  return types[status] || 'info'
}

const getCompletionStatusLabel = (status) => {
  const labels = {
    'not_started': '未开始',
    'in_progress': '进行中',
    'partial': '部分完成',
    'completed': '已完成',
    'cancelled': '已取消'
  }
  return labels[status] || status
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('zh-CN')
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const editPlan = () => {
  router.push(`/experiment-plans/edit/${props.experiment.plan.id}`)
  emit('close')
}

const createRecord = () => {
  router.push(`/experiment-records/create?plan_id=${props.experiment.plan.id}`)
  emit('close')
}

const viewRecord = (recordId) => {
  router.push(`/experiment-records/${recordId}`)
  emit('close')
}
</script>

<style scoped>
.experiment-detail {
  max-height: 70vh;
  overflow-y: auto;
}

.detail-section {
  margin-bottom: 30px;
}

.detail-section h3 {
  margin: 0 0 15px 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
  border-bottom: 2px solid #409eff;
  padding-bottom: 8px;
}

.content-item {
  margin-bottom: 20px;
}

.content-item h4 {
  margin: 0 0 8px 0;
  color: #606266;
  font-size: 14px;
  font-weight: 500;
}

.content-item p {
  margin: 0;
  color: #303133;
  line-height: 1.6;
  background-color: #f8f9fa;
  padding: 12px;
  border-radius: 4px;
  border-left: 3px solid #409eff;
}

.no-records {
  text-align: center;
  padding: 40px 0;
}

.detail-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
}

.text-muted {
  color: #c0c4cc;
}

/* 表格样式优化 */
:deep(.el-table) {
  font-size: 14px;
}

:deep(.el-table th) {
  background-color: #f8f9fa;
  color: #606266;
  font-weight: 500;
}

:deep(.el-table td) {
  padding: 12px 0;
}

/* 描述列表样式优化 */
:deep(.el-descriptions__label) {
  font-weight: 500;
  color: #606266;
}

:deep(.el-descriptions__content) {
  color: #303133;
}
</style>
