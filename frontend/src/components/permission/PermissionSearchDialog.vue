<template>
  <el-dialog
    v-model="visible"
    title="权限查询"
    width="1000px"
    :close-on-click-modal="false"
  >
    <div class="permission-search">
      <!-- 搜索条件 -->
      <el-card class="search-conditions">
        <template #header>
          <span>搜索条件</span>
        </template>
        
        <el-form :model="searchForm" :inline="true" label-width="80px">
          <el-form-item label="查询类型">
            <el-radio-group v-model="searchForm.queryType" @change="handleQueryTypeChange">
              <el-radio-button label="user">用户权限</el-radio-button>
              <el-radio-button label="permission">权限分布</el-radio-button>
              <el-radio-button label="organization">组织权限</el-radio-button>
            </el-radio-group>
          </el-form-item>
          
          <el-form-item label="组织机构">
            <el-cascader
              v-model="searchForm.organizationPath"
              :options="organizationTree"
              :props="cascaderProps"
              placeholder="选择组织机构"
              clearable
              style="width: 200px"
            />
          </el-form-item>
          
          <el-form-item v-if="searchForm.queryType === 'user'" label="用户">
            <el-select
              v-model="searchForm.userId"
              placeholder="选择用户"
              filterable
              remote
              :remote-method="searchUsers"
              clearable
              style="width: 200px"
            >
              <el-option
                v-for="user in userOptions"
                :key="user.id"
                :label="user.name"
                :value="user.id"
              />
            </el-select>
          </el-form-item>
          
          <el-form-item v-if="searchForm.queryType === 'permission'" label="权限">
            <el-select
              v-model="searchForm.permissionId"
              placeholder="选择权限"
              filterable
              clearable
              style="width: 200px"
            >
              <el-option
                v-for="permission in permissionOptions"
                :key="permission.id"
                :label="permission.display_name"
                :value="permission.id"
              />
            </el-select>
          </el-form-item>
          
          <el-form-item label="角色">
            <el-select
              v-model="searchForm.roleId"
              placeholder="选择角色"
              clearable
              style="width: 150px"
            >
              <el-option
                v-for="role in roleOptions"
                :key="role.id"
                :label="role.display_name"
                :value="role.id"
              />
            </el-select>
          </el-form-item>
          
          <el-form-item>
            <el-button type="primary" @click="executeSearch" :loading="searching">
              <el-icon><Search /></el-icon>
              查询
            </el-button>
            <el-button @click="resetSearch">重置</el-button>
            <el-button type="success" @click="exportResults" :disabled="!searchResults.length">
              <el-icon><Download /></el-icon>
              导出
            </el-button>
          </el-form-item>
        </el-form>
      </el-card>

      <!-- 搜索结果 -->
      <el-card class="search-results" v-if="searchResults.length">
        <template #header>
          <div class="results-header">
            <span>搜索结果 ({{ searchResults.length }} 条)</span>
            <div class="result-actions">
              <el-button type="text" @click="showChart = !showChart">
                <el-icon><DataAnalysis /></el-icon>
                {{ showChart ? '隐藏图表' : '显示图表' }}
              </el-button>
            </div>
          </div>
        </template>
        
        <!-- 图表展示 -->
        <div v-if="showChart" class="chart-container">
          <div ref="chartRef" style="width: 100%; height: 300px;"></div>
        </div>
        
        <!-- 表格展示 -->
        <div class="table-container">
          <!-- 用户权限查询结果 -->
          <el-table
            v-if="searchForm.queryType === 'user'"
            :data="searchResults"
            stripe
            border
          >
            <el-table-column prop="permission_name" label="权限名称" width="200" />
            <el-table-column prop="permission_display_name" label="权限描述" />
            <el-table-column prop="source" label="权限来源" width="120">
              <template #default="scope">
                <el-tag :type="getSourceType(scope.row.source)">
                  {{ scope.row.source_label }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="organization_name" label="所属组织" width="150" />
            <el-table-column prop="granted_at" label="分配时间" width="150" />
            <el-table-column prop="expires_at" label="过期时间" width="150">
              <template #default="scope">
                <span v-if="scope.row.expires_at">
                  {{ scope.row.expires_at }}
                </span>
                <el-tag v-else type="success" size="small">永久</el-tag>
              </template>
            </el-table-column>
          </el-table>
          
          <!-- 权限分布查询结果 -->
          <el-table
            v-if="searchForm.queryType === 'permission'"
            :data="searchResults"
            stripe
            border
          >
            <el-table-column prop="user_name" label="用户名称" width="120" />
            <el-table-column prop="organization_name" label="所属组织" width="150" />
            <el-table-column prop="role_name" label="用户角色" width="120" />
            <el-table-column prop="source" label="权限来源" width="120">
              <template #default="scope">
                <el-tag :type="getSourceType(scope.row.source)">
                  {{ scope.row.source_label }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="granted_at" label="分配时间" width="150" />
            <el-table-column label="操作" width="100">
              <template #default="scope">
                <el-button type="text" size="small" @click="viewUserDetail(scope.row)">
                  查看详情
                </el-button>
              </template>
            </el-table-column>
          </el-table>
          
          <!-- 组织权限查询结果 -->
          <el-table
            v-if="searchForm.queryType === 'organization'"
            :data="searchResults"
            stripe
            border
          >
            <el-table-column prop="organization_name" label="组织名称" width="200" />
            <el-table-column prop="organization_type" label="组织类型" width="100" />
            <el-table-column prop="permission_count" label="权限总数" width="100">
              <template #default="scope">
                <el-tag type="primary">{{ scope.row.permission_count }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="direct_count" label="直接权限" width="100">
              <template #default="scope">
                <el-tag type="success">{{ scope.row.direct_count }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="inherited_count" label="继承权限" width="100">
              <template #default="scope">
                <el-tag type="info">{{ scope.row.inherited_count }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="user_count" label="用户数量" width="100" />
            <el-table-column label="操作" width="120">
              <template #default="scope">
                <el-button type="text" size="small" @click="viewOrgDetail(scope.row)">
                  查看详情
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </el-card>
      
      <!-- 空状态 -->
      <el-empty v-else-if="hasSearched" description="未找到相关权限信息" />
    </div>

    <template #footer>
      <el-button @click="visible = false">关闭</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Download, DataAnalysis } from '@element-plus/icons-vue'
import * as echarts from 'echarts'

// Props & Emits
const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

// 响应式数据
const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const searching = ref(false)
const hasSearched = ref(false)
const showChart = ref(false)
const chartRef = ref()
let chartInstance = null

// 搜索表单
const searchForm = reactive({
  queryType: 'user',
  organizationPath: [],
  userId: null,
  permissionId: null,
  roleId: null
})

// 数据
const searchResults = ref([])
const organizationTree = ref([])
const userOptions = ref([])
const permissionOptions = ref([])
const roleOptions = ref([])

// 级联选择器配置
const cascaderProps = {
  value: 'id',
  label: 'name',
  children: 'children',
  emitPath: false
}

// 方法
const handleQueryTypeChange = () => {
  // 重置相关字段
  searchForm.userId = null
  searchForm.permissionId = null
  searchResults.value = []
  hasSearched.value = false
}

const searchUsers = async (query) => {
  if (query) {
    // 模拟用户搜索
    userOptions.value = [
      { id: 1, name: '张校长' },
      { id: 2, name: '李老师' }
    ].filter(user => user.name.includes(query))
  }
}

const executeSearch = async () => {
  try {
    searching.value = true
    hasSearched.value = true
    
    // 模拟API调用
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // 模拟搜索结果
    if (searchForm.queryType === 'user') {
      searchResults.value = [
        {
          permission_name: 'user.view',
          permission_display_name: '查看用户',
          source: 'direct',
          source_label: '直接分配',
          organization_name: '东城小学',
          granted_at: '2025-06-28 10:00:00',
          expires_at: null
        },
        {
          permission_name: 'user.create',
          permission_display_name: '创建用户',
          source: 'role',
          source_label: '角色权限',
          organization_name: '东城小学',
          granted_at: '2025-06-28 09:00:00',
          expires_at: '2025-12-31 23:59:59'
        }
      ]
    } else if (searchForm.queryType === 'permission') {
      searchResults.value = [
        {
          user_name: '张校长',
          organization_name: '东城小学',
          role_name: '校长',
          source: 'role',
          source_label: '角色权限',
          granted_at: '2025-06-28 10:00:00'
        }
      ]
    } else {
      searchResults.value = [
        {
          organization_name: '东城小学',
          organization_type: '学校',
          permission_count: 25,
          direct_count: 8,
          inherited_count: 17,
          user_count: 45
        }
      ]
    }
    
    // 如果显示图表，则渲染图表
    if (showChart.value) {
      nextTick(() => {
        renderChart()
      })
    }
    
  } catch (error) {
    ElMessage.error('查询失败')
  } finally {
    searching.value = false
  }
}

const resetSearch = () => {
  Object.assign(searchForm, {
    queryType: 'user',
    organizationPath: [],
    userId: null,
    permissionId: null,
    roleId: null
  })
  searchResults.value = []
  hasSearched.value = false
  showChart.value = false
}

const exportResults = () => {
  // 导出搜索结果
  ElMessage.success('导出功能开发中')
}

const getSourceType = (source) => {
  const types = {
    direct: 'primary',
    role: 'success',
    inherited: 'info',
    template: 'warning'
  }
  return types[source] || 'info'
}

const viewUserDetail = (row) => {
  // 查看用户详情
  console.log('查看用户详情:', row)
}

const viewOrgDetail = (row) => {
  // 查看组织详情
  console.log('查看组织详情:', row)
}

const renderChart = () => {
  if (!chartRef.value) return
  
  if (chartInstance) {
    chartInstance.dispose()
  }
  
  chartInstance = echarts.init(chartRef.value)
  
  let option = {}
  
  if (searchForm.queryType === 'user') {
    // 用户权限来源分布饼图
    const sourceData = searchResults.value.reduce((acc, item) => {
      acc[item.source_label] = (acc[item.source_label] || 0) + 1
      return acc
    }, {})
    
    option = {
      title: { text: '权限来源分布' },
      tooltip: { trigger: 'item' },
      series: [{
        type: 'pie',
        data: Object.entries(sourceData).map(([name, value]) => ({ name, value }))
      }]
    }
  } else if (searchForm.queryType === 'organization') {
    // 组织权限统计柱状图
    option = {
      title: { text: '组织权限统计' },
      tooltip: { trigger: 'axis' },
      xAxis: {
        type: 'category',
        data: searchResults.value.map(item => item.organization_name)
      },
      yAxis: { type: 'value' },
      series: [
        {
          name: '直接权限',
          type: 'bar',
          data: searchResults.value.map(item => item.direct_count)
        },
        {
          name: '继承权限',
          type: 'bar',
          data: searchResults.value.map(item => item.inherited_count)
        }
      ]
    }
  }
  
  chartInstance.setOption(option)
}

// 监听图表显示状态
watch(showChart, (val) => {
  if (val && searchResults.value.length) {
    nextTick(() => {
      renderChart()
    })
  }
})

// 监听对话框打开
watch(visible, (val) => {
  if (val) {
    loadData()
  } else {
    // 清理图表实例
    if (chartInstance) {
      chartInstance.dispose()
      chartInstance = null
    }
  }
})

const loadData = async () => {
  // 加载基础数据
  organizationTree.value = [
    {
      id: 1,
      name: '河北省',
      children: [
        {
          id: 2,
          name: '石家庄市',
          children: [
            {
              id: 3,
              name: '藁城区',
              children: [
                { id: 4, name: '廉州学区' }
              ]
            }
          ]
        }
      ]
    }
  ]
  
  permissionOptions.value = [
    { id: 1, display_name: '查看用户' },
    { id: 2, display_name: '创建用户' },
    { id: 3, display_name: '编辑用户' }
  ]
  
  roleOptions.value = [
    { id: 1, display_name: '系统管理员' },
    { id: 2, display_name: '学区管理员' },
    { id: 3, display_name: '校长' },
    { id: 4, display_name: '教师' }
  ]
}
</script>

<style scoped>
.permission-search {
  padding: 20px 0;
}

.search-conditions {
  margin-bottom: 20px;
}

.search-results {
  margin-bottom: 20px;
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.result-actions {
  display: flex;
  gap: 8px;
}

.chart-container {
  margin-bottom: 20px;
  border-bottom: 1px solid #ebeef5;
  padding-bottom: 20px;
}

.table-container {
  max-height: 400px;
  overflow-y: auto;
}
</style>
