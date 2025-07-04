<template>
  <div class="action-confirm">
    <!-- 确认对话框 -->
    <el-dialog
      v-model="visible"
      :title="title"
      :width="width"
      :close-on-click-modal="false"
      :close-on-press-escape="false"
      :show-close="false"
      center
    >
      <!-- 图标和消息 -->
      <div class="confirm-content">
        <div class="confirm-icon" :class="iconClass">
          <el-icon :size="48">
            <component :is="iconComponent" />
          </el-icon>
        </div>
        
        <div class="confirm-message">
          <h3>{{ message }}</h3>
          <p v-if="description" class="description">{{ description }}</p>
          
          <!-- 详细信息 -->
          <div v-if="details && details.length > 0" class="details">
            <el-collapse v-model="detailsVisible">
              <el-collapse-item title="查看详细信息" name="details">
                <ul>
                  <li v-for="(detail, index) in details" :key="index">
                    {{ detail }}
                  </li>
                </ul>
              </el-collapse-item>
            </el-collapse>
          </div>
          
          <!-- 风险提示 -->
          <div v-if="risks && risks.length > 0" class="risks">
            <el-alert
              title="风险提示"
              type="warning"
              :closable="false"
              show-icon
            >
              <ul>
                <li v-for="(risk, index) in risks" :key="index">
                  {{ risk }}
                </li>
              </ul>
            </el-alert>
          </div>
          
          <!-- 输入确认 -->
          <div v-if="requireInput" class="input-confirm">
            <p class="input-hint">{{ inputHint }}</p>
            <el-input
              v-model="inputValue"
              :placeholder="inputPlaceholder"
              @keyup.enter="handleConfirm"
            />
          </div>
          
          <!-- 倒计时 -->
          <div v-if="countdown > 0" class="countdown">
            <el-progress
              :percentage="countdownPercentage"
              :stroke-width="6"
              :show-text="false"
              status="warning"
            />
            <p>{{ countdown }}秒后可以操作</p>
          </div>
        </div>
      </div>
      
      <!-- 操作按钮 -->
      <template #footer>
        <div class="confirm-actions">
          <el-button @click="handleCancel" :disabled="loading">
            {{ cancelText }}
          </el-button>
          <el-button
            :type="confirmType"
            @click="handleConfirm"
            :loading="loading"
            :disabled="!canConfirm"
          >
            {{ confirmText }}
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { 
  WarningFilled, 
  QuestionFilled, 
  InfoFilled, 
  CircleCheckFilled,
  Delete,
  Edit,
  Refresh
} from '@element-plus/icons-vue'

const props = defineProps({
  // 显示状态
  modelValue: {
    type: Boolean,
    default: false
  },
  // 确认类型
  type: {
    type: String,
    default: 'warning', // warning, danger, info, success
    validator: (value) => ['warning', 'danger', 'info', 'success'].includes(value)
  },
  // 操作类型
  action: {
    type: String,
    default: 'delete', // delete, edit, refresh, custom
    validator: (value) => ['delete', 'edit', 'refresh', 'custom'].includes(value)
  },
  // 标题
  title: {
    type: String,
    default: ''
  },
  // 主要消息
  message: {
    type: String,
    required: true
  },
  // 描述信息
  description: {
    type: String,
    default: ''
  },
  // 详细信息列表
  details: {
    type: Array,
    default: () => []
  },
  // 风险提示列表
  risks: {
    type: Array,
    default: () => []
  },
  // 是否需要输入确认
  requireInput: {
    type: Boolean,
    default: false
  },
  // 输入提示
  inputHint: {
    type: String,
    default: '请输入确认文本'
  },
  // 输入占位符
  inputPlaceholder: {
    type: String,
    default: ''
  },
  // 期望的输入值
  expectedInput: {
    type: String,
    default: ''
  },
  // 倒计时秒数
  countdownSeconds: {
    type: Number,
    default: 0
  },
  // 确认按钮文本
  confirmText: {
    type: String,
    default: '确认'
  },
  // 取消按钮文本
  cancelText: {
    type: String,
    default: '取消'
  },
  // 对话框宽度
  width: {
    type: String,
    default: '500px'
  },
  // 加载状态
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

// 响应式数据
const visible = ref(false)
const inputValue = ref('')
const countdown = ref(0)
const countdownTimer = ref(null)
const detailsVisible = ref([])

// 计算属性
const iconComponent = computed(() => {
  const icons = {
    warning: WarningFilled,
    danger: WarningFilled,
    info: InfoFilled,
    success: CircleCheckFilled
  }
  return icons[props.type] || WarningFilled
})

const iconClass = computed(() => {
  return `icon-${props.type}`
})

const confirmType = computed(() => {
  const types = {
    warning: 'warning',
    danger: 'danger',
    info: 'primary',
    success: 'success'
  }
  return types[props.type] || 'primary'
})

const computedTitle = computed(() => {
  if (props.title) return props.title
  
  const titles = {
    delete: '确认删除',
    edit: '确认修改',
    refresh: '确认刷新',
    custom: '确认操作'
  }
  
  return titles[props.action] || '确认操作'
})

const canConfirm = computed(() => {
  // 倒计时未结束
  if (countdown.value > 0) return false
  
  // 需要输入确认且输入不匹配
  if (props.requireInput && props.expectedInput) {
    return inputValue.value === props.expectedInput
  }
  
  return true
})

const countdownPercentage = computed(() => {
  if (props.countdownSeconds === 0) return 100
  return ((props.countdownSeconds - countdown.value) / props.countdownSeconds) * 100
})

// 方法
const handleConfirm = () => {
  if (!canConfirm.value) return
  
  emit('confirm', {
    inputValue: inputValue.value,
    action: props.action
  })
}

const handleCancel = () => {
  emit('cancel')
  visible.value = false
}

const startCountdown = () => {
  if (props.countdownSeconds <= 0) return
  
  countdown.value = props.countdownSeconds
  
  countdownTimer.value = setInterval(() => {
    countdown.value--
    
    if (countdown.value <= 0) {
      clearInterval(countdownTimer.value)
      countdownTimer.value = null
    }
  }, 1000)
}

const stopCountdown = () => {
  if (countdownTimer.value) {
    clearInterval(countdownTimer.value)
    countdownTimer.value = null
  }
  countdown.value = 0
}

const resetForm = () => {
  inputValue.value = ''
  detailsVisible.value = []
  stopCountdown()
}

// 监听器
watch(() => props.modelValue, (newValue) => {
  visible.value = newValue
  
  if (newValue) {
    resetForm()
    startCountdown()
  } else {
    stopCountdown()
  }
})

watch(visible, (newValue) => {
  emit('update:modelValue', newValue)
})

// 生命周期
onUnmounted(() => {
  stopCountdown()
})
</script>

<style scoped>
.confirm-content {
  display: flex;
  align-items: flex-start;
  gap: 20px;
  padding: 20px 0;
}

.confirm-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 50%;
}

.icon-warning {
  background-color: #fdf6ec;
  color: #e6a23c;
}

.icon-danger {
  background-color: #fef0f0;
  color: #f56c6c;
}

.icon-info {
  background-color: #ecf5ff;
  color: #409eff;
}

.icon-success {
  background-color: #f0f9ff;
  color: #67c23a;
}

.confirm-message {
  flex: 1;
}

.confirm-message h3 {
  margin: 0 0 8px 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
}

.description {
  margin: 0 0 16px 0;
  color: #606266;
  line-height: 1.6;
}

.details {
  margin: 16px 0;
}

.details ul {
  margin: 8px 0 0 0;
  padding-left: 20px;
}

.details li {
  margin: 4px 0;
  color: #606266;
  font-size: 14px;
}

.risks {
  margin: 16px 0;
}

.risks ul {
  margin: 8px 0 0 0;
  padding-left: 20px;
}

.risks li {
  margin: 4px 0;
  font-size: 14px;
}

.input-confirm {
  margin: 16px 0;
}

.input-hint {
  margin: 0 0 8px 0;
  color: #606266;
  font-size: 14px;
}

.countdown {
  margin: 16px 0;
  text-align: center;
}

.countdown p {
  margin: 8px 0 0 0;
  color: #e6a23c;
  font-size: 14px;
}

.confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .confirm-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 16px;
  }
  
  .confirm-actions {
    flex-direction: column;
  }
  
  .confirm-actions .el-button {
    width: 100%;
  }
}
</style>
