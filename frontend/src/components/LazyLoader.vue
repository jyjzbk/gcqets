<template>
  <div class="lazy-loader">
    <!-- 加载状态 -->
    <div v-if="loading" class="loading-container">
      <el-skeleton :rows="skeletonRows" animated />
      <div class="loading-text">{{ loadingText }}</div>
    </div>
    
    <!-- 错误状态 -->
    <div v-else-if="error" class="error-container">
      <el-result
        icon="error"
        :title="errorTitle"
        :sub-title="errorMessage"
      >
        <template #extra>
          <el-button type="primary" @click="retry">重试</el-button>
          <el-button @click="$emit('cancel')">取消</el-button>
        </template>
      </el-result>
    </div>
    
    <!-- 成功加载内容 -->
    <div v-else class="content-container">
      <slot :data="data" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { ElMessage } from 'element-plus'

const props = defineProps({
  // 加载函数
  loadFunction: {
    type: Function,
    required: true
  },
  // 加载参数
  loadParams: {
    type: Object,
    default: () => ({})
  },
  // 骨架屏行数
  skeletonRows: {
    type: Number,
    default: 5
  },
  // 加载文本
  loadingText: {
    type: String,
    default: '正在加载...'
  },
  // 错误标题
  errorTitle: {
    type: String,
    default: '加载失败'
  },
  // 自动加载
  autoLoad: {
    type: Boolean,
    default: true
  },
  // 缓存结果
  cache: {
    type: Boolean,
    default: false
  },
  // 缓存时间（毫秒）
  cacheTime: {
    type: Number,
    default: 5 * 60 * 1000 // 5分钟
  }
})

const emit = defineEmits(['loaded', 'error', 'cancel'])

// 响应式数据
const loading = ref(false)
const error = ref(false)
const data = ref(null)
const errorMessage = ref('')
const cacheData = ref(new Map())

// 加载数据
const loadData = async () => {
  loading.value = true
  error.value = false
  errorMessage.value = ''
  
  try {
    // 检查缓存
    if (props.cache) {
      const cacheKey = JSON.stringify(props.loadParams)
      const cached = cacheData.value.get(cacheKey)
      
      if (cached && (Date.now() - cached.timestamp) < props.cacheTime) {
        data.value = cached.data
        loading.value = false
        emit('loaded', data.value)
        return
      }
    }
    
    // 调用加载函数
    const result = await props.loadFunction(props.loadParams)
    
    // 处理结果
    if (result && result.data) {
      if (result.data.success) {
        data.value = result.data.data
        
        // 缓存结果
        if (props.cache) {
          const cacheKey = JSON.stringify(props.loadParams)
          cacheData.value.set(cacheKey, {
            data: data.value,
            timestamp: Date.now()
          })
        }
        
        emit('loaded', data.value)
      } else {
        throw new Error(result.data.message || '数据加载失败')
      }
    } else {
      data.value = result
      emit('loaded', data.value)
    }
  } catch (err) {
    error.value = true
    errorMessage.value = err.message || '网络错误，请稍后重试'
    emit('error', err)
    
    // 显示错误消息
    ElMessage.error(errorMessage.value)
  } finally {
    loading.value = false
  }
}

// 重试
const retry = () => {
  loadData()
}

// 清除缓存
const clearCache = () => {
  cacheData.value.clear()
}

// 监听参数变化
watch(() => props.loadParams, () => {
  if (props.autoLoad) {
    loadData()
  }
}, { deep: true })

// 生命周期
onMounted(() => {
  if (props.autoLoad) {
    loadData()
  }
})

// 暴露方法
defineExpose({
  loadData,
  retry,
  clearCache
})
</script>

<style scoped>
.lazy-loader {
  width: 100%;
  min-height: 200px;
}

.loading-container {
  padding: 20px;
  text-align: center;
}

.loading-text {
  margin-top: 15px;
  color: #909399;
  font-size: 14px;
}

.error-container {
  padding: 20px;
}

.content-container {
  width: 100%;
}

/* 骨架屏样式优化 */
:deep(.el-skeleton__item) {
  background: linear-gradient(90deg, #f2f2f2 25%, #e6e6e6 50%, #f2f2f2 75%);
  background-size: 400% 100%;
  animation: loading 1.4s ease infinite;
}

@keyframes loading {
  0% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* 响应式设计 */
@media (max-width: 768px) {
  .lazy-loader {
    min-height: 150px;
  }
  
  .loading-container,
  .error-container {
    padding: 15px;
  }
}
</style>
