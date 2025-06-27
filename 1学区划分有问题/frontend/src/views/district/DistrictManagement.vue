<template>
  <div class="district-management">
    <div class="page-header">
      <div class="header-content">
        <h2>学区划分管理</h2>
        <el-text type="info">
          管理学区划分，支持地理信息展示、自动划分和手动调整
        </el-text>
      </div>
      <div class="header-actions">
        <el-button @click="refreshData" :loading="loading">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <el-button @click="showOverviewDialog" type="info">
          <el-icon><DataAnalysis /></el-icon>
          概览统计
        </el-button>
        <el-button @click="showHistoryDialog" type="warning">
          <el-icon><Clock /></el-icon>
          划分历史
        </el-button>
        <el-button @click="showCreateDialog" type="primary">
          <el-icon><Plus /></el-icon>
          创建学区
        </el-button>
      </div>
    </div>

    <div class="page-content">
      <el-row :gutter="20">
        <!-- 左侧：学区列表 -->
        <el-col :span="8">
          <el-card title="学区列表">
            <template #header>
              <div class="card-header">
                <span>学区列表</span>
                <el-input
                  v-model="searchText"
                  placeholder="搜索学区"
                  size="small"
                  style="width: 200px;"
                  clearable
                >
                  <template #prefix>
                    <el-icon><Search /></el-icon>
                  </template>
                </el-input>
              </div>
            </template>

            <div class="district-list" v-loading="loading">
              <template v-for="district in filteredDistricts" :key="district?.id || Math.random()">
                <div
                  v-if="district && district.id"
                  class="district-item"
                  :class="{ active: selectedDistrict?.id === district?.id }"
                  @click="selectDistrict(district)"
                >
                <div class="district-info">
                  <div class="district-name">{{ district.name || '未命名学区' }}</div>
                  <div class="district-meta">
                    <el-tag size="small">{{ district.school_count || 0 }}所学校</el-tag>
                    <el-tag size="small" type="info">{{ district.student_count || 0 }}名学生</el-tag>
                  </div>
                </div>
                <div class="district-actions">
                  <el-button size="small" text @click.stop="editDistrict(district)">
                    <el-icon><Edit /></el-icon>
                  </el-button>
                  <el-button size="small" text type="danger" @click.stop="deleteDistrict(district)">
                    <el-icon><Delete /></el-icon>
                  </el-button>
                </div>
                </div>
              </template>

              <div v-if="filteredDistricts.length === 0" class="empty-state">
                <el-empty description="暂无学区数据" />
              </div>
            </div>
          </el-card>
        </el-col>

        <!-- 右侧：学区详情和学校管理 -->
        <el-col :span="16">
          <div v-if="!selectedDistrict" class="no-selection">
            <el-empty description="请选择一个学区查看详情" />
          </div>

          <div v-else class="district-detail">
            <!-- 学区基本信息 -->
            <el-card class="district-info-card">
              <template #header>
                <div class="card-header">
                  <span>{{ selectedDistrict.name }} - 基本信息</span>
                  <div>
                    <el-button size="small" @click="showMapView">
                      <el-icon><Location /></el-icon>
                      地图视图
                    </el-button>
                    <el-button size="small" @click="editDistrict(selectedDistrict)">
                      <el-icon><Edit /></el-icon>
                      编辑
                    </el-button>
                  </div>
                </div>
              </template>

              <el-descriptions :column="2" border>
                <el-descriptions-item label="学区名称">
                  {{ selectedDistrict.name }}
                </el-descriptions-item>
                <el-descriptions-item label="学区编码">
                  {{ selectedDistrict.code || '未设置' }}
                </el-descriptions-item>
                <el-descriptions-item label="所属区县">
                  {{ selectedDistrict.parent?.name || '未设置' }}
                </el-descriptions-item>
                <el-descriptions-item label="创建时间">
                  {{ formatDate(selectedDistrict.created_at) }}
                </el-descriptions-item>
                <el-descriptions-item label="学校数量">
                  {{ selectedDistrict.school_count || 0 }}
                </el-descriptions-item>
                <el-descriptions-item label="学生数量">
                  {{ selectedDistrict.student_count || 0 }}
                </el-descriptions-item>
                <el-descriptions-item label="描述" :span="2">
                  {{ selectedDistrict.description || '暂无描述' }}
                </el-descriptions-item>
              </el-descriptions>
            </el-card>

            <!-- 学校管理 -->
            <el-card class="schools-card">
              <template #header>
                <div class="card-header">
                  <span>学校管理</span>
                  <div>
                    <el-button size="small" @click="showAssignSchoolDialog">
                      <el-icon><Plus /></el-icon>
                      手动分配
                    </el-button>
                    <el-button size="small" @click="showAutoAssignDialog">
                      <el-icon><Setting /></el-icon>
                      自动分配
                    </el-button>
                    <el-button size="small" @click="showBatchAssignDialog">
                      <el-icon><Operation /></el-icon>
                      批量调整
                    </el-button>
                  </div>
                </div>
              </template>

              <el-table :data="districtSchools" style="width: 100%" v-loading="schoolsLoading">
                <el-table-column prop="name" label="学校名称" />
                <el-table-column prop="code" label="学校编码" width="120" />
                <el-table-column prop="student_count" label="学生数量" width="100">
                  <template #default="{ row }">
                    {{ row.student_count || 0 }}
                  </template>
                </el-table-column>
                <el-table-column label="地理位置" width="120">
                  <template #default="{ row }">
                    <el-tag v-if="row.school_location?.latitude && row.school_location?.longitude" size="small" type="success">
                      有位置
                    </el-tag>
                    <el-tag v-else size="small" type="warning">
                      无位置
                    </el-tag>
                  </template>
                </el-table-column>
                <el-table-column prop="address" label="地址" />
                <el-table-column label="操作" width="150">
                  <template #default="{ row }">
                    <el-button size="small" text @click="viewSchoolLocation(row)">
                      <el-icon><Location /></el-icon>
                      位置
                    </el-button>
                    <el-button size="small" text type="danger" @click="removeSchoolFromDistrict(row)">
                      移除
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
            </el-card>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 创建/编辑学区对话框 -->
    <el-dialog
      v-model="districtDialogVisible"
      :title="isEditing ? '编辑学区' : '创建学区'"
      width="600px"
    >
      <el-form :model="districtForm" :rules="districtRules" ref="districtFormRef" label-width="100px">
        <el-form-item label="学区名称" prop="name">
          <el-input v-model="districtForm.name" placeholder="请输入学区名称" />
        </el-form-item>
        <el-form-item label="学区编码" prop="code">
          <el-input v-model="districtForm.code" placeholder="请输入学区编码" />
        </el-form-item>
        <el-form-item label="所属区县" prop="parent_id">
          <el-select v-model="districtForm.parent_id" placeholder="请选择所属区县" style="width: 100%;">
            <el-option
              v-for="county in countyOptions"
              :key="county?.id || Math.random()"
              :label="county?.name || '未知区县'"
              :value="county?.id"
              v-if="county && county.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="描述">
          <el-input
            v-model="districtForm.description"
            type="textarea"
            :rows="3"
            placeholder="请输入学区描述"
          />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="districtDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveDistrict" :loading="saving">
          {{ isEditing ? '更新' : '创建' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- 概览统计对话框 -->
    <el-dialog
      v-model="overviewDialogVisible"
      title="学区划分概览统计"
      width="900px"
    >
      <div v-loading="overviewLoading">
        <el-row :gutter="20">
          <el-col :span="6">
            <el-statistic title="总学区数" :value="overviewData.total_districts || 0" />
          </el-col>
          <el-col :span="6">
            <el-statistic title="总学校数" :value="overviewData.total_schools || 0" />
          </el-col>
          <el-col :span="6">
            <el-statistic title="已分配学校" :value="overviewData.assigned_schools || 0" />
          </el-col>
          <el-col :span="6">
            <el-statistic title="未分配学校" :value="overviewData.unassigned_schools || 0" />
          </el-col>
        </el-row>

        <el-divider />

        <el-row :gutter="20">
          <el-col :span="12">
            <h4>地理信息覆盖率</h4>
            <el-progress
              :percentage="overviewData.location_coverage_rate || 0"
              :format="(percentage) => `${percentage}%`"
            />
          </el-col>
          <el-col :span="12">
            <h4>学区负载均衡度</h4>
            <el-progress
              :percentage="overviewData.balance_score || 0"
              :format="(percentage) => `${percentage}分`"
              :color="getBalanceColor(overviewData.balance_score)"
            />
          </el-col>
        </el-row>
      </div>

      <template #footer>
        <el-button @click="overviewDialogVisible = false">关闭</el-button>
        <el-button type="primary" @click="exportOverviewReport">
          <el-icon><Download /></el-icon>
          导出报告
        </el-button>
      </template>
    </el-dialog>

    <!-- 自动分配对话框 -->
    <el-dialog
      v-model="autoAssignDialogVisible"
      title="自动分配学校到学区"
      width="700px"
    >
      <el-form :model="autoAssignForm" label-width="120px">
        <el-form-item label="分配区域">
          <el-select v-model="autoAssignForm.region_id" placeholder="请选择区域" style="width: 100%;">
            <el-option
              v-for="region in regionOptions"
              :key="region?.id || Math.random()"
              :label="region?.name || '未知区域'"
              :value="region?.id"
              v-if="region && region.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="分配标准">
          <div class="criteria-settings">
            <div class="criteria-item">
              <span>距离权重：</span>
              <el-slider v-model="autoAssignForm.criteria.distance_weight" :max="100" show-input />
            </div>
            <div class="criteria-item">
              <span>规模权重：</span>
              <el-slider v-model="autoAssignForm.criteria.scale_weight" :max="100" show-input />
            </div>
            <div class="criteria-item">
              <span>均衡权重：</span>
              <el-slider v-model="autoAssignForm.criteria.balance_weight" :max="100" show-input />
            </div>
          </div>
        </el-form-item>

        <el-form-item>
          <el-button @click="previewAutoAssign" :loading="previewing">
            <el-icon><View /></el-icon>
            预览结果
          </el-button>
        </el-form-item>

        <div v-if="previewResults.length > 0" class="preview-results">
          <h4>预览结果</h4>
          <el-table :data="previewResults" max-height="300">
            <el-table-column prop="school_name" label="学校名称" />
            <el-table-column prop="district_name" label="分配到学区" />
            <el-table-column prop="reason" label="分配原因" />
          </el-table>
        </div>
      </el-form>

      <template #footer>
        <el-button @click="autoAssignDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="confirmAutoAssign" :loading="autoAssigning">
          确认自动分配
        </el-button>
      </template>
    </el-dialog>

    <!-- 手动分配学校对话框 -->
    <el-dialog
      v-model="assignSchoolDialogVisible"
      title="手动分配学校到学区"
      width="800px"
    >
      <div class="assign-school-content">
        <div class="available-schools">
          <h4>可分配学校</h4>
          <el-table
            :data="availableSchools"
            @selection-change="handleSchoolSelection"
            style="width: 100%"
          >
            <el-table-column type="selection" width="55" />
            <el-table-column prop="name" label="学校名称" />
            <el-table-column prop="code" label="学校编码" width="120" />
            <el-table-column label="地理位置" width="100">
              <template #default="{ row }">
                <el-tag v-if="row.school_location?.latitude" size="small" type="success">
                  有位置
                </el-tag>
                <el-tag v-else size="small" type="warning">
                  无位置
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="address" label="地址" />
          </el-table>
        </div>

        <el-divider />

        <el-form-item label="分配原因">
          <el-input
            v-model="assignReason"
            type="textarea"
            :rows="3"
            placeholder="请输入分配原因"
          />
        </el-form-item>
      </div>

      <template #footer>
        <el-button @click="assignSchoolDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          @click="confirmAssignSchools"
          :disabled="selectedSchools.length === 0"
          :loading="assigning"
        >
          分配选中学校
        </el-button>
      </template>
    </el-dialog>

    <!-- 批量调整对话框 -->
    <el-dialog
      v-model="batchAssignDialogVisible"
      title="批量调整学校学区归属"
      width="900px"
    >
      <div class="batch-assign-content">
        <div class="batch-operations">
          <el-button @click="loadAllSchools" :loading="loadingAllSchools">
            <el-icon><Refresh /></el-icon>
            加载所有学校
          </el-button>
          <el-button @click="clearBatchAssignments">
            <el-icon><Delete /></el-icon>
            清空调整
          </el-button>
        </div>

        <el-table :data="batchAssignments" style="width: 100%; margin-top: 20px;">
          <el-table-column prop="school_name" label="学校名称" />
          <el-table-column prop="current_district" label="当前学区" />
          <el-table-column label="调整到学区" width="200">
            <template #default="{ row, $index }">
              <el-select v-model="row.new_district_id" placeholder="选择学区">
                <el-option
                  v-for="district in districts"
                  :key="district?.id || Math.random()"
                  :label="district?.name || '未知学区'"
                  :value="district?.id"
                  v-if="district && district.id"
                />
              </el-select>
            </template>
          </el-table-column>
          <el-table-column label="调整原因" width="200">
            <template #default="{ row, $index }">
              <el-input v-model="row.reason" placeholder="输入原因" />
            </template>
          </el-table-column>
          <el-table-column label="操作" width="80">
            <template #default="{ row, $index }">
              <el-button size="small" text type="danger" @click="removeBatchAssignment($index)">
                移除
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <template #footer>
        <el-button @click="batchAssignDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          @click="confirmBatchAssign"
          :disabled="batchAssignments.length === 0"
          :loading="batchAssigning"
        >
          确认批量调整
        </el-button>
      </template>
    </el-dialog>

    <!-- 划分历史对话框 -->
    <el-dialog
      v-model="historyDialogVisible"
      title="学区划分历史记录"
      width="1000px"
    >
      <div class="history-filters">
        <el-form :model="historyFilters" inline>
          <el-form-item label="学校">
            <el-select v-model="historyFilters.school_id" placeholder="选择学校" clearable>
              <el-option
                v-for="school in allSchools"
                :key="school?.id || Math.random()"
                :label="school?.name || '未知学校'"
                :value="school?.id"
                v-if="school && school.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="学区">
            <el-select v-model="historyFilters.district_id" placeholder="选择学区" clearable>
              <el-option
                v-for="district in districts"
                :key="district?.id || Math.random()"
                :label="district?.name || '未知学区'"
                :value="district?.id"
                v-if="district && district.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="类型">
            <el-select v-model="historyFilters.assignment_type" placeholder="选择类型" clearable>
              <el-option label="自动分配" value="auto" />
              <el-option label="手动分配" value="manual" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button @click="loadAssignmentHistory" :loading="historyLoading">
              <el-icon><Search /></el-icon>
              查询
            </el-button>
          </el-form-item>
        </el-form>
      </div>

      <el-table :data="assignmentHistory" style="width: 100%" v-loading="historyLoading">
        <el-table-column prop="school.name" label="学校名称" />
        <el-table-column prop="old_district.name" label="原学区" />
        <el-table-column prop="new_district.name" label="新学区" />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag :type="row.assignment_type === 'auto' ? 'success' : 'primary'">
              {{ row.assignment_type === 'auto' ? '自动' : '手动' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="operator.name" label="操作人" />
        <el-table-column prop="reason" label="原因" />
        <el-table-column prop="created_at" label="操作时间" width="160">
          <template #default="{ row }">
            {{ formatDate(row.created_at) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100">
          <template #default="{ row }">
            <el-button size="small" text type="danger" @click="revertAssignment(row)">
              撤销
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="historyPagination.current_page"
          v-model:page-size="historyPagination.per_page"
          :total="historyPagination.total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="loadAssignmentHistory"
          @current-change="loadAssignmentHistory"
        />
      </div>

      <template #footer>
        <el-button @click="historyDialogVisible = false">关闭</el-button>
        <el-button type="primary" @click="exportHistoryReport">
          <el-icon><Download /></el-icon>
          导出历史
        </el-button>
      </template>
    </el-dialog>

    <!-- 地图视图对话框 -->
    <el-dialog
      v-model="mapDialogVisible"
      title="学区划分地图视图"
      width="90%"
      :close-on-click-modal="false"
    >
      <div class="map-dialog-content">
        <DistrictMap
          :selected-district="selectedDistrict"
          :region-id="currentRegionId"
          @school-click="handleSchoolClick"
          @district-click="handleDistrictClick"
        />
      </div>

      <template #footer>
        <el-button @click="mapDialogVisible = false">关闭</el-button>
        <el-button type="primary" @click="exportMapImage">
          <el-icon><Camera /></el-icon>
          导出图片
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Refresh,
  DataAnalysis,
  Clock,
  Plus,
  Search,
  Edit,
  Delete,
  Location,
  Operation,
  View,
  Download,
  Camera,
  Setting
} from '@element-plus/icons-vue'
import { organizationApi } from '@/api/organization'
import { districtApi } from '@/api/district'
import { useAuthStore } from '@/stores/auth'
import DistrictMap from '@/components/DistrictMap.vue'
import dayjs from 'dayjs'

// 响应式数据
const loading = ref(false)
const saving = ref(false)
const assigning = ref(false)
const schoolsLoading = ref(false)
const overviewLoading = ref(false)
const historyLoading = ref(false)
const autoAssigning = ref(false)
const batchAssigning = ref(false)
const previewing = ref(false)
const loadingAllSchools = ref(false)

const searchText = ref('')
const districts = ref([])
const selectedDistrict = ref(null)
const districtSchools = ref([])
const countyOptions = ref([])
const regionOptions = ref([])
const availableSchools = ref([])
const allSchools = ref([])
const selectedSchools = ref([])
const assignReason = ref('')

// 对话框显示状态
const districtDialogVisible = ref(false)
const assignSchoolDialogVisible = ref(false)
const overviewDialogVisible = ref(false)
const autoAssignDialogVisible = ref(false)
const batchAssignDialogVisible = ref(false)
const historyDialogVisible = ref(false)
const mapDialogVisible = ref(false)
const isEditing = ref(false)

// 地图相关数据
const currentRegionId = ref(null)

// 概览数据
const overviewData = ref({})

// 自动分配表单
const autoAssignForm = reactive({
  region_id: null,
  criteria: {
    distance_weight: 40,
    scale_weight: 30,
    balance_weight: 30
  }
})

// 预览结果
const previewResults = ref([])

// 批量调整数据
const batchAssignments = ref([])

// 历史记录数据
const assignmentHistory = ref([])
const historyFilters = reactive({
  school_id: null,
  district_id: null,
  assignment_type: null,
  date_from: null,
  date_to: null
})
const historyPagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0
})

// 表单数据
const districtForm = reactive({
  name: '',
  code: '',
  parent_id: null,
  description: ''
})

// 表单验证规则
const districtRules = {
  name: [
    { required: true, message: '请输入学区名称', trigger: 'blur' }
  ],
  parent_id: [
    { required: true, message: '请选择所属区县', trigger: 'change' }
  ]
}

// 组件引用
const districtFormRef = ref(null)

// 计算属性
const filteredDistricts = computed(() => {
  // 确保districts.value是数组
  const districtsArray = Array.isArray(districts.value) ? districts.value : []

  // 首先过滤掉null值和无效对象
  const validDistricts = districtsArray.filter(district =>
    district && district.id && district.name
  )

  if (!searchText.value) return validDistricts
  return validDistricts.filter(district =>
    district.name.toLowerCase().includes(searchText.value.toLowerCase())
  )
})

// 方法
const loadDistricts = async () => {
  loading.value = true
  try {
    const response = await organizationApi.getList({
      type: 'education_zone',
      with_schools: true
    })

    // 正确提取分页数据中的实际数组
    const data = response.data?.data?.data || response.data?.data || []
    districts.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error('加载学区列表失败:', error)
    ElMessage.error('加载学区列表失败')
    districts.value = [] // 确保在错误时设置为空数组
  } finally {
    loading.value = false
  }
}

const loadCountyOptions = async () => {
  try {
    console.log('开始加载区县选项...')

    // 检查用户是否已登录
    const authStore = useAuthStore()
    if (!authStore.isAuthenticated) {
      console.warn('用户未登录，无法加载区县数据')
      ElMessage.warning('请先登录')
      return
    }

    const response = await organizationApi.getList({
      type: 'district'
    })
    console.log('区县API响应:', response)

    // 处理不同的响应数据结构
    let data = []
    if (response.data?.data?.data) {
      data = response.data.data.data
    } else if (response.data?.data) {
      data = response.data.data
    } else if (response.data) {
      data = response.data
    }

    console.log('提取的区县数据:', data)

    // 确保过滤掉null值和无效数据
    const filteredData = Array.isArray(data) ? data.filter(item => item && item.id && item.name) : []
    console.log('过滤后的区县数据:', filteredData)

    countyOptions.value = filteredData

    if (filteredData.length === 0) {
      console.warn('没有找到区县数据，可能是权限问题或数据为空')
      ElMessage.warning('没有找到可用的区县数据')
    }
  } catch (error) {
    console.error('加载区县选项失败:', error)
    if (error.response?.status === 401) {
      ElMessage.error('登录已过期，请重新登录')
    } else if (error.response?.status === 403) {
      ElMessage.error('权限不足，无法访问区县数据')
    } else {
      ElMessage.error('加载区县选项失败: ' + (error.message || '未知错误'))
    }
    countyOptions.value = [] // 确保在错误时设置为空数组
  }
}

const loadRegionOptions = async () => {
  try {
    const response = await organizationApi.getList({
      type: 'district'
    })
    // 确保过滤掉null值和无效数据
    const data = response.data?.data?.data || response.data?.data || []
    regionOptions.value = Array.isArray(data) ? data.filter(item => item && item.id && item.name) : []
  } catch (error) {
    console.error('加载区域选项失败:', error)
    regionOptions.value = [] // 确保在错误时设置为空数组
  }
}

const loadAvailableSchools = async () => {
  try {
    const response = await districtApi.getAvailableSchools({
      without_district: true
    })
    availableSchools.value = response.data.data || []
  } catch (error) {
    console.error('加载可分配学校失败:', error)
  }
}

const loadDistrictSchools = async (districtId) => {
  if (!districtId) return

  schoolsLoading.value = true
  try {
    const response = await districtApi.getDistrictSchools(districtId)
    districtSchools.value = response.data.data || []
  } catch (error) {
    console.error('加载学区学校失败:', error)
    ElMessage.error('加载学区学校失败')
  } finally {
    schoolsLoading.value = false
  }
}

const loadAllSchools = async () => {
  loadingAllSchools.value = true
  try {
    const response = await organizationApi.getList({
      type: 'school'
    })
    allSchools.value = response.data.data || []

    // 初始化批量调整数据
    batchAssignments.value = allSchools.value.map(school => ({
      school_id: school.id,
      school_name: school.name,
      current_district: school.parent?.name || '未分配',
      new_district_id: null,
      reason: ''
    }))
  } catch (error) {
    console.error('加载所有学校失败:', error)
    ElMessage.error('加载所有学校失败')
  } finally {
    loadingAllSchools.value = false
  }
}

const refreshData = () => {
  loadDistricts()
  if (selectedDistrict.value) {
    selectDistrict(selectedDistrict.value)
  }
}

const selectDistrict = async (district) => {
  try {
    const response = await organizationApi.getDetail(district.id)
    selectedDistrict.value = response.data
    // 加载学区内的学校
    await loadDistrictSchools(district.id)
  } catch (error) {
    console.error('加载学区详情失败:', error)
    ElMessage.error('加载学区详情失败')
  }
}

// 显示概览统计对话框
const showOverviewDialog = async () => {
  overviewDialogVisible.value = true
  await loadOverviewData()
}

// 加载概览数据
const loadOverviewData = async () => {
  overviewLoading.value = true
  try {
    const response = await districtApi.getOverview()
    console.log('概览数据响应:', response) // 调试日志

    // 处理不同的响应数据结构
    let data = {}
    if (response.data?.data) {
      data = response.data.data
    } else if (response.data) {
      data = response.data
    }

    // 确保所有统计字段都有默认值
    overviewData.value = {
      total_districts: data.total_districts || 0,
      total_schools: data.total_schools || 0,
      assigned_schools: data.assigned_schools || 0,
      unassigned_schools: data.unassigned_schools || 0,
      schools_with_location: data.schools_with_location || 0,
      location_coverage_rate: data.location_coverage_rate || 0,
      balance_score: data.balance_score || 0,
      ...data
    }

    console.log('处理后的概览数据:', overviewData.value) // 调试日志
  } catch (error) {
    console.error('加载概览数据失败:', error)
    ElMessage.error('加载概览数据失败')
    // 设置默认值以防止显示错误
    overviewData.value = {
      total_districts: 0,
      total_schools: 0,
      assigned_schools: 0,
      unassigned_schools: 0,
      schools_with_location: 0,
      location_coverage_rate: 0,
      balance_score: 0
    }
  } finally {
    overviewLoading.value = false
  }
}

// 获取均衡度颜色
const getBalanceColor = (score) => {
  if (score >= 80) return '#67c23a'
  if (score >= 60) return '#e6a23c'
  return '#f56c6c'
}

// 显示自动分配对话框
const showAutoAssignDialog = () => {
  autoAssignDialogVisible.value = true
  previewResults.value = []
}

// 预览自动分配结果
const previewAutoAssign = async () => {
  if (!autoAssignForm.region_id) {
    ElMessage.warning('请选择分配区域')
    return
  }

  previewing.value = true
  try {
    const response = await districtApi.previewAutoAssign(autoAssignForm)
    previewResults.value = response.data.assignments || []
  } catch (error) {
    console.error('预览自动分配失败:', error)
    ElMessage.error('预览自动分配失败')
  } finally {
    previewing.value = false
  }
}

// 确认自动分配
const confirmAutoAssign = async () => {
  if (!autoAssignForm.region_id) {
    ElMessage.warning('请选择分配区域')
    return
  }

  try {
    await ElMessageBox.confirm(
      '确定要执行自动分配吗？此操作将根据设定的标准重新分配学校。',
      '确认自动分配',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    autoAssigning.value = true
    const response = await districtApi.autoAssign(autoAssignForm)

    ElMessage.success(`自动分配完成：成功分配 ${response.data.assigned} 所学校`)
    autoAssignDialogVisible.value = false

    // 刷新数据
    refreshData()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('自动分配失败:', error)
      ElMessage.error('自动分配失败')
    }
  } finally {
    autoAssigning.value = false
  }
}

const showCreateDialog = () => {
  isEditing.value = false
  resetDistrictForm()
  districtDialogVisible.value = true
}

const editDistrict = (district) => {
  isEditing.value = true
  Object.assign(districtForm, {
    name: district.name,
    code: district.code,
    parent_id: district.parent_id,
    description: district.description
  })
  districtDialogVisible.value = true
}

const resetDistrictForm = () => {
  Object.assign(districtForm, {
    name: '',
    code: '',
    parent_id: null,
    description: ''
  })
}

const saveDistrict = async () => {
  try {
    await districtFormRef.value.validate()
    
    saving.value = true
    
    const data = {
      ...districtForm,
      type: 'education_zone',
      level: 4
    }
    
    if (isEditing.value) {
      await organizationApi.update(selectedDistrict.value.id, data)
      ElMessage.success('更新学区成功')
    } else {
      await organizationApi.create(data)
      ElMessage.success('创建学区成功')
    }
    
    districtDialogVisible.value = false
    loadDistricts()
  } catch (error) {
    if (error.errors) {
      return
    }
    console.error('保存学区失败:', error)
    ElMessage.error('保存学区失败')
  } finally {
    saving.value = false
  }
}

const deleteDistrict = async (district) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除学区 "${district.name}" 吗？`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    await organizationApi.delete(district.id)
    ElMessage.success('删除学区成功')
    
    if (selectedDistrict.value?.id === district.id) {
      selectedDistrict.value = null
    }
    
    loadDistricts()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除学区失败:', error)
      ElMessage.error('删除学区失败')
    }
  }
}

const showAssignSchoolDialog = () => {
  loadAvailableSchools()
  assignSchoolDialogVisible.value = true
}

const handleSchoolSelection = (selection) => {
  selectedSchools.value = selection
}

const confirmAssignSchools = async () => {
  if (selectedSchools.value.length === 0) {
    ElMessage.warning('请选择要分配的学校')
    return
  }

  if (!selectedDistrict.value) {
    ElMessage.warning('请先选择学区')
    return
  }

  try {
    assigning.value = true

    // 批量手动分配学校
    const assignments = selectedSchools.value.map(school => ({
      school_id: school.id,
      district_id: selectedDistrict.value.id,
      reason: assignReason.value || '手动分配'
    }))

    const response = await districtApi.batchAssign({ assignments })

    ElMessage.success(`成功分配 ${response.data.assigned} 所学校`)
    assignSchoolDialogVisible.value = false
    selectedSchools.value = []
    assignReason.value = ''

    // 刷新学区详情
    if (selectedDistrict.value) {
      selectDistrict(selectedDistrict.value)
    }
  } catch (error) {
    console.error('分配学校失败:', error)
    ElMessage.error('分配学校失败')
  } finally {
    assigning.value = false
  }
}

const removeSchoolFromDistrict = async (school) => {
  try {
    await ElMessageBox.confirm(
      `确定要将 "${school.name}" 从当前学区移除吗？`,
      '确认移除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    // 手动调整学校到未分配状态（parent_id设为null）
    await districtApi.manualAssign({
      school_id: school.id,
      district_id: null,
      reason: '从学区移除'
    })

    ElMessage.success('移除学校成功')

    // 刷新学区详情
    selectDistrict(selectedDistrict.value)
  } catch (error) {
    if (error !== 'cancel') {
      console.error('移除学校失败:', error)
      ElMessage.error('移除学校失败')
    }
  }
}

// 显示批量调整对话框
const showBatchAssignDialog = () => {
  batchAssignDialogVisible.value = true
  batchAssignments.value = []
}

// 确认批量调整
const confirmBatchAssign = async () => {
  const validAssignments = batchAssignments.value.filter(item =>
    item.new_district_id && item.new_district_id !== item.current_district_id
  )

  if (validAssignments.length === 0) {
    ElMessage.warning('没有需要调整的学校')
    return
  }

  try {
    batchAssigning.value = true

    const assignments = validAssignments.map(item => ({
      school_id: item.school_id,
      district_id: item.new_district_id,
      reason: item.reason || '批量调整'
    }))

    const response = await districtApi.batchAssign({ assignments })

    ElMessage.success(`批量调整完成：成功调整 ${response.data.assigned} 所学校`)
    batchAssignDialogVisible.value = false

    // 刷新数据
    refreshData()
  } catch (error) {
    console.error('批量调整失败:', error)
    ElMessage.error('批量调整失败')
  } finally {
    batchAssigning.value = false
  }
}

// 清空批量调整
const clearBatchAssignments = () => {
  batchAssignments.value = []
}

// 移除批量调整项
const removeBatchAssignment = (index) => {
  batchAssignments.value.splice(index, 1)
}

// 显示历史记录对话框
const showHistoryDialog = () => {
  historyDialogVisible.value = true
  loadAssignmentHistory()
}

// 加载划分历史
const loadAssignmentHistory = async () => {
  historyLoading.value = true
  try {
    const params = {
      ...historyFilters,
      page: historyPagination.current_page,
      per_page: historyPagination.per_page
    }

    const response = await districtApi.getAssignmentHistory(params)
    assignmentHistory.value = response.data.data || []
    historyPagination.total = response.data.total || 0
  } catch (error) {
    console.error('加载划分历史失败:', error)
    ElMessage.error('加载划分历史失败')
  } finally {
    historyLoading.value = false
  }
}

// 撤销划分操作
const revertAssignment = async (record) => {
  try {
    await ElMessageBox.confirm(
      `确定要撤销这次划分操作吗？将会把 "${record.school?.name}" 恢复到原学区。`,
      '确认撤销',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    await districtApi.revertAssignment(record.id, {
      reason: '撤销操作'
    })

    ElMessage.success('撤销操作成功')

    // 刷新历史记录和数据
    loadAssignmentHistory()
    refreshData()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('撤销操作失败:', error)
      ElMessage.error('撤销操作失败')
    }
  }
}

// 查看学校位置
const viewSchoolLocation = (school) => {
  if (!school.school_location?.latitude || !school.school_location?.longitude) {
    ElMessage.warning('该学校没有地理位置信息')
    return
  }

  // 这里可以打开地图显示学校位置
  ElMessage.info(`学校位置：${school.school_location.latitude}, ${school.school_location.longitude}`)
}

// 显示地图视图
const showMapView = () => {
  mapDialogVisible.value = true
  // 设置当前区域ID，如果有选中的学区，使用其父级区域
  if (selectedDistrict.value?.parent_id) {
    currentRegionId.value = selectedDistrict.value.parent_id
  }
}

// 处理地图中学校点击事件
const handleSchoolClick = (schoolData) => {
  ElMessage.info(`点击了学校: ${schoolData.name}`)
  // 可以在这里添加更多交互逻辑，比如显示学校详情
}

// 处理地图中学区点击事件
const handleDistrictClick = (districtData) => {
  ElMessage.info(`点击了学区: ${districtData.name}`)
  // 可以在这里添加更多交互逻辑，比如选中该学区
}

// 导出地图图片
const exportMapImage = () => {
  ElMessage.info('导出地图图片功能开发中...')
}

// 导出概览报告
const exportOverviewReport = async () => {
  try {
    const response = await districtApi.exportReport({
      format: 'excel'
    })

    // 处理文件下载
    const blob = new Blob([response.data])
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `学区划分概览报告_${dayjs().format('YYYY-MM-DD')}.xlsx`
    link.click()
    window.URL.revokeObjectURL(url)

    ElMessage.success('报告导出成功')
  } catch (error) {
    console.error('导出报告失败:', error)
    ElMessage.error('导出报告失败')
  }
}

// 导出历史报告
const exportHistoryReport = async () => {
  try {
    const response = await districtApi.exportReport({
      type: 'history',
      format: 'excel',
      ...historyFilters
    })

    // 处理文件下载
    const blob = new Blob([response.data])
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `学区划分历史报告_${dayjs().format('YYYY-MM-DD')}.xlsx`
    link.click()
    window.URL.revokeObjectURL(url)

    ElMessage.success('历史报告导出成功')
  } catch (error) {
    console.error('导出历史报告失败:', error)
    ElMessage.error('导出历史报告失败')
  }
}

const formatDate = (date) => {
  return dayjs(date).format('YYYY-MM-DD HH:mm')
}

// 生命周期
onMounted(() => {
  loadDistricts()
  loadCountyOptions()
  loadRegionOptions()
})
</script>

<style scoped>
.district-management {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f5f7fa;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: #fff;
  border-bottom: 1px solid #ebeef5;
}

.header-content h2 {
  margin: 0 0 5px 0;
  color: #303133;
  font-size: 20px;
  font-weight: 600;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.page-content {
  flex: 1;
  padding: 20px;
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.district-list {
  max-height: 600px;
  overflow-y: auto;
}

.district-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin-bottom: 10px;
  background: #f8f9fa;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.district-item:hover {
  background: #e3f2fd;
}

.district-item.active {
  background: #e3f2fd;
  border: 1px solid #409eff;
}

.district-info {
  flex: 1;
}

.district-name {
  font-weight: 600;
  color: #303133;
  margin-bottom: 5px;
}

.district-meta {
  display: flex;
  gap: 5px;
}

.district-actions {
  display: flex;
  gap: 5px;
}

.no-selection {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 400px;
}

.district-detail {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.district-info-card,
.schools-card {
  background: #fff;
}

.assign-school-content h4 {
  margin: 0 0 15px 0;
  color: #303133;
}

.empty-state {
  text-align: center;
  padding: 40px 0;
}

.criteria-settings {
  padding: 15px;
  background: #f5f7fa;
  border-radius: 6px;
  border: 1px solid #e4e7ed;
}

.criteria-item {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.criteria-item:last-child {
  margin-bottom: 0;
}

.criteria-item span {
  width: 100px;
  font-weight: 500;
  color: #606266;
}

.preview-results {
  margin-top: 20px;
  padding: 15px;
  background: #f0f9ff;
  border-radius: 6px;
  border: 1px solid #b3d8ff;
}

.preview-results h4 {
  margin: 0 0 15px 0;
  color: #1890ff;
  font-size: 14px;
}

.batch-assign-content {
  max-height: 500px;
  overflow-y: auto;
}

.batch-operations {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  padding: 10px;
  background: #f5f7fa;
  border-radius: 4px;
}

.history-filters {
  margin-bottom: 20px;
  padding: 15px;
  background: #f5f7fa;
  border-radius: 6px;
  border: 1px solid #e4e7ed;
}

.pagination-wrapper {
  margin-top: 20px;
  text-align: center;
  padding: 15px 0;
}

.assign-school-content {
  max-height: 400px;
  overflow-y: auto;
}

.map-dialog-content {
  height: 600px;
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  overflow: hidden;
}
</style>
