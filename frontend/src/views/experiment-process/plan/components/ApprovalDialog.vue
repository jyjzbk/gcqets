<template>
  <el-dialog
    v-model="dialogVisible"
    :title="dialogTitle"
    width="600px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="approval-dialog">
      <!-- 计划基本信息 -->
      <el-card class="plan-info-card">
        <template #header>
          <span>计划信息</span>
        </template>
        <div v-if="planData" class="plan-info">
          <el-descriptions :column="2" size="small">
            <el-descriptions-item label="计划名称">{{ planData.name }}</el-descriptions-item>
            <el-descriptions-item label="计划编码">{{ planData.code }}</el-descriptions-item>
            <el-descriptions-item label="班级名称">{{ planData.class_name || '-' }}</el-descriptions-item>
            <el-descriptions-item label="学生人数">{{ planData.student_count || '-' }}</el-descriptions-item>
            <el-descriptions-item label="计划日期">{{ planData.planned_date || '-' }}</el-descriptions-item>
            <el-descriptions-item label="计划时长">{{ planData.planned_duration ? planData.planned_duration + ' 分钟' : '-' }}</el-descriptions-item>
          </el-descriptions>
          
          <div v-if="planData.experiment_catalog" class="catalog-info">
            <h4>实验目录</h4>
            <div class="catalog-detail">
              <span class="catalog-name">{{ planData.experiment_catalog.name }}</span>
              <el-tag size="small" class="ml-2">{{ getSubjectLabel(planData.experiment_catalog.subject) }}</el-tag>
              <el-tag size="small" type="info" class="ml-1">{{ getGradeLabel(planData.experiment_catalog.grade) }}</el-tag>
            </div>
          </div>

          <div v-if="planData.description" class="description-info">
            <h4>计划描述</h4>
            <div class="description-text">{{ planData.description }}</div>
          </div>
        </div>
      </el-card>

      <!-- 审批表单 -->
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
        @submit.prevent
      >
        <el-form-item
          v-if="approvalType === 'approve'"
          label="审批意见"
          prop="approval_notes"
        >
          <el-input
            v-model="formData.approval_notes"
            type="textarea"
            :rows="4"
            placeholder="请输入审批意见（可选）"
          />
        </el-form-item>

        <el-form-item
          v-if="approvalType === 'reject'"
          label="拒绝原因"
          prop="rejection_reason"
        >
          <el-input
            v-model="formData.rejection_reason"
            type="textarea"
            :rows="4"
            placeholder="请输入拒绝原因"
          />
        </el-form-item>
      </el-form>

      <!-- 审批确认 -->
      <el-alert
        :title="confirmMessage"
        :type="approvalType === 'approve' ? 'success' : 'warning'"
        show-icon
        :closable="false"
        class="confirm-alert"
      />
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">取消</el-button>
        <el-button
          :type="approvalType === 'approve' ? 'success' : 'danger'"
          :loading="submitting"
          @click="handleSubmit"
        >
          {{ approvalType === 'approve' ? '审批通过' : '审批拒绝' }}
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { experimentPlanApi, getSubjectLabel, getGradeLabel } from '@/api/experimentPlan'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  planData: {
    type: Object,
    default: null
  },
  approvalType: {
    type: String,
    default: 'approve', // approve | reject
    validator: (value) => ['approve', 'reject'].includes(value)
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const formRef = ref()
const submitting = ref(false)

// 表单数据
const formData = reactive({
  approval_notes: '',
  rejection_reason: ''
})

// 表单验证规则
const formRules = computed(() => {
  const rules = {}
  
  if (props.approvalType === 'reject') {
    rules.rejection_reason = [
      { required: true, message: '请输入拒绝原因', trigger: 'blur' },
      { min: 5, max: 1000, message: '长度在 5 到 1000 个字符', trigger: 'blur' }
    ]
  }
  
  if (props.approvalType === 'approve') {
    rules.approval_notes = [
      { max: 1000, message: '长度不能超过 1000 个字符', trigger: 'blur' }
    ]
  }
  
  return rules
})

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const dialogTitle = computed(() => {
  return props.approvalType === 'approve' ? '审批通过' : '审批拒绝'
})

const confirmMessage = computed(() => {
  if (props.approvalType === 'approve') {
    return '确认审批通过该实验计划？通过后计划将可以开始执行。'
  } else {
    return '确认拒绝该实验计划？拒绝后计划将退回给创建者修改。'
  }
})

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal) {
    resetForm()
  }
})

watch(() => props.approvalType, () => {
  resetForm()
})

// 方法
const resetForm = () => {
  formData.approval_notes = ''
  formData.rejection_reason = ''
  nextTick(() => {
    formRef.value?.clearValidate()
  })
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    
    const actionText = props.approvalType === 'approve' ? '审批通过' : '审批拒绝'
    
    await ElMessageBox.confirm(
      `确定要${actionText}该实验计划吗？`,
      '确认操作',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: props.approvalType === 'approve' ? 'success' : 'warning'
      }
    )
    
    submitting.value = true
    
    let response
    if (props.approvalType === 'approve') {
      response = await experimentPlanApi.approve(props.planData.id, {
        approval_notes: formData.approval_notes
      })
    } else {
      response = await experimentPlanApi.reject(props.planData.id, {
        rejection_reason: formData.rejection_reason
      })
    }
    
    if (response.data.success) {
      ElMessage.success(`${actionText}成功`)
      emit('success')
    }
  } catch (error) {
    if (error === 'cancel') {
      return
    }
    if (error.errors) {
      // 表单验证错误
      return
    }
    const actionText = props.approvalType === 'approve' ? '审批通过' : '审批拒绝'
    ElMessage.error(`${actionText}失败：` + error.message)
  } finally {
    submitting.value = false
  }
}

const handleClose = () => {
  emit('update:visible', false)
}
</script>

<style scoped>
.approval-dialog {
  padding: 10px 0;
}

.plan-info-card {
  margin-bottom: 20px;
}

.plan-info {
  padding: 10px 0;
}

.catalog-info,
.description-info {
  margin-top: 15px;
}

.catalog-info h4,
.description-info h4 {
  margin: 0 0 8px 0;
  color: #303133;
  font-size: 14px;
  font-weight: 500;
}

.catalog-detail {
  display: flex;
  align-items: center;
}

.catalog-name {
  font-weight: 500;
}

.ml-2 {
  margin-left: 8px;
}

.ml-1 {
  margin-left: 4px;
}

.description-text {
  color: #606266;
  line-height: 1.6;
  white-space: pre-wrap;
  background-color: #fafafa;
  padding: 10px;
  border-radius: 4px;
  border: 1px solid #ebeef5;
}

.confirm-alert {
  margin-top: 20px;
}

.dialog-footer {
  text-align: right;
}
</style>
