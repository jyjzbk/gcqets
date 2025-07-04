<template>
  <el-dialog
    v-model="dialogVisible"
    title="审核日志"
    width="900px"
    :close-on-click-modal="false"
  >
    <div v-loading="loading" class="review-logs">
      <!-- 日志统计 -->
      <div class="logs-stats">
        <el-row :gutter="20">
          <el-col :span="6">
            <div class="stat-item">
              <div class="stat-number">{{ logs.length }}</div>
              <div class="stat-label">总操作数</div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="stat-item">
              <div class="stat-number">{{ reviewerCount }}</div>
              <div class="stat-label">参与人数</div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="stat-item">
              <div class="stat-number">{{ aiCheckCount }}</div>
              <div class="stat-label">AI检查次数</div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="stat-item">
              <div class="stat-number">{{ totalDuration }}分</div>
              <div class="stat-label">总耗时</div>
            </div>
          </el-col>
        </el-row>
      </div>

      <!-- 日志时间线 -->
      <div class="logs-timeline">
        <el-timeline>
          <el-timeline-item
            v-for="log in logs"
            :key="log.id"
            :timestamp="formatDateTime(log.created_at)"
            :type="getTimelineType(log.review_type)"
            :icon="getTimelineIcon(log.review_type)"
            placement="top"
          >
            <el-card class="log-card">
              <div class="log-header">
                <div class="log-title">
                  <el-tag :type="getReviewTypeType(log.review_type)">
                    {{ getReviewTypeLabel(log.review_type) }}
                  </el-tag>
                  <span class="status-change">
                    {{ log.previous_status }} → {{ log.new_status }}
                  </span>
                </div>
                <div class="log-meta">
                  <span class="reviewer">{{ log.reviewer_name }}</span>
                  <span v-if="log.review_duration" class="duration">
                    耗时: {{ formatReviewDuration(log.review_duration) }}
                  </span>
                  <el-tag v-if="log.is_ai_review" type="info" size="small">AI</el-tag>
                  <el-tag v-if="log.is_urgent" type="danger" size="small">紧急</el-tag>
                </div>
              </div>

              <div v-if="log.review_notes" class="log-content">
                <h5>审核意见：</h5>
                <p class="review-notes">{{ log.review_notes }}</p>
              </div>

              <div v-if="log.review_category" class="log-category">
                <el-tag size="small" :color="getReviewCategoryColor(log.review_category)">
                  <el-icon>
                    <component :is="getReviewCategoryIcon(log.review_category)" />
                  </el-icon>
                  {{ getReviewCategoryLabel(log.review_category) }}
                </el-tag>
              </div>

              <div v-if="log.review_score" class="log-score">
                <span class="score-label">评分：</span>
                <el-rate
                  :model-value="log.review_score"
                  :max="10"
                  disabled
                  show-score
                  score-template="{value}分"
                />
                <span class="score-grade" :style="{ color: getReviewScoreColor(log.review_score) }">
                  {{ getReviewGrade(log.review_score).label }}
                </span>
              </div>

              <!-- AI分析结果 -->
              <div v-if="log.ai_analysis_result" class="ai-analysis">
                <h5>AI分析结果：</h5>
                <div class="ai-stats">
                  <div v-if="log.ai_analysis_result.total_photos" class="ai-stat">
                    <span>检查照片：{{ log.ai_analysis_result.total_photos }}张</span>
                  </div>
                  <div v-if="log.ai_analysis_result.compliance_rate !== undefined" class="ai-stat">
                    <span>合规率：{{ log.ai_analysis_result.compliance_rate }}%</span>
                  </div>
                </div>
              </div>

              <!-- 审核详情 -->
              <div v-if="log.review_details" class="review-details">
                <el-collapse>
                  <el-collapse-item title="详细信息" name="details">
                    <div class="details-content">
                      <!-- 修改要求 -->
                      <div v-if="log.review_details.revision_requirements" class="revision-requirements">
                        <h6>修改要求：</h6>
                        <ul>
                          <li v-for="req in log.review_details.revision_requirements" :key="req">
                            {{ req }}
                          </li>
                        </ul>
                      </div>

                      <!-- 修改截止时间 -->
                      <div v-if="log.review_details.revision_deadline" class="revision-deadline">
                        <h6>修改截止：</h6>
                        <span>{{ formatDateTime(log.review_details.revision_deadline) }}</span>
                      </div>

                      <!-- 强制完成原因 -->
                      <div v-if="log.review_details.force_reason" class="force-reason">
                        <h6>强制原因：</h6>
                        <p>{{ log.review_details.force_reason }}</p>
                      </div>

                      <!-- 批量操作信息 -->
                      <div v-if="log.review_details.batch_operation" class="batch-info">
                        <h6>批量操作：</h6>
                        <span>批量大小：{{ log.review_details.batch_size }}</span>
                      </div>
                    </div>
                  </el-collapse-item>
                </el-collapse>
              </div>

              <!-- 操作信息 -->
              <div class="log-footer">
                <div class="operation-info">
                  <span class="ip-address">IP: {{ log.ip_address || '未记录' }}</span>
                  <span v-if="log.review_deadline" class="deadline">
                    截止: {{ formatDateTime(log.review_deadline) }}
                  </span>
                </div>
              </div>
            </el-card>
          </el-timeline-item>
        </el-timeline>
      </div>

      <!-- 空状态 -->
      <div v-if="logs.length === 0 && !loading" class="empty-state">
        <el-empty description="暂无审核日志" />
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">关闭</el-button>
        <el-button type="primary" @click="exportLogs">导出日志</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ElMessage } from 'element-plus'
import {
  Document,
  Check,
  Close,
  Warning,
  Setting,
  Cpu,
  User
} from '@element-plus/icons-vue'
import { 
  experimentReviewApi,
  getReviewTypeType,
  getReviewTypeLabel,
  getReviewCategoryLabel,
  getReviewCategoryIcon,
  getReviewCategoryColor,
  getReviewGrade,
  getReviewScoreColor,
  formatReviewDuration
} from '@/api/experimentReview'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  recordId: {
    type: [String, Number],
    default: null
  }
})

// Emits
const emit = defineEmits(['update:visible'])

// 响应式数据
const loading = ref(false)
const logs = ref([])

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const reviewerCount = computed(() => {
  const reviewers = new Set(logs.value.map(log => log.reviewer_id))
  return reviewers.size
})

const aiCheckCount = computed(() => {
  return logs.value.filter(log => log.is_ai_review).length
})

const totalDuration = computed(() => {
  return logs.value.reduce((total, log) => total + (log.review_duration || 0), 0)
})

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal && props.recordId) {
    loadLogs()
  }
})

// 方法
const loadLogs = async () => {
  loading.value = true
  try {
    const response = await experimentReviewApi.getReviewLogs(props.recordId)
    if (response.data.success) {
      logs.value = response.data.data
    }
  } catch (error) {
    ElMessage.error('获取审核日志失败：' + error.message)
  } finally {
    loading.value = false
  }
}

const getTimelineType = (reviewType) => {
  const types = {
    submit: 'primary',
    approve: 'success',
    reject: 'danger',
    revision_request: 'warning',
    force_complete: 'info',
    batch_approve: 'success',
    batch_reject: 'danger',
    ai_check: 'info',
    manual_check: 'primary'
  }
  return types[reviewType] || 'primary'
}

const getTimelineIcon = (reviewType) => {
  const icons = {
    submit: Document,
    approve: Check,
    reject: Close,
    revision_request: Warning,
    force_complete: Setting,
    batch_approve: Check,
    batch_reject: Close,
    ai_check: Cpu,
    manual_check: User
  }
  return icons[reviewType] || Document
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

const exportLogs = () => {
  // 导出日志功能
  const csvContent = generateCSV()
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  link.setAttribute('href', url)
  link.setAttribute('download', `审核日志_${props.recordId}_${new Date().toISOString().slice(0, 10)}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  
  ElMessage.success('日志导出成功')
}

const generateCSV = () => {
  const headers = ['时间', '操作类型', '状态变化', '审核人', '审核意见', '评分', '耗时']
  const rows = logs.value.map(log => [
    formatDateTime(log.created_at),
    getReviewTypeLabel(log.review_type),
    `${log.previous_status} → ${log.new_status}`,
    log.reviewer_name,
    log.review_notes || '',
    log.review_score || '',
    log.review_duration ? formatReviewDuration(log.review_duration) : ''
  ])
  
  const csvContent = [headers, ...rows]
    .map(row => row.map(field => `"${field}"`).join(','))
    .join('\n')
  
  return '\uFEFF' + csvContent // 添加BOM以支持中文
}

const handleClose = () => {
  emit('update:visible', false)
}
</script>

<style scoped>
.review-logs {
  max-height: 70vh;
  overflow-y: auto;
}

.logs-stats {
  margin-bottom: 20px;
  padding: 15px;
  background-color: #fafafa;
  border-radius: 4px;
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 20px;
  font-weight: bold;
  color: #303133;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 12px;
  color: #909399;
}

.logs-timeline {
  padding: 10px 0;
}

.log-card {
  margin-bottom: 10px;
}

.log-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.log-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.status-change {
  font-size: 12px;
  color: #909399;
}

.log-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.reviewer {
  color: #606266;
  font-weight: 500;
}

.duration {
  color: #909399;
}

.log-content {
  margin-bottom: 10px;
}

.log-content h5 {
  margin: 0 0 5px 0;
  font-size: 13px;
  color: #303133;
}

.review-notes {
  margin: 0;
  color: #606266;
  line-height: 1.5;
  white-space: pre-wrap;
}

.log-category {
  margin-bottom: 10px;
}

.log-score {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.score-label {
  margin-right: 8px;
  font-size: 13px;
  color: #606266;
}

.score-grade {
  margin-left: 8px;
  font-weight: 500;
}

.ai-analysis {
  margin-bottom: 10px;
  padding: 10px;
  background-color: #f0f9ff;
  border-radius: 4px;
}

.ai-analysis h5 {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: #303133;
}

.ai-stats {
  display: flex;
  gap: 15px;
}

.ai-stat {
  font-size: 12px;
  color: #606266;
}

.review-details {
  margin-bottom: 10px;
}

.details-content h6 {
  margin: 0 0 5px 0;
  font-size: 12px;
  color: #303133;
}

.details-content ul {
  margin: 0;
  padding-left: 20px;
}

.details-content li {
  font-size: 12px;
  color: #606266;
  margin-bottom: 2px;
}

.details-content p {
  margin: 0;
  font-size: 12px;
  color: #606266;
}

.log-footer {
  border-top: 1px solid #f0f0f0;
  padding-top: 8px;
  margin-top: 10px;
}

.operation-info {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #c0c4cc;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
}

.dialog-footer {
  text-align: right;
}
</style>
