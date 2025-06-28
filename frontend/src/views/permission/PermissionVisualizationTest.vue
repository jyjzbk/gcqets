<template>
  <div class="permission-visualization-test">
    <div class="page-header">
      <h1>权限可视化管理 - 测试版</h1>
      <p class="page-description">
        简化版本，用于测试API连接
      </p>
    </div>

    <el-tabs v-model="activeTab" type="card" class="permission-tabs">
      <!-- 权限继承关系 -->
      <el-tab-pane label="权限继承关系" name="inheritance">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>权限继承关系树</span>
              <el-button type="primary" size="small" @click="loadInheritanceTree">
                <el-icon><Refresh /></el-icon>
                刷新
              </el-button>
            </div>
          </template>

          <div v-if="loading.inheritance" class="loading">
            <el-icon class="is-loading"><Loading /></el-icon>
            加载中...
          </div>
          
          <div v-else-if="inheritanceData.length" class="inheritance-tree">
            <template v-for="item in inheritanceData" :key="item.id">
              <div class="tree-node">
                <div class="node-content">
                  <el-icon><OfficeBuilding /></el-icon>
                  <span>{{ item.name }}</span>
                  <el-tag size="small" :type="getOrgTypeColor(item.type)">{{ item.type }}</el-tag>
                  <span class="permissions-count">权限: {{ item.permissions_count }}</span>
                  <span class="users-count">用户: {{ item.users_count }}</span>
                </div>
              </div>
              <!-- 递归显示子节点 -->
              <div v-if="item.children && item.children.length" class="tree-children" style="margin-left: 20px;">
                <template v-for="child in item.children" :key="child.id">
                  <div class="tree-node">
                    <div class="node-content">
                      <el-icon><OfficeBuilding /></el-icon>
                      <span>{{ child.name }}</span>
                      <el-tag size="small" :type="getOrgTypeColor(child.type)">{{ child.type }}</el-tag>
                      <span class="permissions-count">权限: {{ child.permissions_count }}</span>
                      <span class="users-count">用户: {{ child.users_count }}</span>
                    </div>
                  </div>
                  <!-- 第三层 -->
                  <div v-if="child.children && child.children.length" class="tree-children" style="margin-left: 40px;">
                    <template v-for="grandchild in child.children" :key="grandchild.id">
                      <div class="tree-node">
                        <div class="node-content">
                          <el-icon><OfficeBuilding /></el-icon>
                          <span>{{ grandchild.name }}</span>
                          <el-tag size="small" :type="getOrgTypeColor(grandchild.type)">{{ grandchild.type }}</el-tag>
                          <span class="permissions-count">权限: {{ grandchild.permissions_count }}</span>
                          <span class="users-count">用户: {{ grandchild.users_count }}</span>
                        </div>
                      </div>
                      <!-- 第四层 -->
                      <div v-if="grandchild.children && grandchild.children.length" class="tree-children" style="margin-left: 60px;">
                        <template v-for="greatgrandchild in grandchild.children" :key="greatgrandchild.id">
                          <div class="tree-node">
                            <div class="node-content">
                              <el-icon><OfficeBuilding /></el-icon>
                              <span>{{ greatgrandchild.name }}</span>
                              <el-tag size="small" :type="getOrgTypeColor(greatgrandchild.type)">{{ greatgrandchild.type }}</el-tag>
                              <span class="permissions-count">权限: {{ greatgrandchild.permissions_count }}</span>
                              <span class="users-count">用户: {{ greatgrandchild.users_count }}</span>
                            </div>
                          </div>
                          <!-- 第五层 -->
                          <div v-if="greatgrandchild.children && greatgrandchild.children.length" class="tree-children" style="margin-left: 80px;">
                            <div v-for="school in greatgrandchild.children" :key="school.id" class="tree-node">
                              <div class="node-content">
                                <el-icon><OfficeBuilding /></el-icon>
                                <span>{{ school.name }}</span>
                                <el-tag size="small" :type="getOrgTypeColor(school.type)">{{ school.type }}</el-tag>
                                <span class="permissions-count">权限: {{ school.permissions_count }}</span>
                                <span class="users-count">用户: {{ school.users_count }}</span>
                              </div>
                            </div>
                          </div>
                        </template>
                      </div>
                    </template>
                  </div>
                </template>
              </div>
            </template>
          </div>
          
          <el-empty v-else description="暂无数据" />
        </el-card>
      </el-tab-pane>

      <!-- 权限矩阵 -->
      <el-tab-pane label="权限矩阵" name="matrix">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>用户权限矩阵</span>
              <el-button type="primary" size="small" @click="loadPermissionMatrix">
                <el-icon><Refresh /></el-icon>
                刷新
              </el-button>
            </div>
          </template>

          <div v-if="loading.matrix" class="loading">
            <el-icon class="is-loading"><Loading /></el-icon>
            加载中...
          </div>
          
          <el-table v-else :data="matrixData" border style="width: 100%">
            <el-table-column prop="user_name" label="用户" width="120" />
            <el-table-column prop="organization" label="所属组织" width="150" />
            <el-table-column label="权限数量" width="100">
              <template #default="scope">
                <el-tag type="primary">{{ getPermissionCount(scope.row) }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="权限详情">
              <template #default="scope">
                <div class="permission-tags">
                  <el-tag 
                    v-for="(perm, key) in scope.row.permissions" 
                    :key="key"
                    :type="perm.has_permission ? 'success' : 'info'"
                    size="small"
                    class="permission-tag"
                  >
                    {{ perm.permission_display_name }}
                  </el-tag>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>

      <!-- 审计日志 -->
      <el-tab-pane label="审计日志" name="audit">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>权限操作审计日志</span>
              <el-button type="primary" size="small" @click="loadAuditLogs">
                <el-icon><Refresh /></el-icon>
                刷新
              </el-button>
            </div>
          </template>

          <div v-if="loading.audit" class="loading">
            <el-icon class="is-loading"><Loading /></el-icon>
            加载中...
          </div>
          
          <el-table v-else :data="auditData" border style="width: 100%">
            <el-table-column prop="created_at" label="时间" width="150" />
            <el-table-column prop="action" label="操作" width="100">
              <template #default="scope">
                <el-tag :type="getActionType(scope.row.action)">
                  {{ scope.row.action }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="target_name" label="目标用户" width="120" />
            <el-table-column prop="reason" label="操作原因" />
            <el-table-column prop="status" label="状态" width="80">
              <template #default="scope">
                <el-tag :type="scope.row.status === 'success' ? 'success' : 'danger'" size="small">
                  {{ scope.row.status }}
                </el-tag>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>

      <!-- 统计分析 -->
      <el-tab-pane label="统计分析" name="stats">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>权限统计分析</span>
              <el-button type="primary" size="small" @click="loadStats">
                <el-icon><Refresh /></el-icon>
                刷新
              </el-button>
            </div>
          </template>

          <div v-if="loading.stats" class="loading">
            <el-icon class="is-loading"><Loading /></el-icon>
            加载中...
          </div>
          
          <div v-else class="stats-grid">
            <div class="stat-card">
              <div class="stat-number">{{ statsData.total_users }}</div>
              <div class="stat-label">总用户数</div>
            </div>
            <div class="stat-card">
              <div class="stat-number">{{ statsData.total_permissions }}</div>
              <div class="stat-label">总权限数</div>
            </div>
            <div class="stat-card">
              <div class="stat-number">{{ statsData.active_permissions }}</div>
              <div class="stat-label">活跃权限</div>
            </div>
            <div class="stat-card">
              <div class="stat-number">{{ statsData.expired_permissions }}</div>
              <div class="stat-label">过期权限</div>
            </div>
          </div>
        </el-card>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import {
  Refresh,
  OfficeBuilding,
  Loading
} from '@element-plus/icons-vue'
import { permissionManagementApi } from '../../api/permissionManagement'

// 响应式数据
const activeTab = ref('inheritance')

const loading = reactive({
  inheritance: false,
  matrix: false,
  audit: false,
  stats: false
})

const inheritanceData = ref([])
const matrixData = ref([])
const auditData = ref([])
const statsData = reactive({
  total_users: 0,
  total_permissions: 0,
  total_roles: 0,
  active_permissions: 0,
  expired_permissions: 0
})

// 方法
const loadInheritanceTree = async () => {
  loading.inheritance = true
  try {
    const response = await permissionManagementApi.getInheritanceTree()
    console.log('API响应:', response)
    if (response.data && response.data.data) {
      inheritanceData.value = Array.isArray(response.data.data) ? response.data.data : [response.data.data]
    }
    ElMessage.success('权限继承关系加载成功')
  } catch (error) {
    console.error('加载权限继承关系失败:', error)
    ElMessage.error('加载权限继承关系失败')
  } finally {
    loading.inheritance = false
  }
}

const loadPermissionMatrix = async () => {
  loading.matrix = true
  try {
    const response = await permissionManagementApi.getPermissionMatrix()
    console.log('权限矩阵API响应:', response)
    if (response.data && response.data.data && response.data.data.matrix) {
      matrixData.value = response.data.data.matrix
    }
    ElMessage.success('权限矩阵加载成功')
  } catch (error) {
    console.error('加载权限矩阵失败:', error)
    ElMessage.error('加载权限矩阵失败')
  } finally {
    loading.matrix = false
  }
}

const loadAuditLogs = async () => {
  loading.audit = true
  try {
    const response = await permissionManagementApi.getAuditLogs()
    console.log('审计日志API响应:', response)
    if (response.data && response.data.data && response.data.data.data) {
      auditData.value = response.data.data.data
    }
    ElMessage.success('审计日志加载成功')
  } catch (error) {
    console.error('加载审计日志失败:', error)
    ElMessage.error('加载审计日志失败')
  } finally {
    loading.audit = false
  }
}

const loadStats = async () => {
  loading.stats = true
  try {
    const response = await permissionManagementApi.getPermissionStats()
    console.log('统计数据API响应:', response)
    if (response.data && response.data.data) {
      Object.assign(statsData, response.data.data)
    }
    ElMessage.success('统计数据加载成功')
  } catch (error) {
    console.error('加载统计数据失败:', error)
    ElMessage.error('加载统计数据失败')
  } finally {
    loading.stats = false
  }
}

const getOrgTypeColor = (type) => {
  const colors = {
    province: 'danger',
    city: 'warning',
    district: 'primary',
    zone: 'success',
    school: 'info'
  }
  return colors[type] || 'info'
}

const getPermissionCount = (row) => {
  if (!row.permissions) return 0
  return Object.values(row.permissions).filter(p => p.has_permission).length
}

const getActionType = (action) => {
  const types = {
    grant: 'success',
    revoke: 'danger',
    update: 'warning'
  }
  return types[action] || 'info'
}

onMounted(() => {
  ElMessage.success('权限可视化管理测试版已加载')
})
</script>

<style scoped>
.permission-visualization-test {
  padding: 20px;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0 0 8px 0;
  color: #303133;
}

.page-description {
  margin: 0;
  color: #606266;
  font-size: 14px;
}

.permission-tabs {
  margin-top: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #909399;
}

.inheritance-tree {
  max-height: 400px;
  overflow-y: auto;
}

.tree-node {
  margin-bottom: 8px;
  padding: 8px 12px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  background: #fafafa;
  transition: all 0.3s ease;
}

.tree-node:hover {
  background: #f0f9ff;
  border-color: #409eff;
}

.tree-children {
  border-left: 2px solid #e4e7ed;
  padding-left: 16px;
  margin-top: 8px;
}

.node-content {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
}

.permissions-count,
.users-count {
  font-size: 12px;
  color: #909399;
}

.permission-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.permission-tag {
  margin-bottom: 4px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  text-align: center;
  padding: 20px;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  background: #fafafa;
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #409eff;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}
</style>
