<template>
  <el-dialog
    v-model="dialogVisible"
    title="上传实验照片"
    width="600px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="photo-upload">
      <!-- 照片类型选择 -->
      <div class="photo-type-selection">
        <h4>选择照片类型</h4>
        <el-radio-group v-model="selectedPhotoType" class="photo-type-group">
          <el-radio
            v-for="option in photoTypeOptions"
            :key="option.value"
            :label="option.value"
            class="photo-type-radio"
          >
            <div class="photo-type-content">
              <el-icon :color="option.color" size="20">
                <component :is="option.icon" />
              </el-icon>
              <span class="photo-type-label">{{ option.label }}</span>
            </div>
          </el-radio>
        </el-radio-group>
      </div>

      <!-- 文件上传区域 -->
      <div class="upload-area">
        <el-upload
          ref="uploadRef"
          :action="uploadAction"
          :headers="uploadHeaders"
          :data="uploadData"
          :before-upload="beforeUpload"
          :on-success="handleUploadSuccess"
          :on-error="handleUploadError"
          :on-progress="handleUploadProgress"
          :show-file-list="false"
          drag
          accept="image/*"
          multiple
        >
          <div class="upload-content">
            <el-icon class="upload-icon"><UploadFilled /></el-icon>
            <div class="upload-text">
              <p>将图片拖拽到此处，或<em>点击上传</em></p>
              <p class="upload-tip">支持 JPG、PNG、GIF 格式，单个文件不超过 10MB</p>
            </div>
          </div>
        </el-upload>
      </div>

      <!-- 照片描述 -->
      <div class="photo-description">
        <el-form-item label="照片描述">
          <el-input
            v-model="photoDescription"
            type="textarea"
            :rows="3"
            placeholder="请描述照片内容（可选）"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
      </div>

      <!-- 位置信息 -->
      <div class="location-info">
        <el-form-item>
          <el-checkbox v-model="enableLocation">添加位置信息</el-checkbox>
        </el-form-item>
        <div v-if="enableLocation" class="location-inputs">
          <el-row :gutter="10">
            <el-col :span="12">
              <el-input
                v-model="locationInfo.latitude"
                placeholder="纬度"
                type="number"
                step="0.000001"
              />
            </el-col>
            <el-col :span="12">
              <el-input
                v-model="locationInfo.longitude"
                placeholder="经度"
                type="number"
                step="0.000001"
              />
            </el-col>
          </el-row>
          <el-button size="small" @click="getCurrentLocation">获取当前位置</el-button>
        </div>
      </div>

      <!-- 上传进度 -->
      <div v-if="uploading" class="upload-progress">
        <el-progress :percentage="uploadProgress" :status="uploadStatus" />
        <p class="progress-text">{{ progressText }}</p>
      </div>

      <!-- 上传结果 -->
      <div v-if="uploadResults.length > 0" class="upload-results">
        <h4>上传结果</h4>
        <div class="result-list">
          <div
            v-for="(result, index) in uploadResults"
            :key="index"
            class="result-item"
            :class="{ success: result.success, error: !result.success }"
          >
            <el-icon>
              <SuccessFilled v-if="result.success" />
              <CircleCloseFilled v-else />
            </el-icon>
            <span class="result-text">{{ result.message }}</span>
          </div>
        </div>
      </div>

      <!-- 照片类型完成情况 -->
      <div class="photo-completion">
        <h4>照片完成情况</h4>
        <div class="completion-grid">
          <div
            v-for="option in photoTypeOptions"
            :key="option.value"
            class="completion-item"
            :class="{ completed: photoCompletion[option.value] > 0 }"
          >
            <el-icon :color="option.color">
              <component :is="option.icon" />
            </el-icon>
            <span class="completion-label">{{ option.label }}</span>
            <el-badge :value="photoCompletion[option.value] || 0" class="completion-badge" />
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">关闭</el-button>
        <el-button type="primary" @click="triggerUpload" :disabled="!selectedPhotoType">
          选择文件上传
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { UploadFilled, SuccessFilled, CircleCloseFilled } from '@element-plus/icons-vue'
import { 
  experimentRecordApi, 
  photoTypeOptions,
  validatePhotoFile,
  createPhotoUploadData
} from '@/api/experimentRecord'
import { useAuthStore } from '@/stores/auth'

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
const emit = defineEmits(['update:visible', 'success'])

// 状态管理
const authStore = useAuthStore()

// 响应式数据
const uploadRef = ref()
const selectedPhotoType = ref('')
const photoDescription = ref('')
const enableLocation = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const uploadStatus = ref('')
const progressText = ref('')
const uploadResults = ref([])
const photoCompletion = reactive({})

const locationInfo = reactive({
  latitude: null,
  longitude: null
})

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const uploadAction = computed(() => {
  return `/api/experiment-records/${props.recordId}/photos`
})

const uploadHeaders = computed(() => {
  return {
    'Authorization': `Bearer ${authStore.token}`
  }
})

const uploadData = computed(() => {
  const data = {
    photo_type: selectedPhotoType.value,
    upload_method: 'web'
  }
  
  if (photoDescription.value) {
    data.description = photoDescription.value
  }
  
  if (enableLocation.value && locationInfo.latitude && locationInfo.longitude) {
    data['location_info[latitude]'] = locationInfo.latitude
    data['location_info[longitude]'] = locationInfo.longitude
  }
  
  return data
})

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal) {
    resetForm()
    loadPhotoCompletion()
  }
})

// 方法
const resetForm = () => {
  selectedPhotoType.value = ''
  photoDescription.value = ''
  enableLocation.value = false
  locationInfo.latitude = null
  locationInfo.longitude = null
  uploading.value = false
  uploadProgress.value = 0
  uploadStatus.value = ''
  progressText.value = ''
  uploadResults.value = []
}

const loadPhotoCompletion = async () => {
  try {
    const response = await experimentRecordApi.getDetail(props.recordId)
    if (response.data.success && response.data.data.photos) {
      const photos = response.data.data.photos
      
      // 重置计数
      photoTypeOptions.forEach(option => {
        photoCompletion[option.value] = 0
      })
      
      // 统计各类型照片数量
      photos.forEach(photo => {
        if (photoCompletion.hasOwnProperty(photo.photo_type)) {
          photoCompletion[photo.photo_type]++
        }
      })
    }
  } catch (error) {
    console.error('获取照片完成情况失败：', error)
  }
}

const beforeUpload = (file) => {
  // 验证照片类型
  if (!selectedPhotoType.value) {
    ElMessage.error('请先选择照片类型')
    return false
  }
  
  // 验证文件
  const validation = validatePhotoFile(file)
  if (!validation.valid) {
    ElMessage.error(validation.errors.join('；'))
    return false
  }
  
  uploading.value = true
  uploadProgress.value = 0
  uploadStatus.value = ''
  progressText.value = '准备上传...'
  
  return true
}

const handleUploadProgress = (event) => {
  uploadProgress.value = Math.round(event.percent)
  progressText.value = `上传中... ${uploadProgress.value}%`
}

const handleUploadSuccess = (response, file) => {
  uploading.value = false
  
  if (response.success) {
    uploadResults.value.push({
      success: true,
      message: `${file.name} 上传成功`
    })
    
    // 更新照片完成情况
    if (photoCompletion.hasOwnProperty(selectedPhotoType.value)) {
      photoCompletion[selectedPhotoType.value]++
    }
    
    ElMessage.success('照片上传成功')
    emit('success')
  } else {
    uploadResults.value.push({
      success: false,
      message: `${file.name} 上传失败：${response.message}`
    })
    ElMessage.error(`上传失败：${response.message}`)
  }
}

const handleUploadError = (error, file) => {
  uploading.value = false
  uploadStatus.value = 'exception'
  
  uploadResults.value.push({
    success: false,
    message: `${file.name} 上传失败：网络错误`
  })
  
  ElMessage.error('上传失败：网络错误')
}

const triggerUpload = () => {
  if (!selectedPhotoType.value) {
    ElMessage.error('请先选择照片类型')
    return
  }
  
  uploadRef.value.$el.querySelector('input[type="file"]').click()
}

const getCurrentLocation = () => {
  if (!navigator.geolocation) {
    ElMessage.error('浏览器不支持地理位置获取')
    return
  }
  
  navigator.geolocation.getCurrentPosition(
    (position) => {
      locationInfo.latitude = position.coords.latitude
      locationInfo.longitude = position.coords.longitude
      ElMessage.success('位置获取成功')
    },
    (error) => {
      ElMessage.error('位置获取失败：' + error.message)
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 60000
    }
  )
}

const handleClose = () => {
  emit('update:visible', false)
}

// 初始化
onMounted(() => {
  // 设置默认照片类型为过程记录
  selectedPhotoType.value = 'process'
})
</script>

<style scoped>
.photo-upload {
  padding: 10px 0;
}

.photo-type-selection {
  margin-bottom: 20px;
}

.photo-type-selection h4 {
  margin: 0 0 10px 0;
  color: #303133;
  font-size: 14px;
}

.photo-type-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.photo-type-radio {
  margin-right: 0;
  margin-bottom: 10px;
}

.photo-type-content {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
}

.photo-type-content:hover {
  border-color: #409eff;
  background-color: #f0f9ff;
}

.photo-type-label {
  margin-left: 8px;
  font-size: 14px;
}

.upload-area {
  margin-bottom: 20px;
}

.upload-content {
  text-align: center;
  padding: 40px 20px;
}

.upload-icon {
  font-size: 48px;
  color: #c0c4cc;
  margin-bottom: 16px;
}

.upload-text p {
  margin: 8px 0;
  color: #606266;
}

.upload-tip {
  font-size: 12px;
  color: #909399;
}

.photo-description {
  margin-bottom: 20px;
}

.location-info {
  margin-bottom: 20px;
}

.location-inputs {
  margin-top: 10px;
}

.upload-progress {
  margin-bottom: 20px;
}

.progress-text {
  text-align: center;
  margin-top: 8px;
  color: #606266;
  font-size: 14px;
}

.upload-results {
  margin-bottom: 20px;
}

.upload-results h4 {
  margin: 0 0 10px 0;
  color: #303133;
  font-size: 14px;
}

.result-list {
  max-height: 150px;
  overflow-y: auto;
}

.result-item {
  display: flex;
  align-items: center;
  padding: 8px;
  margin-bottom: 4px;
  border-radius: 4px;
}

.result-item.success {
  background-color: #f0f9ff;
  color: #67c23a;
}

.result-item.error {
  background-color: #fef0f0;
  color: #f56c6c;
}

.result-text {
  margin-left: 8px;
  font-size: 14px;
}

.photo-completion {
  margin-bottom: 20px;
}

.photo-completion h4 {
  margin: 0 0 10px 0;
  color: #303133;
  font-size: 14px;
}

.completion-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
}

.completion-item {
  display: flex;
  align-items: center;
  padding: 8px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  background-color: #fafafa;
}

.completion-item.completed {
  background-color: #f0f9ff;
  border-color: #b3d8ff;
}

.completion-label {
  margin-left: 8px;
  font-size: 12px;
  flex: 1;
}

.completion-badge {
  margin-left: 4px;
}

.dialog-footer {
  text-align: right;
}
</style>
