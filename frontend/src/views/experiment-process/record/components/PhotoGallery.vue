<template>
  <div class="photo-gallery">
    <!-- 照片类型筛选 -->
    <div class="photo-filter">
      <el-radio-group v-model="selectedType" @change="filterPhotos">
        <el-radio-button label="">全部</el-radio-button>
        <el-radio-button
          v-for="option in photoTypeOptions"
          :key="option.value"
          :label="option.value"
        >
          {{ option.label }}
        </el-radio-button>
      </el-radio-group>
    </div>

    <!-- 照片网格 -->
    <div class="photo-grid">
      <div
        v-for="photo in filteredPhotos"
        :key="photo.id"
        class="photo-item"
        @click="previewPhoto(photo)"
      >
        <div class="photo-wrapper">
          <img
            :src="photo.thumbnail_url || photo.file_url"
            :alt="photo.description || '实验照片'"
            class="photo-image"
            @error="handleImageError"
          />
          
          <!-- 照片信息覆盖层 -->
          <div class="photo-overlay">
            <div class="photo-info">
              <div class="photo-type">
                <el-icon :color="getPhotoTypeColor(photo.photo_type)">
                  <component :is="getPhotoTypeIcon(photo.photo_type)" />
                </el-icon>
                <span>{{ getPhotoTypeLabel(photo.photo_type) }}</span>
              </div>
              
              <div class="photo-meta">
                <div class="upload-method">
                  <el-icon>
                    <Monitor v-if="photo.upload_method === 'web'" />
                    <Iphone v-else />
                  </el-icon>
                  <span>{{ photo.upload_method === 'web' ? '网页' : '移动端' }}</span>
                </div>
                
                <div class="file-size">
                  {{ formatFileSize(photo.file_size) }}
                </div>
              </div>
            </div>
            
            <!-- 合规状态 -->
            <div class="compliance-status">
              <el-tag
                :type="getComplianceStatusType(photo.compliance_status)"
                size="small"
              >
                {{ getComplianceStatusLabel(photo.compliance_status) }}
              </el-tag>
            </div>
          </div>
        </div>
        
        <!-- 照片描述 -->
        <div v-if="photo.description" class="photo-description">
          {{ photo.description }}
        </div>
        
        <!-- 上传时间 -->
        <div class="photo-time">
          {{ formatDateTime(photo.created_at) }}
        </div>
      </div>
    </div>

    <!-- 空状态 -->
    <div v-if="filteredPhotos.length === 0" class="empty-state">
      <el-empty description="暂无照片" />
    </div>

    <!-- 照片预览对话框 -->
    <el-dialog
      v-model="previewVisible"
      :title="currentPhoto?.description || '照片预览'"
      width="800px"
      append-to-body
    >
      <div v-if="currentPhoto" class="photo-preview">
        <div class="preview-image-container">
          <img
            :src="currentPhoto.file_url"
            :alt="currentPhoto.description || '实验照片'"
            class="preview-image"
          />
        </div>
        
        <div class="preview-info">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="照片类型">
              <div class="type-info">
                <el-icon :color="getPhotoTypeColor(currentPhoto.photo_type)">
                  <component :is="getPhotoTypeIcon(currentPhoto.photo_type)" />
                </el-icon>
                <span>{{ getPhotoTypeLabel(currentPhoto.photo_type) }}</span>
              </div>
            </el-descriptions-item>
            <el-descriptions-item label="上传方式">
              <div class="upload-info">
                <el-icon>
                  <Monitor v-if="currentPhoto.upload_method === 'web'" />
                  <Iphone v-else />
                </el-icon>
                <span>{{ currentPhoto.upload_method === 'web' ? '网页上传' : '移动端上传' }}</span>
              </div>
            </el-descriptions-item>
            <el-descriptions-item label="文件大小">
              {{ formatFileSize(currentPhoto.file_size) }}
            </el-descriptions-item>
            <el-descriptions-item label="图片尺寸">
              {{ currentPhoto.width && currentPhoto.height ? `${currentPhoto.width} × ${currentPhoto.height}` : '-' }}
            </el-descriptions-item>
            <el-descriptions-item label="合规状态">
              <el-tag :type="getComplianceStatusType(currentPhoto.compliance_status)">
                {{ getComplianceStatusLabel(currentPhoto.compliance_status) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="上传时间">
              {{ formatDateTime(currentPhoto.created_at) }}
            </el-descriptions-item>
            <el-descriptions-item v-if="currentPhoto.description" label="照片描述" :span="2">
              {{ currentPhoto.description }}
            </el-descriptions-item>
          </el-descriptions>
          
          <!-- 位置信息 -->
          <div v-if="currentPhoto.location_info" class="location-info">
            <h4>位置信息</h4>
            <p>
              纬度：{{ currentPhoto.location_info.latitude }}，
              经度：{{ currentPhoto.location_info.longitude }}
            </p>
          </div>
          
          <!-- AI分析结果 -->
          <div v-if="currentPhoto.ai_analysis_result" class="ai-analysis">
            <h4>AI分析结果</h4>
            <el-descriptions :column="1" size="small">
              <el-descriptions-item label="模糊检测">
                <el-tag :type="currentPhoto.ai_analysis_result.blur_detection?.is_blurry ? 'danger' : 'success'">
                  {{ currentPhoto.ai_analysis_result.blur_detection?.is_blurry ? '模糊' : '清晰' }}
                </el-tag>
                <span class="score">
                  (评分: {{ currentPhoto.ai_analysis_result.blur_detection?.blur_score || 0 }})
                </span>
              </el-descriptions-item>
              <el-descriptions-item label="内容检测">
                <el-tag :type="currentPhoto.ai_analysis_result.content_detection?.has_experiment_content ? 'success' : 'warning'">
                  {{ currentPhoto.ai_analysis_result.content_detection?.has_experiment_content ? '包含实验内容' : '无实验内容' }}
                </el-tag>
                <span class="score">
                  (置信度: {{ Math.round((currentPhoto.ai_analysis_result.content_detection?.confidence || 0) * 100) }}%)
                </span>
              </el-descriptions-item>
              <el-descriptions-item label="安全检查">
                <el-tag :type="currentPhoto.ai_analysis_result.safety_check?.is_safe ? 'success' : 'danger'">
                  {{ currentPhoto.ai_analysis_result.safety_check?.is_safe ? '安全' : '存在安全问题' }}
                </el-tag>
              </el-descriptions-item>
            </el-descriptions>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Monitor, Iphone } from '@element-plus/icons-vue'
import { 
  photoTypeOptions,
  getPhotoTypeLabel,
  getPhotoTypeIcon,
  getPhotoTypeColor,
  getComplianceStatusType,
  getComplianceStatusLabel,
  formatFileSize
} from '@/api/experimentRecord'

// Props
const props = defineProps({
  photos: {
    type: Array,
    default: () => []
  }
})

// 响应式数据
const selectedType = ref('')
const previewVisible = ref(false)
const currentPhoto = ref(null)

// 计算属性
const filteredPhotos = computed(() => {
  if (!selectedType.value) {
    return props.photos
  }
  return props.photos.filter(photo => photo.photo_type === selectedType.value)
})

// 方法
const filterPhotos = () => {
  // 筛选逻辑已在计算属性中处理
}

const previewPhoto = (photo) => {
  currentPhoto.value = photo
  previewVisible.value = true
}

const handleImageError = (event) => {
  // 图片加载失败时的处理
  event.target.src = '/placeholder-image.png' // 可以设置一个占位图
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 初始化
onMounted(() => {
  // 组件挂载后的初始化逻辑
})
</script>

<style scoped>
.photo-gallery {
  padding: 10px 0;
}

.photo-filter {
  margin-bottom: 20px;
  text-align: center;
}

.photo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.photo-item {
  cursor: pointer;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}

.photo-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.photo-wrapper {
  position: relative;
  width: 100%;
  height: 150px;
  overflow: hidden;
}

.photo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.photo-item:hover .photo-image {
  transform: scale(1.05);
}

.photo-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, transparent 50%, rgba(0, 0, 0, 0.7) 100%);
  opacity: 0;
  transition: opacity 0.3s;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 8px;
}

.photo-item:hover .photo-overlay {
  opacity: 1;
}

.photo-info {
  color: white;
}

.photo-type {
  display: flex;
  align-items: center;
  margin-bottom: 4px;
  font-size: 12px;
}

.photo-type span {
  margin-left: 4px;
}

.photo-meta {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
}

.upload-method {
  display: flex;
  align-items: center;
}

.upload-method span {
  margin-left: 2px;
}

.compliance-status {
  align-self: flex-end;
}

.photo-description {
  padding: 8px;
  font-size: 12px;
  color: #606266;
  line-height: 1.4;
  max-height: 40px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.photo-time {
  padding: 0 8px 8px;
  font-size: 11px;
  color: #909399;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
}

.photo-preview {
  max-height: 70vh;
  overflow-y: auto;
}

.preview-image-container {
  text-align: center;
  margin-bottom: 20px;
}

.preview-image {
  max-width: 100%;
  max-height: 400px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.preview-info {
  padding: 10px 0;
}

.type-info,
.upload-info {
  display: flex;
  align-items: center;
}

.type-info span,
.upload-info span {
  margin-left: 8px;
}

.location-info,
.ai-analysis {
  margin-top: 20px;
  padding: 15px;
  background-color: #fafafa;
  border-radius: 4px;
}

.location-info h4,
.ai-analysis h4 {
  margin: 0 0 10px 0;
  color: #303133;
  font-size: 14px;
}

.location-info p {
  margin: 0;
  color: #606266;
  font-size: 14px;
}

.score {
  margin-left: 8px;
  font-size: 12px;
  color: #909399;
}
</style>
