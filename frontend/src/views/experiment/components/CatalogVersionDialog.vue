<template>
  <el-dialog
    v-model="dialogVisible"
    title="版本管理"
    width="1000px"
    :close-on-click-modal="false"
  >
    <div v-if="catalog">
      <!-- 目录信息 -->
      <el-card class="catalog-info" shadow="never">
        <h3>{{ catalog.name }}</h3>
        <p>编码：{{ catalog.code }} | 学科：{{ catalog.subject_name }} | 年级：{{ catalog.grade_name }}</p>
      </el-card>

      <!-- 版本列表 -->
      <el-card class="version-list" shadow="never">
        <template #header>
          <div class="card-header">
            <span>版本历史</span>
            <el-button type="primary" size="small" @click="loadVersions">
              <el-icon><Refresh /></el-icon>
              刷新
            </el-button>
          </div>
        </template>

        <el-table v-loading="loading" :data="versions" stripe>
          <el-table-column prop="version" label="版本号" width="100" />
          <el-table-column prop="change_type_name" label="变更类型" width="100">
            <template #default="{ row }">
              <el-tag :type="getChangeTypeTag(row.change_type)">
                {{ row.change_type_name }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="version_description" label="版本描述" min-width="200" />
          <el-table-column prop="changes_summary" label="变更摘要" min-width="250" />
          <el-table-column prop="change_reason" label="变更原因" min-width="150" />
          <el-table-column prop="creator.name" label="操作人" width="100" />
          <el-table-column prop="created_at" label="创建时间" width="160">
            <template #default="{ row }">
              {{ formatDateTime(row.created_at) }}
            </template>
          </el-table-column>
          <el-table-column label="操作" width="200" fixed="right">
            <template #default="{ row, $index }">
              <el-button type="primary" link @click="handleViewVersion(row)">
                <el-icon><View /></el-icon>
                查看
              </el-button>
              <el-button 
                v-if="$index > 0" 
                type="primary" 
                link 
                @click="handleCompare(row, $index)"
              >
                <el-icon><DocumentCopy /></el-icon>
                对比
              </el-button>
              <el-button 
                v-if="$index > 0" 
                type="warning" 
                link 
                @click="handleRollback(row)"
              >
                <el-icon><RefreshLeft /></el-icon>
                回滚
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <!-- 分页 -->
        <div class="pagination-wrapper">
          <el-pagination
            v-model:current-page="pagination.current"
            v-model:page-size="pagination.size"
            :total="pagination.total"
            :page-sizes="[10, 20, 50]"
            layout="total, sizes, prev, pager, next"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </el-card>
    </div>

    <!-- 版本详情对话框 -->
    <el-dialog
      v-model="versionDetailVisible"
      title="版本详情"
      width="800px"
      append-to-body
    >
      <div v-if="selectedVersion">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="版本号">{{ selectedVersion.version }}</el-descriptions-item>
          <el-descriptions-item label="变更类型">{{ selectedVersion.change_type_name }}</el-descriptions-item>
          <el-descriptions-item label="操作人">{{ selectedVersion.creator?.name }}</el-descriptions-item>
          <el-descriptions-item label="创建时间">{{ formatDateTime(selectedVersion.created_at) }}</el-descriptions-item>
          <el-descriptions-item label="版本描述" :span="2">{{ selectedVersion.version_description }}</el-descriptions-item>
          <el-descriptions-item label="变更原因" :span="2">{{ selectedVersion.change_reason || '-' }}</el-descriptions-item>
        </el-descriptions>

        <div v-if="selectedVersion.changes && Object.keys(selectedVersion.changes).length > 0" style="margin-top: 20px">
          <h4>变更详情</h4>
          <el-table :data="formatChanges(selectedVersion.changes)" border>
            <el-table-column prop="field" label="字段" width="150" />
            <el-table-column prop="oldValue" label="原值" />
            <el-table-column prop="newValue" label="新值" />
          </el-table>
        </div>
      </div>
    </el-dialog>

    <!-- 版本对比对话框 -->
    <el-dialog
      v-model="compareVisible"
      title="版本对比"
      width="1000px"
      append-to-body
    >
      <div v-if="compareData">
        <el-descriptions :column="2" border style="margin-bottom: 20px">
          <el-descriptions-item label="对比版本">
            {{ compareData.from_version.version }} vs {{ compareData.to_version.version }}
          </el-descriptions-item>
          <el-descriptions-item label="时间范围">
            {{ formatDateTime(compareData.from_version.created_at) }} - {{ formatDateTime(compareData.to_version.created_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <div v-if="compareData.differences && Object.keys(compareData.differences).length > 0">
          <h4>差异详情</h4>
          <el-table :data="formatDifferences(compareData.differences)" border>
            <el-table-column prop="field" label="字段" width="150" />
            <el-table-column prop="type" label="变更类型" width="100">
              <template #default="{ row }">
                <el-tag :type="getDifferenceTypeTag(row.type)">
                  {{ getDifferenceTypeName(row.type) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="fromValue" label="原值" />
            <el-table-column prop="toValue" label="新值" />
          </el-table>
        </div>
        <div v-else>
          <el-empty description="两个版本之间没有差异" />
        </div>
      </div>
    </el-dialog>

    <!-- 回滚确认对话框 -->
    <el-dialog
      v-model="rollbackVisible"
      title="确认回滚"
      width="500px"
      append-to-body
    >
      <div v-if="rollbackVersion">
        <el-alert
          title="警告"
          type="warning"
          description="回滚操作将会覆盖当前版本的数据，此操作不可逆，请谨慎操作！"
          show-icon
          :closable="false"
          style="margin-bottom: 20px"
        />
        
        <p>确定要回滚到版本 <strong>{{ rollbackVersion.version }}</strong> 吗？</p>
        
        <el-form>
          <el-form-item label="回滚原因">
            <el-input
              v-model="rollbackReason"
              type="textarea"
              :rows="3"
              placeholder="请输入回滚原因（可选）"
            />
          </el-form-item>
        </el-form>
      </div>

      <template #footer>
        <el-button @click="rollbackVisible = false">取消</el-button>
        <el-button type="danger" @click="confirmRollback" :loading="rollbackLoading">
          确认回滚
        </el-button>
      </template>
    </el-dialog>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Refresh, View, DocumentCopy, RefreshLeft } from '@element-plus/icons-vue'
import { experimentCatalogApi } from '../../../api/experiment'
import { formatDateTime } from '../../../utils/date'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  catalog: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['update:visible'])

// 响应式数据
const loading = ref(false)
const versions = ref([])
const selectedVersion = ref(null)
const compareData = ref(null)
const rollbackVersion = ref(null)
const rollbackReason = ref('')
const rollbackLoading = ref(false)

const versionDetailVisible = ref(false)
const compareVisible = ref(false)
const rollbackVisible = ref(false)

// 分页数据
const pagination = reactive({
  current: 1,
  size: 10,
  total: 0
})

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

// 监听器
watch(() => props.visible, (visible) => {
  if (visible && props.catalog) {
    loadVersions()
  }
})

// 方法
const loadVersions = async () => {
  if (!props.catalog) return
  
  loading.value = true
  try {
    const params = {
      page: pagination.current,
      per_page: pagination.size
    }
    
    const response = await experimentCatalogApi.getVersions(props.catalog.id, params)
    if (response.data.success) {
      versions.value = response.data.data.versions.data
      pagination.total = response.data.data.versions.total
    }
  } catch (error) {
    ElMessage.error('获取版本历史失败：' + error.message)
  } finally {
    loading.value = false
  }
}

const handleViewVersion = async (version) => {
  try {
    const response = await experimentCatalogApi.getVersionDetail(props.catalog.id, version.id)
    if (response.data.success) {
      selectedVersion.value = response.data.data
      versionDetailVisible.value = true
    }
  } catch (error) {
    ElMessage.error('获取版本详情失败：' + error.message)
  }
}

const handleCompare = async (currentVersion, index) => {
  const previousVersion = versions.value[index + 1]
  if (!previousVersion) {
    ElMessage.warning('没有可对比的版本')
    return
  }

  try {
    const response = await experimentCatalogApi.compareVersions(props.catalog.id, {
      from_version: previousVersion.id,
      to_version: currentVersion.id
    })
    
    if (response.data.success) {
      compareData.value = response.data.data
      compareVisible.value = true
    }
  } catch (error) {
    ElMessage.error('版本对比失败：' + error.message)
  }
}

const handleRollback = (version) => {
  rollbackVersion.value = version
  rollbackReason.value = ''
  rollbackVisible.value = true
}

const confirmRollback = async () => {
  try {
    rollbackLoading.value = true
    
    const response = await experimentCatalogApi.rollbackVersion(
      props.catalog.id,
      rollbackVersion.value.id,
      { reason: rollbackReason.value }
    )
    
    if (response.data.success) {
      ElMessage.success('版本回滚成功')
      rollbackVisible.value = false
      loadVersions()
      emit('update:visible', false) // 关闭版本管理对话框，让父组件刷新数据
    }
  } catch (error) {
    ElMessage.error('版本回滚失败：' + error.message)
  } finally {
    rollbackLoading.value = false
  }
}

const handleSizeChange = (size) => {
  pagination.size = size
  pagination.current = 1
  loadVersions()
}

const handleCurrentChange = (current) => {
  pagination.current = current
  loadVersions()
}

// 辅助方法
const getChangeTypeTag = (type) => {
  const tagMap = {
    create: 'success',
    update: 'primary',
    delete: 'danger',
    restore: 'warning'
  }
  return tagMap[type] || 'info'
}

const getDifferenceTypeTag = (type) => {
  const tagMap = {
    added: 'success',
    removed: 'danger',
    modified: 'warning'
  }
  return tagMap[type] || 'info'
}

const getDifferenceTypeName = (type) => {
  const nameMap = {
    added: '新增',
    removed: '删除',
    modified: '修改'
  }
  return nameMap[type] || type
}

const formatChanges = (changes) => {
  return Object.keys(changes).map(field => ({
    field,
    oldValue: changes[field].old || '-',
    newValue: changes[field].new || '-'
  }))
}

const formatDifferences = (differences) => {
  return Object.keys(differences).map(field => ({
    field,
    type: differences[field].type,
    fromValue: differences[field].from || '-',
    toValue: differences[field].to || '-'
  }))
}
</script>

<style scoped>
.catalog-info {
  margin-bottom: 20px;
}

.catalog-info h3 {
  margin: 0 0 8px 0;
  color: #303133;
}

.catalog-info p {
  margin: 0;
  color: #909399;
  font-size: 14px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-wrapper {
  margin-top: 20px;
  text-align: right;
}
</style>
