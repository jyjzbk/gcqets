<template>
  <div class="user-guide">
    <!-- 引导遮罩 -->
    <div v-if="showGuide" class="guide-overlay" @click="skipGuide">
      <!-- 高亮区域 -->
      <div 
        class="highlight-area"
        :style="highlightStyle"
      ></div>
      
      <!-- 引导卡片 -->
      <div 
        class="guide-card"
        :style="cardStyle"
      >
        <div class="guide-header">
          <h3>{{ currentStep.title }}</h3>
          <el-button 
            type="text" 
            @click="skipGuide"
            class="skip-btn"
          >
            跳过引导
          </el-button>
        </div>
        
        <div class="guide-content">
          <p>{{ currentStep.content }}</p>
          
          <!-- 图片说明 -->
          <div v-if="currentStep.image" class="guide-image">
            <img :src="currentStep.image" :alt="currentStep.title" />
          </div>
          
          <!-- 操作提示 -->
          <div v-if="currentStep.action" class="guide-action">
            <el-tag type="info">{{ currentStep.action }}</el-tag>
          </div>
        </div>
        
        <div class="guide-footer">
          <div class="step-indicator">
            <span class="current-step">{{ currentStepIndex + 1 }}</span>
            <span class="total-steps">/ {{ steps.length }}</span>
          </div>
          
          <div class="guide-buttons">
            <el-button 
              v-if="currentStepIndex > 0"
              @click="previousStep"
              size="small"
            >
              上一步
            </el-button>
            <el-button 
              v-if="currentStepIndex < steps.length - 1"
              type="primary"
              @click="nextStep"
              size="small"
            >
              下一步
            </el-button>
            <el-button 
              v-else
              type="success"
              @click="finishGuide"
              size="small"
            >
              完成
            </el-button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 引导触发按钮 -->
    <el-button 
      v-if="!showGuide && showTrigger"
      class="guide-trigger"
      type="primary"
      circle
      @click="startGuide"
    >
      <el-icon><QuestionFilled /></el-icon>
    </el-button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { QuestionFilled } from '@element-plus/icons-vue'

const props = defineProps({
  // 引导步骤
  steps: {
    type: Array,
    required: true,
    validator: (steps) => {
      return steps.every(step => 
        step.title && 
        step.content && 
        step.target
      )
    }
  },
  // 是否显示触发按钮
  showTrigger: {
    type: Boolean,
    default: true
  },
  // 自动开始
  autoStart: {
    type: Boolean,
    default: false
  },
  // 引导键（用于记录用户是否已看过）
  guideKey: {
    type: String,
    default: 'default'
  }
})

const emit = defineEmits(['start', 'finish', 'skip', 'step-change'])

// 响应式数据
const showGuide = ref(false)
const currentStepIndex = ref(0)

// 计算属性
const currentStep = computed(() => {
  return props.steps[currentStepIndex.value] || {}
})

const highlightStyle = computed(() => {
  const target = document.querySelector(currentStep.value.target)
  if (!target) return {}
  
  const rect = target.getBoundingClientRect()
  const padding = 8
  
  return {
    left: `${rect.left - padding}px`,
    top: `${rect.top - padding}px`,
    width: `${rect.width + padding * 2}px`,
    height: `${rect.height + padding * 2}px`
  }
})

const cardStyle = computed(() => {
  const target = document.querySelector(currentStep.value.target)
  if (!target) return {}
  
  const rect = target.getBoundingClientRect()
  const cardWidth = 320
  const cardHeight = 200
  const margin = 20
  
  let left = rect.right + margin
  let top = rect.top
  
  // 检查是否超出右边界
  if (left + cardWidth > window.innerWidth) {
    left = rect.left - cardWidth - margin
  }
  
  // 检查是否超出下边界
  if (top + cardHeight > window.innerHeight) {
    top = window.innerHeight - cardHeight - margin
  }
  
  // 检查是否超出上边界
  if (top < margin) {
    top = margin
  }
  
  // 检查是否超出左边界
  if (left < margin) {
    left = margin
  }
  
  return {
    left: `${left}px`,
    top: `${top}px`
  }
})

// 方法
const startGuide = () => {
  showGuide.value = true
  currentStepIndex.value = 0
  emit('start')
  
  // 滚动到目标元素
  scrollToTarget()
}

const nextStep = () => {
  if (currentStepIndex.value < props.steps.length - 1) {
    currentStepIndex.value++
    emit('step-change', currentStepIndex.value)
    scrollToTarget()
  }
}

const previousStep = () => {
  if (currentStepIndex.value > 0) {
    currentStepIndex.value--
    emit('step-change', currentStepIndex.value)
    scrollToTarget()
  }
}

const finishGuide = () => {
  showGuide.value = false
  emit('finish')
  
  // 记录用户已完成引导
  localStorage.setItem(`guide_completed_${props.guideKey}`, 'true')
}

const skipGuide = () => {
  showGuide.value = false
  emit('skip')
  
  // 记录用户已跳过引导
  localStorage.setItem(`guide_skipped_${props.guideKey}`, 'true')
}

const scrollToTarget = () => {
  const target = document.querySelector(currentStep.value.target)
  if (target) {
    target.scrollIntoView({
      behavior: 'smooth',
      block: 'center'
    })
  }
}

const checkShouldShowGuide = () => {
  const completed = localStorage.getItem(`guide_completed_${props.guideKey}`)
  const skipped = localStorage.getItem(`guide_skipped_${props.guideKey}`)
  
  return !completed && !skipped
}

// 键盘事件处理
const handleKeydown = (event) => {
  if (!showGuide.value) return
  
  switch (event.key) {
    case 'Escape':
      skipGuide()
      break
    case 'ArrowRight':
    case ' ':
      event.preventDefault()
      nextStep()
      break
    case 'ArrowLeft':
      event.preventDefault()
      previousStep()
      break
  }
}

// 生命周期
onMounted(() => {
  // 添加键盘事件监听
  document.addEventListener('keydown', handleKeydown)
  
  // 自动开始引导
  if (props.autoStart && checkShouldShowGuide()) {
    setTimeout(startGuide, 1000) // 延迟1秒开始
  }
})

onUnmounted(() => {
  // 移除键盘事件监听
  document.removeEventListener('keydown', handleKeydown)
})

// 暴露方法
defineExpose({
  startGuide,
  finishGuide,
  skipGuide,
  nextStep,
  previousStep
})
</script>

<style scoped>
.user-guide {
  position: relative;
}

.guide-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  cursor: pointer;
}

.highlight-area {
  position: absolute;
  background-color: transparent;
  border: 2px solid #409eff;
  border-radius: 4px;
  box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
  pointer-events: none;
  transition: all 0.3s ease;
}

.guide-card {
  position: absolute;
  width: 320px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  cursor: default;
  transition: all 0.3s ease;
  z-index: 10000;
}

.guide-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px 0;
}

.guide-header h3 {
  margin: 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
}

.skip-btn {
  color: #909399;
  font-size: 12px;
  padding: 0;
}

.guide-content {
  padding: 16px 20px;
}

.guide-content p {
  margin: 0 0 12px 0;
  color: #606266;
  line-height: 1.6;
  font-size: 14px;
}

.guide-image {
  margin: 12px 0;
  text-align: center;
}

.guide-image img {
  max-width: 100%;
  height: auto;
  border-radius: 4px;
  border: 1px solid #ebeef5;
}

.guide-action {
  margin-top: 12px;
}

.guide-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px 16px;
  border-top: 1px solid #ebeef5;
  margin-top: 16px;
  padding-top: 16px;
}

.step-indicator {
  font-size: 12px;
  color: #909399;
}

.current-step {
  color: #409eff;
  font-weight: 600;
}

.guide-buttons {
  display: flex;
  gap: 8px;
}

.guide-trigger {
  position: fixed;
  bottom: 80px;
  right: 30px;
  width: 50px;
  height: 50px;
  z-index: 1000;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
}

.guide-trigger:hover {
  transform: scale(1.1);
}

/* 动画效果 */
.guide-card {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 响应式设计 */
@media (max-width: 768px) {
  .guide-card {
    width: 280px;
    margin: 20px;
  }
  
  .guide-trigger {
    bottom: 60px;
    right: 20px;
    width: 45px;
    height: 45px;
  }
}
</style>
