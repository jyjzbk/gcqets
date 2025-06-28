<template>
  <div class="education-permission-manager">
    <!-- 页面头部 -->
    <div class="page-header">
      <h1>教育权限管理</h1>
      <p class="page-description">
        简化的教育管理权限分配，专为学区、学校用户管理设计
      </p>
    </div>

    <!-- 快速操作面板 -->
    <el-row :gutter="20" class="quick-actions">
      <el-col :span="6">
        <el-card class="action-card" @click="showUserWizard = true">
          <div class="action-content">
            <el-icon class="action-icon"><UserFilled /></el-icon>
            <h3>创建用户</h3>
            <p>快速创建学区/学校用户并分配权限</p>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="action-card" @click="showOrgManager = true">
          <div class="action-content">
            <el-icon class="action-icon"><OfficeBuilding /></el-icon>
            <h3>组织权限</h3>
            <p>管理学区、学校的组织权限</p>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="action-card" @click="showBatchAssign = true">
          <div class="action-content">
            <el-icon class="action-icon"><Grid /></el-icon>
            <h3>批量分配</h3>
            <p>批量为多个用户分配相同权限</p>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="action-card" @click="showPermissionSearch = true">
          <div class="action-content">
            <el-icon class="action-icon"><Search /></el-icon>
            <h3>权限查询</h3>
            <p>查询用户权限和组织权限分布</p>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 权限概览 -->
    <el-card class="overview-card">
      <template #header>
        <div class="card-header">
          <span>权限概览</span>
          <el-button type="text" @click="refreshOverview">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </template>
      
      <el-row :gutter="20">
        <el-col :span="6">
          <div class="stat-item">
            <div class="stat-number">{{ stats.totalUsers }}</div>
            <div class="stat-label">总用户数</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-item">
            <div class="stat-number">{{ stats.districtUsers }}</div>
            <div class="stat-label">学区用户</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-item">
            <div class="stat-number">{{ stats.schoolUsers }}</div>
            <div class="stat-label">学校用户</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-item">
            <div class="stat-number">{{ stats.pendingPermissions }}</div>
            <div class="stat-label">待处理权限</div>
          </div>
        </el-col>
      </el-row>
    </el-card>

    <!-- 最近操作 -->
    <el-card class="recent-actions">
      <template #header>
        <div class="card-header">
          <span>最近权限操作</span>
          <el-button type="text" @click="viewAllAudits">
            查看全部
          </el-button>
        </div>
      </template>
      
      <el-table :data="recentActions" style="width: 100%">
        <el-table-column prop="time" label="时间" width="120" />
        <el-table-column prop="operator" label="操作人" width="100" />
        <el-table-column prop="action" label="操作" width="100">
          <template #default="scope">
            <el-tag :type="getActionType(scope.row.action)">
              {{ scope.row.action }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="target" label="目标" />
        <el-table-column prop="organization" label="组织" width="150" />
        <el-table-column label="操作" width="100">
          <template #default="scope">
            <el-button type="text" size="small" @click="viewActionDetail(scope.row)">
              详情
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 用户创建向导对话框 -->
    <UserPermissionWizard 
      v-model="showUserWizard"
      @success="handleWizardSuccess"
    />

    <!-- 组织权限管理对话框 -->
    <OrganizationPermissionManager 
      v-model="showOrgManager"
      @success="refreshOverview"
    />

    <!-- 批量权限分配对话框 -->
    <BatchPermissionAssign 
      v-model="showBatchAssign"
      @success="refreshOverview"
    />

    <!-- 权限搜索对话框 -->
    <PermissionSearchDialog 
      v-model="showPermissionSearch"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import {
  UserFilled,
  OfficeBuilding,
  Grid,
  Search,
  Refresh
} from '@element-plus/icons-vue'

// 导入子组件
import UserPermissionWizard from '../../components/permission/UserPermissionWizard.vue'
import OrganizationPermissionManager from '../../components/permission/OrganizationPermissionManager.vue'
import BatchPermissionAssign from '../../components/permission/BatchPermissionAssign.vue'
import PermissionSearchDialog from '../../components/permission/PermissionSearchDialog.vue'

// 响应式数据
const showUserWizard = ref(false)
const showOrgManager = ref(false)
const showBatchAssign = ref(false)
const showPermissionSearch = ref(false)

// 统计数据
const stats = reactive({
  totalUsers: 0,
  districtUsers: 0,
  schoolUsers: 0,
  pendingPermissions: 0
})

// 最近操作
const recentActions = ref([])

// 方法
const refreshOverview = async () => {
  try {
    // 获取统计数据
    // const response = await permissionApi.getStats()
    // Object.assign(stats, response.data)
    
    // 模拟数据
    Object.assign(stats, {
      totalUsers: 156,
      districtUsers: 12,
      schoolUsers: 144,
      pendingPermissions: 3
    })
    
    // 获取最近操作
    recentActions.value = [
      {
        time: '10:30',
        operator: '张管理员',
        action: '分配权限',
        target: '李校长',
        organization: '东城小学'
      },
      {
        time: '09:15',
        operator: '王主任',
        action: '创建用户',
        target: '赵老师',
        organization: '廉州学区'
      }
    ]
  } catch (error) {
    ElMessage.error('获取数据失败')
  }
}

const getActionType = (action) => {
  const types = {
    '分配权限': 'success',
    '撤销权限': 'warning',
    '创建用户': 'primary',
    '删除用户': 'danger'
  }
  return types[action] || 'info'
}

const handleWizardSuccess = () => {
  ElMessage.success('用户创建成功')
  refreshOverview()
}

const viewActionDetail = (row) => {
  // 查看操作详情
  console.log('查看详情:', row)
}

const viewAllAudits = () => {
  // 跳转到完整的审计页面
  console.log('查看全部审计')
}

onMounted(() => {
  refreshOverview()
})
</script>

<style scoped>
.education-permission-manager {
  padding: 20px;
  background: #f5f7fa;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0 0 8px 0;
  color: #303133;
  font-size: 24px;
  font-weight: 600;
}

.page-description {
  margin: 0;
  color: #606266;
  font-size: 14px;
}

.quick-actions {
  margin-bottom: 24px;
}

.action-card {
  cursor: pointer;
  transition: all 0.3s;
  min-height: 140px;
  height: auto;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.action-content {
  text-align: center;
  padding: 20px 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.action-icon {
  font-size: 32px;
  color: #409eff;
  margin-bottom: 12px;
}

.action-content h3 {
  margin: 8px 0 8px 0;
  font-size: 16px;
  color: #303133;
  font-weight: 600;
}

.action-content p {
  margin: 0;
  font-size: 13px;
  color: #909399;
  line-height: 1.4;
}

.overview-card,
.recent-actions {
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-item {
  text-align: center;
  padding: 16px;
}

.stat-number {
  font-size: 28px;
  font-weight: 600;
  color: #409eff;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}
</style>
