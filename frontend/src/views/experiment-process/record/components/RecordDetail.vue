<template>
  <el-dialog
    v-model="dialogVisible"
    title="实验记录详情"
    width="1000px"
    :close-on-click-modal="false"
  >
    <div v-loading="loading" class="record-detail">
      <div v-if="recordData" class="detail-content">
        <!-- 基本信息 -->
        <el-card class="detail-card">
          <template #header>
            <div class="card-header">
              <span>基本信息</span>
              <div class="header-tags">
                <el-tag :type="getRecordStatusType(recordData.status)">
                  {{ getRecordStatusLabel(recordData.status) }}
                </el-tag>
                <el-tag :type="getCompletionStatusType(recordData.completion_status)" class="ml-2">
                  {{ getCompletionStatusLabel(recordData.completion_status) }}
                </el-tag>
              </div>
            </div>
          </template>
          
          <el-descriptions :column="2" border>
            <el-descriptions-item label="实验计划">
              <div v-if="recordData.experiment_plan">
                <div class="plan-name">{{ recordData.experiment_plan.name }}</div>
                <div class="plan-code">{{ recordData.experiment_plan.code }}</div>
              </div>
            </el-descriptions-item>
            <el-descriptions-item label="执行日期">{{ recordData.execution_date }}</el-descriptions-item>
            <el-descriptions-item label="开始时间">{{ recordData.start_time || '-' }}</el-descriptions-item>
            <el-descriptions-item label="结束时间">{{ recordData.end_time || '-' }}</el-descriptions-item>
            <el-descriptions-item label="实际时长">
              {{ recordData.actual_duration ? formatDuration(recordData.actual_duration) : '-' }}
            </el-descriptions-item>
            <el-descriptions-item label="实际学生数">{{ recordData.actual_student_count || '-' }}</el-descriptions-item>
            <el-descriptions-item label="完成度">
              <el-progress
                :percentage="recordData.completion_percentage || 0"
                :color="getCompletionColor(recordData.completion_percentage || 0)"
                :stroke-width="8"
                text-inside
                style="width: 200px"
              />
            </el-descriptions-item>
            <el-descriptions-item label="器材确认">
              <el-tag :type="recordData.equipment_confirmed ? 'success' : 'info'">
                {{ recordData.equipment_confirmed ? '已确认' : '未确认' }}
              </el-tag>
              <span v-if="recordData.equipment_confirmed_at" class="confirm-time">
                ({{ formatDateTime(recordData.equipment_confirmed_at) }})
              </span>
            </el-descriptions-item>
            <el-descriptions-item label="照片数量">
              <el-badge :value="recordData.photo_count || 0">
                <el-icon><Picture /></el-icon>
              </el-badge>
            </el-descriptions-item>
            <el-descriptions-item label="创建人">
              {{ recordData.creator?.real_name || recordData.creator?.username || '-' }}
            </el-descriptions-item>
            <el-descriptions-item label="创建时间" :span="2">
              {{ formatDateTime(recordData.created_at) }}
            </el-descriptions-item>
          </el-descriptions>
        </el-card>

        <!-- 执行情况 -->
        <el-card class="detail-card">
          <template #header>
            <span>执行情况</span>
          </template>
          
          <div class="execution-content">
            <div v-if="recordData.execution_notes" class="content-section">
              <h4>执行说明</h4>
              <div class="content-text">{{ recordData.execution_notes }}</div>
            </div>
            
            <div v-if="recordData.problems_encountered" class="content-section">
              <h4>遇到的问题</h4>
              <div class="content-text">{{ recordData.problems_encountered }}</div>
            </div>
            
            <div v-if="recordData.solutions_applied" class="content-section">
              <h4>解决方案</h4>
              <div class="content-text">{{ recordData.solutions_applied }}</div>
            </div>
            
            <div v-if="recordData.teaching_reflection" class="content-section">
              <h4>教学反思</h4>
              <div class="content-text">{{ recordData.teaching_reflection }}</div>
            </div>
            
            <div v-if="recordData.student_feedback" class="content-section">
              <h4>学生反馈</h4>
              <div class="content-text">{{ recordData.student_feedback }}</div>
            </div>
            
            <div v-if="recordData.safety_incidents" class="content-section">
              <h4>安全事件</h4>
              <div class="content-text safety-incidents">{{ recordData.safety_incidents }}</div>
            </div>
          </div>
        </el-card>

        <!-- 资源使用 -->
        <el-card class="detail-card">
          <template #header>
            <span>资源使用</span>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <h4>使用的设备</h4>
              <div v-if="recordData.equipment_used && recordData.equipment_used.length > 0">
                <el-table :data="recordData.equipment_used" size="small" border>
                  <el-table-column prop="name" label="设备名称" />
                  <el-table-column prop="quantity" label="数量" width="80" />
                  <el-table-column prop="condition" label="使用状况" />
                  <el-table-column prop="notes" label="备注" show-overflow-tooltip />
                </el-table>
              </div>
              <div v-else class="no-data">暂无设备使用记录</div>
            </el-col>
            
            <el-col :span="12">
              <h4>消耗的材料</h4>
              <div v-if="recordData.materials_consumed && recordData.materials_consumed.length > 0">
                <el-table :data="recordData.materials_consumed" size="small" border>
                  <el-table-column prop="name" label="材料名称" />
                  <el-table-column prop="quantity" label="数量" width="80" />
                  <el-table-column prop="unit" label="单位" width="60" />
                  <el-table-column prop="notes" label="备注" show-overflow-tooltip />
                </el-table>
              </div>
              <div v-else class="no-data">暂无材料消耗记录</div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 实验数据 -->
        <el-card v-if="recordData.data_records && recordData.data_records.length > 0" class="detail-card">
          <template #header>
            <span>实验数据</span>
          </template>
          
          <el-table :data="recordData.data_records" border>
            <el-table-column prop="parameter" label="参数名称" />
            <el-table-column prop="value" label="测量值" />
            <el-table-column prop="unit" label="单位" width="100" />
            <el-table-column prop="notes" label="备注" show-overflow-tooltip />
          </el-table>
        </el-card>

        <!-- 实验照片 -->
        <el-card v-if="recordData.photos && recordData.photos.length > 0" class="detail-card">
          <template #header>
            <span>实验照片</span>
          </template>
          
          <PhotoGallery :photos="recordData.photos" />
        </el-card>

        <!-- 审核信息 -->
        <el-card v-if="showReviewInfo" class="detail-card">
          <template #header>
            <span>审核信息</span>
          </template>
          
          <el-descriptions :column="1" border>
            <el-descriptions-item v-if="recordData.reviewed_by" label="审核人">
              {{ recordData.reviewer?.real_name || recordData.reviewer?.username || '-' }}
            </el-descriptions-item>
            <el-descriptions-item v-if="recordData.reviewed_at" label="审核时间">
              {{ formatDateTime(recordData.reviewed_at) }}
            </el-descriptions-item>
            <el-descriptions-item v-if="recordData.review_notes" label="审核意见">
              <div class="review-notes">{{ recordData.review_notes }}</div>
            </el-descriptions-item>
          </el-descriptions>
        </el-card>

        <!-- 验证结果 -->
        <el-card v-if="recordData.validation_results" class="detail-card">
          <template #header>
            <span>验证结果</span>
          </template>
          
          <div class="validation-results">
            <div class="validation-status">
              <el-tag :type="recordData.validation_results.valid ? 'success' : 'warning'">
                {{ recordData.validation_results.valid ? '验证通过' : '存在问题' }}
              </el-tag>
              <span class="completion-text">
                完成度：{{ recordData.validation_results.completion_percentage }}%
              </span>
            </div>
            
            <div v-if="recordData.validation_results.errors && recordData.validation_results.errors.length > 0" class="validation-errors">
              <h5>错误信息：</h5>
              <ul>
                <li v-for="error in recordData.validation_results.errors" :key="error" class="error-item">
                  {{ error }}
                </li>
              </ul>
            </div>
            
            <div v-if="recordData.validation_results.warnings && recordData.validation_results.warnings.length > 0" class="validation-warnings">
              <h5>建议改进：</h5>
              <ul>
                <li v-for="warning in recordData.validation_results.warnings" :key="warning" class="warning-item">
                  {{ warning }}
                </li>
              </ul>
            </div>
          </div>
        </el-card>
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">关闭</el-button>
        <el-button v-if="canEdit" type="warning" @click="editRecord">编辑记录</el-button>
        <el-button v-if="canUploadPhoto" type="success" @click="uploadPhoto">上传照片</el-button>
      </div>
    </template>

    <!-- 照片上传对话框 -->
    <PhotoUpload
      v-model:visible="photoUploadVisible"
      :record-id="recordData?.id"
      @success="handlePhotoUploadSuccess"
    />
  </el-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { Picture } from '@element-plus/icons-vue'
import { 
  experimentRecordApi, 
  getRecordStatusType, 
  getRecordStatusLabel,
  getCompletionStatusType, 
  getCompletionStatusLabel,
  formatDuration,
  getCompletionColor
} from '@/api/experimentRecord'
import { useAuthStore } from '@/stores/auth'
import PhotoGallery from './PhotoGallery.vue'
import PhotoUpload from './PhotoUpload.vue'

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
const emit = defineEmits(['update:visible', 'edit'])

// 状态管理
const authStore = useAuthStore()

// 响应式数据
const loading = ref(false)
const recordData = ref(null)
const photoUploadVisible = ref(false)

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const showReviewInfo = computed(() => {
  return recordData.value && (
    recordData.value.reviewed_by ||
    recordData.value.review_notes
  )
})

const canEdit = computed(() => {
  return recordData.value && 
         (recordData.value.status === 'draft' || recordData.value.status === 'revision_required') &&
         (authStore.user?.user_type === 'admin' || recordData.value.created_by === authStore.user?.id)
})

const canUploadPhoto = computed(() => {
  return recordData.value && 
         (recordData.value.status === 'draft' || recordData.value.status === 'revision_required') &&
         (authStore.user?.user_type === 'admin' || recordData.value.created_by === authStore.user?.id)
})

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal && props.recordId) {
    loadRecordDetail()
  }
})

// 方法
const loadRecordDetail = async () => {
  loading.value = true
  try {
    const response = await experimentRecordApi.getDetail(props.recordId)
    if (response.data.success) {
      recordData.value = response.data.data
    }
  } catch (error) {
    ElMessage.error('获取记录详情失败：' + error.message)
    handleClose()
  } finally {
    loading.value = false
  }
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

const editRecord = () => {
  emit('edit', recordData.value)
  handleClose()
}

const uploadPhoto = () => {
  photoUploadVisible.value = true
}

const handlePhotoUploadSuccess = () => {
  photoUploadVisible.value = false
  loadRecordDetail() // 重新加载数据以更新照片信息
}

const handleClose = () => {
  emit('update:visible', false)
  recordData.value = null
}
</script>

<style scoped>
.record-detail {
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

.plan-name {
  font-weight: 500;
  color: #303133;
}

.plan-code {
  font-size: 12px;
  color: #909399;
  margin-top: 2px;
}

.confirm-time {
  font-size: 12px;
  color: #909399;
  margin-left: 8px;
}

.execution-content {
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

.safety-incidents {
  color: #f56c6c;
  background-color: #fef0f0;
  padding: 10px;
  border-radius: 4px;
  border: 1px solid #fbc4c4;
}

.no-data {
  text-align: center;
  color: #909399;
  padding: 20px;
  background-color: #fafafa;
  border-radius: 4px;
}

.review-notes {
  color: #606266;
  line-height: 1.6;
  white-space: pre-wrap;
}

.validation-results {
  padding: 10px 0;
}

.validation-status {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.completion-text {
  margin-left: 10px;
  color: #606266;
}

.validation-errors,
.validation-warnings {
  margin-bottom: 15px;
}

.validation-errors h5,
.validation-warnings h5 {
  margin: 0 0 8px 0;
  font-size: 14px;
}

.validation-errors h5 {
  color: #f56c6c;
}

.validation-warnings h5 {
  color: #e6a23c;
}

.error-item {
  color: #f56c6c;
  margin-bottom: 4px;
}

.warning-item {
  color: #e6a23c;
  margin-bottom: 4px;
}

.dialog-footer {
  text-align: right;
}
</style>
