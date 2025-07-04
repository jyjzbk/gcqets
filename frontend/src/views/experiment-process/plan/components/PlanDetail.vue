<template>
  <el-dialog
    v-model="dialogVisible"
    title="实验计划详情"
    width="900px"
    :close-on-click-modal="false"
  >
    <div v-loading="loading" class="plan-detail">
      <div v-if="planData" class="detail-content">
        <!-- 基本信息 -->
        <el-card class="detail-card">
          <template #header>
            <div class="card-header">
              <span>基本信息</span>
              <div class="header-tags">
                <el-tag :type="getStatusType(planData.status)">
                  {{ getStatusLabel(planData.status) }}
                </el-tag>
                <el-tag :type="getPriorityType(planData.priority)" class="ml-2">
                  {{ getPriorityLabel(planData.priority) }}
                </el-tag>
              </div>
            </div>
          </template>
          
          <el-descriptions :column="2" border>
            <el-descriptions-item label="计划编码">{{ planData.code }}</el-descriptions-item>
            <el-descriptions-item label="计划名称">{{ planData.name }}</el-descriptions-item>
            <el-descriptions-item label="班级名称">{{ planData.class_name || '-' }}</el-descriptions-item>
            <el-descriptions-item label="学生人数">{{ planData.student_count || '-' }}</el-descriptions-item>
            <el-descriptions-item label="计划日期">{{ planData.planned_date || '-' }}</el-descriptions-item>
            <el-descriptions-item label="计划时长">{{ planData.planned_duration ? planData.planned_duration + ' 分钟' : '-' }}</el-descriptions-item>
            <el-descriptions-item label="创建教师">
              {{ planData.teacher?.real_name || planData.teacher?.username || '-' }}
            </el-descriptions-item>
            <el-descriptions-item label="所属组织">{{ planData.organization?.name || '-' }}</el-descriptions-item>
            <el-descriptions-item label="是否公开">
              <el-tag :type="planData.is_public ? 'success' : 'info'" size="small">
                {{ planData.is_public ? '是' : '否' }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="修改次数">{{ planData.revision_count || 0 }}</el-descriptions-item>
            <el-descriptions-item label="创建时间" :span="2">{{ formatDateTime(planData.created_at) }}</el-descriptions-item>
          </el-descriptions>
        </el-card>

        <!-- 实验信息 -->
        <el-card class="detail-card">
          <template #header>
            <span>实验信息</span>
          </template>
          
          <el-descriptions :column="1" border>
            <el-descriptions-item label="实验目录">
              <div v-if="planData.experiment_catalog">
                <div class="catalog-info">
                  <span class="catalog-name">{{ planData.experiment_catalog.name }}</span>
                  <el-tag size="small" class="ml-2">{{ getSubjectLabel(planData.experiment_catalog.subject) }}</el-tag>
                  <el-tag size="small" type="info" class="ml-1">{{ getGradeLabel(planData.experiment_catalog.grade) }}</el-tag>
                </div>
                <div class="catalog-code">编码：{{ planData.experiment_catalog.code }}</div>
              </div>
              <span v-else>-</span>
            </el-descriptions-item>
            <el-descriptions-item label="课程标准">
              <div v-if="planData.curriculum_standard">
                <div class="standard-info">
                  <span class="standard-name">{{ planData.curriculum_standard.name }}</span>
                  <el-tag size="small" type="warning" class="ml-2">{{ planData.curriculum_standard.version }}</el-tag>
                </div>
                <div class="standard-code">编码：{{ planData.curriculum_standard.code }}</div>
              </div>
              <span v-else>-</span>
            </el-descriptions-item>
          </el-descriptions>
        </el-card>

        <!-- 教学设计 -->
        <el-card class="detail-card">
          <template #header>
            <span>教学设计</span>
          </template>
          
          <div class="teaching-content">
            <div v-if="planData.description" class="content-section">
              <h4>计划描述</h4>
              <div class="content-text">{{ planData.description }}</div>
            </div>
            
            <div v-if="planData.objectives" class="content-section">
              <h4>教学目标</h4>
              <div class="content-text">{{ planData.objectives }}</div>
            </div>
            
            <div v-if="planData.key_points" class="content-section">
              <h4>重点难点</h4>
              <div class="content-text">{{ planData.key_points }}</div>
            </div>
            
            <div v-if="planData.safety_requirements" class="content-section">
              <h4>安全要求</h4>
              <div class="content-text">{{ planData.safety_requirements }}</div>
            </div>
          </div>
        </el-card>

        <!-- 资源需求 -->
        <el-card class="detail-card">
          <template #header>
            <span>资源需求</span>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <h4>设备需求</h4>
              <div v-if="planData.equipment_requirements && planData.equipment_requirements.length > 0">
                <el-table :data="planData.equipment_requirements" size="small" border>
                  <el-table-column prop="name" label="设备名称" />
                  <el-table-column prop="quantity" label="数量" width="80" />
                  <el-table-column prop="specification" label="规格要求" show-overflow-tooltip />
                </el-table>
              </div>
              <div v-else class="no-data">暂无设备需求</div>
            </el-col>
            
            <el-col :span="12">
              <h4>材料需求</h4>
              <div v-if="planData.material_requirements && planData.material_requirements.length > 0">
                <el-table :data="planData.material_requirements" size="small" border>
                  <el-table-column prop="name" label="材料名称" />
                  <el-table-column prop="quantity" label="数量" width="80" />
                  <el-table-column prop="unit" label="单位" width="60" />
                  <el-table-column prop="specification" label="规格要求" show-overflow-tooltip />
                </el-table>
              </div>
              <div v-else class="no-data">暂无材料需求</div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 审批信息 -->
        <el-card v-if="showApprovalInfo" class="detail-card">
          <template #header>
            <span>审批信息</span>
          </template>
          
          <el-descriptions :column="1" border>
            <el-descriptions-item v-if="planData.approved_by" label="审批人">
              {{ planData.approver?.real_name || planData.approver?.username || '-' }}
            </el-descriptions-item>
            <el-descriptions-item v-if="planData.approved_at" label="审批时间">
              {{ formatDateTime(planData.approved_at) }}
            </el-descriptions-item>
            <el-descriptions-item v-if="planData.approval_notes" label="审批意见">
              <div class="approval-notes">{{ planData.approval_notes }}</div>
            </el-descriptions-item>
            <el-descriptions-item v-if="planData.rejection_reason" label="拒绝原因">
              <div class="rejection-reason">{{ planData.rejection_reason }}</div>
            </el-descriptions-item>
          </el-descriptions>
        </el-card>
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">关闭</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { experimentPlanApi, getStatusType, getStatusLabel, getPriorityType, getPriorityLabel, getSubjectLabel, getGradeLabel } from '@/api/experimentPlan'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  planId: {
    type: [String, Number],
    default: null
  }
})

// Emits
const emit = defineEmits(['update:visible'])

// 响应式数据
const loading = ref(false)
const planData = ref(null)

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const showApprovalInfo = computed(() => {
  return planData.value && (
    planData.value.approved_by ||
    planData.value.approval_notes ||
    planData.value.rejection_reason
  )
})

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal && props.planId) {
    loadPlanDetail()
  }
})

// 方法
const loadPlanDetail = async () => {
  loading.value = true
  try {
    const response = await experimentPlanApi.getDetail(props.planId)
    if (response.data.success) {
      planData.value = response.data.data
    }
  } catch (error) {
    ElMessage.error('获取计划详情失败：' + error.message)
    handleClose()
  } finally {
    loading.value = false
  }
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

const handleClose = () => {
  emit('update:visible', false)
  planData.value = null
}
</script>

<style scoped>
.plan-detail {
  min-height: 400px;
}

.detail-card {
  margin-bottom: 20px;
}

.detail-card:last-child {
  margin-bottom: 0;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-tags .ml-2 {
  margin-left: 8px;
}

.catalog-info,
.standard-info {
  margin-bottom: 4px;
}

.catalog-name,
.standard-name {
  font-weight: 500;
}

.catalog-code,
.standard-code {
  font-size: 12px;
  color: #909399;
}

.ml-2 {
  margin-left: 8px;
}

.ml-1 {
  margin-left: 4px;
}

.teaching-content {
  padding: 10px 0;
}

.content-section {
  margin-bottom: 20px;
}

.content-section:last-child {
  margin-bottom: 0;
}

.content-section h4 {
  margin: 0 0 8px 0;
  color: #303133;
  font-size: 14px;
  font-weight: 500;
}

.content-text {
  color: #606266;
  line-height: 1.6;
  white-space: pre-wrap;
}

.no-data {
  text-align: center;
  color: #909399;
  padding: 20px;
  background-color: #fafafa;
  border-radius: 4px;
}

.approval-notes,
.rejection-reason {
  color: #606266;
  line-height: 1.6;
  white-space: pre-wrap;
}

.rejection-reason {
  color: #f56c6c;
}

.dialog-footer {
  text-align: right;
}
</style>
