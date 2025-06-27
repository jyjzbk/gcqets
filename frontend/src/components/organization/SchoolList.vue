<template>
  <div class="school-list">
    <div class="list-header">
      <h3>学校列表</h3>
      <div class="header-actions">
        <el-button type="primary" :icon="Plus" @click="showAddDialog">
          新增学校
        </el-button>
        <el-dropdown @command="handleQuickAction">
          <el-button type="success" :icon="Download">
            快速操作<el-icon class="el-icon--right"><ArrowDown /></el-icon>
          </el-button>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item command="export" :icon="Download">导出数据</el-dropdown-item>
              <el-dropdown-item command="template" :icon="Document">下载模板</el-dropdown-item>
              <el-dropdown-item command="stats" :icon="DataAnalysis">统计分析</el-dropdown-item>
              <el-dropdown-item divided command="refresh" :icon="Refresh">刷新数据</el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
        <el-button :icon="Refresh" @click="loadSchools">
          刷新
        </el-button>
      </div>
    </div>

    <!-- 搜索筛选 -->
    <el-card class="filter-card">
      <template #header>
        <div class="filter-header">
          <span>搜索筛选</span>
          <el-button
            type="text"
            @click="showAdvancedSearch = !showAdvancedSearch"
            :icon="showAdvancedSearch ? ArrowUp : ArrowDown"
          >
            {{ showAdvancedSearch ? '收起' : '高级搜索' }}
          </el-button>
        </div>
      </template>

      <el-form :inline="true" :model="filters" class="filter-form">
        <!-- 基础搜索 -->
        <el-form-item label="学校名称">
          <el-input
            v-model="filters.name"
            placeholder="请输入学校名称"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="学校代码">
          <el-input
            v-model="filters.code"
            placeholder="请输入学校代码"
            clearable
            style="width: 150px"
          />
        </el-form-item>
        <el-form-item label="学校类型">
          <el-select
            v-model="filters.school_type"
            placeholder="请选择学校类型"
            clearable
            style="width: 150px"
          >
            <el-option label="小学" value="primary" />
            <el-option label="初中" value="junior_high" />
            <el-option label="高中" value="senior_high" />
            <el-option label="九年一贯制" value="nine_year" />
            <el-option label="完全中学" value="complete_middle" />
            <el-option label="职业学校" value="vocational" />
            <el-option label="特殊教育学校" value="special_education" />
          </el-select>
        </el-form-item>
        <el-form-item label="所属组织">
          <el-tree-select
            v-model="filters.parent_id"
            :data="organizationTree"
            :props="treeProps"
            placeholder="请选择所属组织"
            check-strictly
            clearable
            style="width: 200px"
          />
        </el-form-item>

        <!-- 高级搜索 -->
        <template v-if="showAdvancedSearch">
          <el-form-item label="教育层次">
            <el-select
              v-model="filters.education_level"
              placeholder="请选择教育层次"
              clearable
              style="width: 150px"
            >
              <el-option label="学前教育" value="preschool" />
              <el-option label="小学教育" value="primary" />
              <el-option label="初中教育" value="junior_high" />
              <el-option label="高中教育" value="senior_high" />
              <el-option label="中等职业教育" value="vocational" />
              <el-option label="特殊教育" value="special" />
            </el-select>
          </el-form-item>
          <el-form-item label="校长姓名">
            <el-input
              v-model="filters.principal_name"
              placeholder="请输入校长姓名"
              clearable
              style="width: 150px"
            />
          </el-form-item>
          <el-form-item label="联系电话">
            <el-input
              v-model="filters.contact_phone"
              placeholder="请输入联系电话"
              clearable
              style="width: 150px"
            />
          </el-form-item>
          <el-form-item label="学生人数">
            <el-input-number
              v-model="filters.min_students"
              placeholder="最少"
              :min="0"
              style="width: 100px"
            />
            <span style="margin: 0 8px">-</span>
            <el-input-number
              v-model="filters.max_students"
              placeholder="最多"
              :min="0"
              style="width: 100px"
            />
          </el-form-item>
          <el-form-item label="建校年份">
            <el-date-picker
              v-model="filters.founded_year_range"
              type="yearrange"
              range-separator="至"
              start-placeholder="开始年份"
              end-placeholder="结束年份"
              style="width: 200px"
            />
          </el-form-item>
          <el-form-item label="状态">
            <el-select
              v-model="filters.status"
              placeholder="请选择状态"
              clearable
              style="width: 120px"
            >
              <el-option label="正常" value="active" />
              <el-option label="停用" value="inactive" />
            </el-select>
          </el-form-item>
        </template>

        <el-form-item>
          <el-button type="primary" @click="searchSchools" :loading="loading">
            搜索
          </el-button>
          <el-button @click="resetFilters">重置</el-button>
          <el-button type="info" @click="saveSearchTemplate">
            保存搜索
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 学校表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <span>学校列表 ({{ pagination.total }} 条记录)</span>
          <div class="table-actions" v-if="selectedSchools.length > 0">
            <el-tag type="info" class="selection-tag">
              已选择 {{ selectedSchools.length }} 项
            </el-tag>
            <el-button type="warning" size="small" @click="batchEdit">
              批量编辑
            </el-button>
            <el-button type="success" size="small" @click="batchExport">
              批量导出
            </el-button>
            <el-button type="danger" size="small" @click="batchDelete">
              批量删除
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        :data="schools"
        style="width: 100%"
        v-loading="loading"
        row-key="id"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column type="index" label="序号" width="60" />
        <el-table-column prop="name" label="学校名称" width="180" />
        <el-table-column prop="code" label="学校代码" width="120" />
        <el-table-column prop="school_type" label="学校类型" width="120">
          <template #default="{ row }">
            {{ getSchoolTypeText(row.school_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="education_level" label="教育层次" width="120">
          <template #default="{ row }">
            {{ getEducationLevelText(row.education_level) }}
          </template>
        </el-table-column>
        <el-table-column prop="parent.name" label="所属组织" width="150" />
        <el-table-column prop="principal_name" label="校长" width="100" />
        <el-table-column prop="principal_phone" label="校长电话" width="120" />
        <el-table-column prop="contact_person" label="联系人" width="100" />
        <el-table-column prop="contact_phone" label="联系电话" width="120" />
        <el-table-column prop="student_count" label="学生数" width="80" />
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
              {{ row.status === 'active' ? '正常' : '停用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="viewSchool(row)">
              查看
            </el-button>
            <el-button type="warning" size="small" @click="editSchool(row)">
              编辑
            </el-button>
            <el-button type="danger" size="small" @click="deleteSchool(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="pagination.current_page"
          v-model:page-size="pagination.per_page"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="loadSchools"
          @current-change="loadSchools"
        />
      </div>
    </el-card>

    <!-- 学校详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="学校详情"
      width="80%"
      :before-close="handleDetailDialogClose"
    >
      <div v-if="selectedSchool" class="school-detail">
        <!-- 基本信息 -->
        <el-card class="detail-section">
          <template #header>
            <div class="section-header">
              <span>基本信息</span>
              <el-tag :type="selectedSchool.status === 'active' ? 'success' : 'danger'">
                {{ selectedSchool.status === 'active' ? '正常' : '停用' }}
              </el-tag>
            </div>
          </template>

          <el-row :gutter="20">
            <el-col :span="12">
              <el-descriptions :column="1" border>
                <el-descriptions-item label="学校名称">
                  {{ selectedSchool.name }}
                </el-descriptions-item>
                <el-descriptions-item label="学校代码">
                  {{ selectedSchool.code }}
                </el-descriptions-item>
                <el-descriptions-item label="学校类型">
                  {{ getSchoolTypeText(selectedSchool.school_type) }}
                </el-descriptions-item>
                <el-descriptions-item label="教育层次">
                  {{ getEducationLevelText(selectedSchool.education_level) }}
                </el-descriptions-item>
                <el-descriptions-item label="所属组织">
                  {{ selectedSchool.parent?.name || '无' }}
                </el-descriptions-item>
                <el-descriptions-item label="学校地址">
                  {{ selectedSchool.address || '未填写' }}
                </el-descriptions-item>
              </el-descriptions>
            </el-col>
            <el-col :span="12">
              <el-descriptions :column="1" border>
                <el-descriptions-item label="学生人数">
                  {{ selectedSchool.student_count || '未统计' }}
                </el-descriptions-item>
                <el-descriptions-item label="校园面积">
                  {{ selectedSchool.campus_area ? selectedSchool.campus_area + ' 平方米' : '未填写' }}
                </el-descriptions-item>
                <el-descriptions-item label="建校年份">
                  {{ selectedSchool.founded_year || '未填写' }}
                </el-descriptions-item>
                <el-descriptions-item label="创建时间">
                  {{ formatDateTime(selectedSchool.created_at) }}
                </el-descriptions-item>
                <el-descriptions-item label="更新时间">
                  {{ formatDateTime(selectedSchool.updated_at) }}
                </el-descriptions-item>
                <el-descriptions-item label="学校描述">
                  {{ selectedSchool.description || '无' }}
                </el-descriptions-item>
              </el-descriptions>
            </el-col>
          </el-row>
        </el-card>

        <!-- 联系信息 -->
        <el-card class="detail-section">
          <template #header>
            <span>联系信息</span>
          </template>

          <el-row :gutter="20">
            <el-col :span="12">
              <h4>校长信息</h4>
              <el-descriptions :column="1" border>
                <el-descriptions-item label="校长姓名">
                  {{ selectedSchool.principal_name || '未填写' }}
                </el-descriptions-item>
                <el-descriptions-item label="校长电话">
                  {{ selectedSchool.principal_phone || '未填写' }}
                </el-descriptions-item>
                <el-descriptions-item label="校长邮箱">
                  {{ selectedSchool.principal_email || '未填写' }}
                </el-descriptions-item>
              </el-descriptions>
            </el-col>
            <el-col :span="12">
              <h4>联系人信息</h4>
              <el-descriptions :column="1" border>
                <el-descriptions-item label="联系人">
                  {{ selectedSchool.contact_person || '未填写' }}
                </el-descriptions-item>
                <el-descriptions-item label="联系电话">
                  {{ selectedSchool.contact_phone || '未填写' }}
                </el-descriptions-item>
                <el-descriptions-item label="传真号码">
                  {{ selectedSchool.fax || '未填写' }}
                </el-descriptions-item>
              </el-descriptions>
            </el-col>
          </el-row>
        </el-card>

        <!-- 地理位置 -->
        <el-card v-if="selectedSchool.longitude && selectedSchool.latitude" class="detail-section">
          <template #header>
            <span>地理位置</span>
          </template>

          <el-row :gutter="20">
            <el-col :span="12">
              <el-descriptions :column="1" border>
                <el-descriptions-item label="经度">
                  {{ selectedSchool.longitude }}
                </el-descriptions-item>
                <el-descriptions-item label="纬度">
                  {{ selectedSchool.latitude }}
                </el-descriptions-item>
              </el-descriptions>
            </el-col>
            <el-col :span="12">
              <div class="map-placeholder">
                <el-icon size="48"><Location /></el-icon>
                <p>地图显示功能</p>
                <p>经纬度: {{ selectedSchool.longitude }}, {{ selectedSchool.latitude }}</p>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 扩展信息 -->
        <el-card v-if="selectedSchool.extra_data" class="detail-section">
          <template #header>
            <span>扩展信息</span>
          </template>

          <el-descriptions :column="2" border>
            <el-descriptions-item v-if="selectedSchool.extra_data.class_count" label="班级数">
              {{ selectedSchool.extra_data.class_count }}
            </el-descriptions-item>
            <el-descriptions-item v-if="selectedSchool.extra_data.teacher_count" label="教师人数">
              {{ selectedSchool.extra_data.teacher_count }}
            </el-descriptions-item>
            <el-descriptions-item v-if="selectedSchool.extra_data.facilities" label="设施设备" :span="2">
              {{ selectedSchool.extra_data.facilities }}
            </el-descriptions-item>
            <el-descriptions-item v-if="selectedSchool.extra_data.special_programs" label="特色项目" :span="2">
              {{ selectedSchool.extra_data.special_programs }}
            </el-descriptions-item>
          </el-descriptions>
        </el-card>

        <!-- 操作历史 -->
        <el-card class="detail-section">
          <template #header>
            <span>操作历史</span>
          </template>

          <el-timeline>
            <el-timeline-item
              timestamp="创建时间"
              :time="formatDateTime(selectedSchool.created_at)"
              type="primary"
            >
              学校信息创建
            </el-timeline-item>
            <el-timeline-item
              v-if="selectedSchool.updated_at !== selectedSchool.created_at"
              timestamp="最后更新"
              :time="formatDateTime(selectedSchool.updated_at)"
              type="success"
            >
              学校信息更新
            </el-timeline-item>
          </el-timeline>
        </el-card>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="handleDetailDialogClose">关闭</el-button>
          <el-button type="primary" @click="editSchool(selectedSchool)">
            编辑学校
          </el-button>
          <el-button type="info" @click="exportSchoolDetail">
            导出详情
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- 学校编辑对话框 -->
    <el-dialog
      v-model="editDialogVisible"
      title="编辑学校"
      width="70%"
      :before-close="handleEditDialogClose"
    >
      <el-form
        v-if="editingSchool"
        ref="editFormRef"
        :model="editingSchool"
        :rules="editRules"
        label-width="120px"
        class="edit-form"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="学校名称" prop="name">
              <el-input v-model="editingSchool.name" placeholder="请输入学校名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="学校代码" prop="code">
              <el-input v-model="editingSchool.code" placeholder="请输入学校代码" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="学校类型" prop="school_type">
              <el-select v-model="editingSchool.school_type" placeholder="请选择学校类型" style="width: 100%">
                <el-option label="小学" value="primary" />
                <el-option label="初中" value="junior_high" />
                <el-option label="高中" value="senior_high" />
                <el-option label="九年一贯制" value="nine_year" />
                <el-option label="完全中学" value="complete_middle" />
                <el-option label="职业学校" value="vocational" />
                <el-option label="特殊教育学校" value="special_education" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="教育层次" prop="education_level">
              <el-select v-model="editingSchool.education_level" placeholder="请选择教育层次" style="width: 100%">
                <el-option label="学前教育" value="preschool" />
                <el-option label="小学教育" value="primary" />
                <el-option label="初中教育" value="junior_high" />
                <el-option label="高中教育" value="senior_high" />
                <el-option label="中等职业教育" value="vocational" />
                <el-option label="特殊教育" value="special" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="校长姓名" prop="principal_name">
              <el-input v-model="editingSchool.principal_name" placeholder="请输入校长姓名" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="校长电话" prop="principal_phone">
              <el-input v-model="editingSchool.principal_phone" placeholder="请输入校长电话" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人" prop="contact_person">
              <el-input v-model="editingSchool.contact_person" placeholder="请输入联系人" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话" prop="contact_phone">
              <el-input v-model="editingSchool.contact_phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="学校地址" prop="address">
          <el-input
            v-model="editingSchool.address"
            type="textarea"
            :rows="2"
            placeholder="请输入学校地址"
          />
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="学生人数">
              <el-input-number
                v-model="editingSchool.student_count"
                :min="0"
                placeholder="学生人数"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="校园面积">
              <el-input-number
                v-model="editingSchool.campus_area"
                :min="0"
                :precision="2"
                placeholder="平方米"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="建校年份">
              <el-input-number
                v-model="editingSchool.founded_year"
                :min="1900"
                :max="new Date().getFullYear()"
                placeholder="建校年份"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="学校描述">
          <el-input
            v-model="editingSchool.description"
            type="textarea"
            :rows="3"
            placeholder="请输入学校描述"
          />
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="editingSchool.status">
            <el-radio label="active">正常</el-radio>
            <el-radio label="inactive">停用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="handleEditDialogClose">取消</el-button>
          <el-button type="primary" @click="saveSchool" :loading="saving">
            保存
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Refresh, Download, Location, ArrowDown, ArrowUp, Document, DataAnalysis } from '@element-plus/icons-vue'
import { organizationApi } from '../../api/organization'

// 响应式数据
const loading = ref(false)
const exporting = ref(false)
const schools = ref([])
const organizationTree = ref([])
const selectedSchools = ref([])
const showAdvancedSearch = ref(false)

const pagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0
})

const filters = reactive({
  name: '',
  code: '',
  school_type: '',
  parent_id: null,
  education_level: '',
  principal_name: '',
  contact_phone: '',
  min_students: null,
  max_students: null,
  founded_year_range: null,
  status: ''
})

const treeProps = {
  children: 'children',
  label: 'name',
  value: 'id'
}

// 详情对话框
const detailDialogVisible = ref(false)
const selectedSchool = ref(null)

// 编辑对话框
const editDialogVisible = ref(false)
const editingSchool = ref(null)
const editFormRef = ref()
const saving = ref(false)

// 学校类型映射
const schoolTypeMap = {
  'primary': '小学',
  'junior_high': '初中',
  'senior_high': '高中',
  'nine_year': '九年一贯制',
  'complete_middle': '完全中学',
  'vocational': '职业学校',
  'special_education': '特殊教育学校'
}

// 教育层次映射
const educationLevelMap = {
  'preschool': '学前教育',
  'primary': '小学教育',
  'junior_high': '初中教育',
  'senior_high': '高中教育',
  'vocational': '中等职业教育',
  'special': '特殊教育'
}

// 编辑表单验证规则
const editRules = {
  name: [
    { required: true, message: '请输入学校名称', trigger: 'blur' },
    { min: 2, max: 100, message: '学校名称长度在 2 到 100 个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入学校代码', trigger: 'blur' },
    { min: 2, max: 50, message: '学校代码长度在 2 到 50 个字符', trigger: 'blur' }
  ],
  school_type: [
    { required: true, message: '请选择学校类型', trigger: 'change' }
  ],
  education_level: [
    { required: true, message: '请选择教育层次', trigger: 'change' }
  ],
  principal_name: [
    { required: true, message: '请输入校长姓名', trigger: 'blur' }
  ],
  principal_phone: [
    { required: true, message: '请输入校长电话', trigger: 'blur' },
    { pattern: /^[\d\-\+\(\)\s]{7,20}$/, message: '请输入正确的电话号码', trigger: 'blur' }
  ],
  contact_person: [
    { required: true, message: '请输入联系人', trigger: 'blur' }
  ],
  contact_phone: [
    { required: true, message: '请输入联系电话', trigger: 'blur' },
    { pattern: /^[\d\-\+\(\)\s]{7,20}$/, message: '请输入正确的电话号码', trigger: 'blur' }
  ],
  address: [
    { required: true, message: '请输入学校地址', trigger: 'blur' }
  ]
}

// 方法
const loadOrganizationTree = async () => {
  try {
    const response = await organizationApi.getTree()
    organizationTree.value = response.data.data
  } catch (error) {
    console.error('加载组织树失败:', error)
  }
}

const loadSchools = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      per_page: pagination.per_page,
      type: 'school', // 只查询学校类型的组织
      ...filters
    }
    
    const response = await organizationApi.getList(params)
    const data = response.data.data
    
    schools.value = data.data
    pagination.total = data.total
    pagination.current_page = data.current_page
    pagination.per_page = data.per_page
  } catch (error) {
    console.error('加载学校列表失败:', error)
    ElMessage.error('加载学校列表失败')
  } finally {
    loading.value = false
  }
}

const searchSchools = () => {
  pagination.current_page = 1
  loadSchools()
}

const resetFilters = () => {
  filters.name = ''
  filters.code = ''
  filters.school_type = ''
  filters.parent_id = null
  filters.education_level = ''
  filters.principal_name = ''
  filters.contact_phone = ''
  filters.min_students = null
  filters.max_students = null
  filters.founded_year_range = null
  filters.status = ''
  searchSchools()
}

const handleSelectionChange = (selection) => {
  selectedSchools.value = selection
}

const exportSchools = async () => {
  exporting.value = true
  try {
    const params = {
      ...filters,
      export: true,
      format: 'excel'
    }

    // 处理年份范围
    if (filters.founded_year_range && filters.founded_year_range.length === 2) {
      params.founded_year_start = filters.founded_year_range[0].getFullYear()
      params.founded_year_end = filters.founded_year_range[1].getFullYear()
    }

    const response = await organizationApi.exportSchools(params)

    // 创建下载链接
    const blob = new Blob([response.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `学校信息_${new Date().toISOString().split('T')[0]}.xlsx`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    ElMessage.success('导出成功')
  } catch (error) {
    console.error('导出失败:', error)
    ElMessage.error('导出失败')
  } finally {
    exporting.value = false
  }
}

const batchEdit = () => {
  if (selectedSchools.value.length === 0) {
    ElMessage.warning('请先选择要编辑的学校')
    return
  }
  ElMessage.info('批量编辑功能开发中...')
}

const batchExport = async () => {
  if (selectedSchools.value.length === 0) {
    ElMessage.warning('请先选择要导出的学校')
    return
  }

  try {
    const ids = selectedSchools.value.map(school => school.id)
    const response = await organizationApi.exportSchools({ ids, format: 'excel' })

    // 创建下载链接
    const blob = new Blob([response.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `选中学校信息_${new Date().toISOString().split('T')[0]}.xlsx`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    ElMessage.success('批量导出成功')
  } catch (error) {
    console.error('批量导出失败:', error)
    ElMessage.error('批量导出失败')
  }
}

const batchDelete = async () => {
  if (selectedSchools.value.length === 0) {
    ElMessage.warning('请先选择要删除的学校')
    return
  }

  try {
    await ElMessageBox.confirm(
      `确定要删除选中的 ${selectedSchools.value.length} 所学校吗？此操作不可恢复。`,
      '确认批量删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const ids = selectedSchools.value.map(school => school.id)
    await organizationApi.batchDelete(ids)

    ElMessage.success('批量删除成功')
    selectedSchools.value = []
    loadSchools()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('批量删除失败:', error)
      ElMessage.error('批量删除失败')
    }
  }
}

const saveSearchTemplate = () => {
  ElMessage.info('保存搜索模板功能开发中...')
}

const handleQuickAction = (command) => {
  switch (command) {
    case 'export':
      exportSchools()
      break
    case 'template':
      downloadTemplate()
      break
    case 'stats':
      showStats()
      break
    case 'refresh':
      loadSchools()
      break
    default:
      break
  }
}

const downloadTemplate = () => {
  // 创建学校信息模板
  const headers = [
    '学校名称', '学校代码', '学校类型', '教育层次', '校长姓名', '校长电话',
    '校长邮箱', '联系人', '联系电话', '学校地址', '学生人数', '校园面积',
    '建校年份', '学校描述'
  ]

  const sampleData = [
    [
      '示例小学', 'DEMO001', '小学', '小学教育', '张校长', '0311-88123456',
      'principal@demo.edu.cn', '李老师', '0311-88123457', '河北省石家庄市示例区示例街道123号',
      '500', '15000', '1985', '这是一所示例学校'
    ]
  ]

  // 生成CSV内容
  let csvContent = '\uFEFF' // BOM for UTF-8
  csvContent += headers.join(',') + '\n'
  sampleData.forEach(row => {
    csvContent += row.map(field => `"${field}"`).join(',') + '\n'
  })

  // 创建下载链接
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `学校信息导入模板_${new Date().toISOString().split('T')[0]}.csv`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)

  ElMessage.success('模板下载成功')
}

const showStats = () => {
  if (schools.value.length === 0) {
    ElMessage.warning('暂无数据可统计')
    return
  }

  // 计算统计信息
  const stats = {
    total: schools.value.length,
    byType: {},
    byLevel: {},
    totalStudents: 0,
    avgStudents: 0,
    activeCount: 0
  }

  schools.value.forEach(school => {
    // 按类型统计
    const type = getSchoolTypeText(school.school_type)
    stats.byType[type] = (stats.byType[type] || 0) + 1

    // 按教育层次统计
    const level = getEducationLevelText(school.education_level)
    stats.byLevel[level] = (stats.byLevel[level] || 0) + 1

    // 学生数统计
    if (school.student_count) {
      stats.totalStudents += school.student_count
    }

    // 状态统计
    if (school.status === 'active') {
      stats.activeCount++
    }
  })

  stats.avgStudents = Math.round(stats.totalStudents / stats.total)

  // 显示统计信息
  const typeStats = Object.entries(stats.byType).map(([type, count]) => `${type}: ${count}所`).join('，')
  const levelStats = Object.entries(stats.byLevel).map(([level, count]) => `${level}: ${count}所`).join('，')

  ElMessageBox.alert(
    `
    <div style="text-align: left;">
      <p><strong>总体统计：</strong></p>
      <p>学校总数：${stats.total}所</p>
      <p>正常状态：${stats.activeCount}所</p>
      <p>学生总数：${stats.totalStudents}人</p>
      <p>平均学生数：${stats.avgStudents}人/所</p>
      <br>
      <p><strong>按类型分布：</strong></p>
      <p>${typeStats}</p>
      <br>
      <p><strong>按教育层次分布：</strong></p>
      <p>${levelStats}</p>
    </div>
    `,
    '学校统计信息',
    {
      dangerouslyUseHTMLString: true,
      confirmButtonText: '确定'
    }
  )
}

const showAddDialog = () => {
  ElMessage.info('新增学校功能开发中...')
}

const viewSchool = async (school) => {
  try {
    const response = await organizationApi.getDetail(school.id)
    selectedSchool.value = response.data.data
    detailDialogVisible.value = true
  } catch (error) {
    console.error('获取学校详情失败:', error)
    ElMessage.error('获取学校详情失败')
  }
}

const editSchool = async (school) => {
  try {
    // 获取完整的学校信息
    const response = await organizationApi.getDetail(school.id)
    editingSchool.value = { ...response.data.data }
    editDialogVisible.value = true
  } catch (error) {
    console.error('获取学校信息失败:', error)
    ElMessage.error('获取学校信息失败')
  }
}

const handleEditDialogClose = () => {
  editDialogVisible.value = false
  editingSchool.value = null
  if (editFormRef.value) {
    editFormRef.value.resetFields()
  }
}

const saveSchool = async () => {
  if (!editFormRef.value) return

  try {
    await editFormRef.value.validate()
  } catch (error) {
    ElMessage.warning('请检查表单填写是否正确')
    return
  }

  saving.value = true
  try {
    await organizationApi.update(editingSchool.value.id, editingSchool.value)

    ElMessage.success('学校信息更新成功')
    handleEditDialogClose()

    // 刷新列表
    await loadSchools()

    // 如果详情对话框打开，也需要更新
    if (detailDialogVisible.value && selectedSchool.value?.id === editingSchool.value.id) {
      selectedSchool.value = { ...editingSchool.value }
    }

  } catch (error) {
    console.error('更新学校信息失败:', error)

    if (error.response?.status === 422) {
      // 处理验证错误
      const errors = error.response.data.errors
      if (errors) {
        const errorMessages = Object.values(errors).flat()
        ElMessage.error('数据验证失败：' + errorMessages.join('；'))
      } else {
        ElMessage.error('数据验证失败')
      }
    } else {
      ElMessage.error(error.response?.data?.message || '更新学校信息失败')
    }
  } finally {
    saving.value = false
  }
}

const deleteSchool = async (school) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除学校"${school.name}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    await organizationApi.delete(school.id)
    ElMessage.success('删除成功')
    loadSchools()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除学校失败:', error)
      ElMessage.error('删除学校失败')
    }
  }
}

const handleDetailDialogClose = () => {
  detailDialogVisible.value = false
  selectedSchool.value = null
}

const exportSchoolDetail = () => {
  if (!selectedSchool.value) return

  // 生成学校详情的文本内容
  const detailContent = generateSchoolDetailText(selectedSchool.value)

  // 创建下载链接
  const blob = new Blob([detailContent], { type: 'text/plain;charset=utf-8' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `学校详情_${selectedSchool.value.name}_${new Date().toISOString().split('T')[0]}.txt`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)

  ElMessage.success('学校详情导出成功')
}

const generateSchoolDetailText = (school) => {
  let content = `学校详情报告\n`
  content += `${'='.repeat(50)}\n\n`

  content += `基本信息:\n`
  content += `学校名称: ${school.name}\n`
  content += `学校代码: ${school.code}\n`
  content += `学校类型: ${getSchoolTypeText(school.school_type)}\n`
  content += `教育层次: ${getEducationLevelText(school.education_level)}\n`
  content += `所属组织: ${school.parent?.name || '无'}\n`
  content += `学校地址: ${school.address || '未填写'}\n`
  content += `学生人数: ${school.student_count || '未统计'}\n`
  content += `校园面积: ${school.campus_area ? school.campus_area + ' 平方米' : '未填写'}\n`
  content += `建校年份: ${school.founded_year || '未填写'}\n`
  content += `状态: ${school.status === 'active' ? '正常' : '停用'}\n\n`

  content += `联系信息:\n`
  content += `校长姓名: ${school.principal_name || '未填写'}\n`
  content += `校长电话: ${school.principal_phone || '未填写'}\n`
  content += `校长邮箱: ${school.principal_email || '未填写'}\n`
  content += `联系人: ${school.contact_person || '未填写'}\n`
  content += `联系电话: ${school.contact_phone || '未填写'}\n\n`

  if (school.longitude && school.latitude) {
    content += `地理位置:\n`
    content += `经度: ${school.longitude}\n`
    content += `纬度: ${school.latitude}\n\n`
  }

  if (school.extra_data) {
    content += `扩展信息:\n`
    if (school.extra_data.class_count) {
      content += `班级数: ${school.extra_data.class_count}\n`
    }
    if (school.extra_data.teacher_count) {
      content += `教师人数: ${school.extra_data.teacher_count}\n`
    }
    if (school.extra_data.facilities) {
      content += `设施设备: ${school.extra_data.facilities}\n`
    }
    if (school.extra_data.special_programs) {
      content += `特色项目: ${school.extra_data.special_programs}\n`
    }
    content += '\n'
  }

  content += `时间信息:\n`
  content += `创建时间: ${formatDateTime(school.created_at)}\n`
  content += `更新时间: ${formatDateTime(school.updated_at)}\n`

  return content
}

const getSchoolTypeText = (type) => {
  return schoolTypeMap[type] || type
}

const getEducationLevelText = (level) => {
  return educationLevelMap[level] || level
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '无'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 生命周期
onMounted(() => {
  loadOrganizationTree()
  loadSchools()
})
</script>

<style scoped>
.school-list {
  padding: 20px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.list-header h3 {
  margin: 0;
  color: #303133;
  font-size: 18px;
  font-weight: 600;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.filter-card,
.table-card {
  margin-bottom: 20px;
}

.filter-form {
  margin: 0;
}

.filter-form .el-form-item {
  margin-bottom: 0;
}

.pagination-wrapper {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

/* 学校详情样式 */
.school-detail {
  max-height: 70vh;
  overflow-y: auto;
}

.extra-info {
  margin-top: 20px;
}

.extra-info h4 {
  margin: 0 0 12px 0;
  color: #303133;
  font-size: 14px;
  font-weight: 600;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .school-list {
    padding: 10px;
  }

  .list-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-end;
  }

  .filter-form {
    display: block;
  }

  .filter-form .el-form-item {
    margin-bottom: 12px;
    display: block;
  }

  .filter-form .el-form-item:last-child {
    margin-bottom: 0;
  }
}

/* 筛选卡片样式 */
.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

/* 表格头部样式 */
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.table-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.selection-tag {
  margin-right: 8px;
}

/* 学校详情样式 */
.school-detail {
  max-height: 70vh;
  overflow-y: auto;
}

.detail-section {
  margin-bottom: 20px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.section-header span {
  font-weight: 600;
  color: #303133;
}

.detail-section h4 {
  margin: 0 0 16px 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
  border-bottom: 2px solid #e4e7ed;
  padding-bottom: 8px;
}

.map-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 150px;
  border: 2px dashed #dcdfe6;
  border-radius: 6px;
  background: #fafafa;
  color: #909399;
  text-align: center;
}

.map-placeholder p {
  margin: 4px 0;
  font-size: 14px;
}

/* 时间线样式 */
:deep(.el-timeline-item__timestamp) {
  color: #909399;
  font-size: 12px;
}

:deep(.el-timeline-item__content) {
  color: #303133;
  font-size: 14px;
}

/* 描述列表样式 */
:deep(.el-descriptions__content) {
  color: #303133;
  word-break: break-all;
}

:deep(.el-descriptions__label) {
  font-weight: 600;
}

/* 编辑表单样式 */
.edit-form {
  max-height: 60vh;
  overflow-y: auto;
  padding-right: 10px;
}

.edit-form .el-form-item {
  margin-bottom: 20px;
}

.edit-form .el-form-item__label {
  font-weight: 600;
  color: #303133;
}

.edit-form .el-input__inner,
.edit-form .el-textarea__inner {
  border-radius: 6px;
}

.edit-form .el-select {
  width: 100%;
}

/* 对话框样式优化 */
:deep(.el-dialog__header) {
  background: #f5f7fa;
  border-bottom: 1px solid #ebeef5;
  padding: 16px 20px;
}

:deep(.el-dialog__title) {
  font-weight: 600;
  color: #303133;
}

:deep(.el-dialog__body) {
  padding: 20px;
}

:deep(.el-dialog__footer) {
  border-top: 1px solid #ebeef5;
  padding: 16px 20px;
  background: #fafafa;
}

/* 表格样式调整 */
:deep(.el-table) {
  font-size: 13px;
}

:deep(.el-table .cell) {
  padding: 8px 12px;
}

/* 描述列表样式 */
:deep(.el-descriptions) {
  margin-bottom: 16px;
}

:deep(.el-descriptions__label) {
  font-weight: 600;
  color: #606266;
}

:deep(.el-descriptions__content) {
  color: #303133;
}

/* 对话框样式 */
:deep(.el-dialog) {
  border-radius: 8px;
}

:deep(.el-dialog__header) {
  padding: 20px 20px 10px;
  border-bottom: 1px solid #ebeef5;
}

:deep(.el-dialog__body) {
  padding: 20px;
}
</style>
