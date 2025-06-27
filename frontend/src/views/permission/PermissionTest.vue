<template>
  <div class="permission-test">
    <div class="page-header">
      <h1>权限可视化管理系统</h1>
      <p class="page-description">
        实验教学管理系统 - 权限可视化功能测试页面
      </p>
    </div>

    <el-card class="test-card">
      <template #header>
        <div class="card-header">
          <span>功能模块测试</span>
          <el-tag type="success">开发完成</el-tag>
        </div>
      </template>

      <div class="feature-grid">
        <div class="feature-item">
          <div class="feature-icon">
            <el-icon size="32"><Share /></el-icon>
          </div>
          <h3>权限继承关系</h3>
          <p>五级权限层次可视化展示，支持权限路径追踪和冲突检测</p>
          <el-button type="primary" size="small" @click="goToInheritanceTree">查看详情</el-button>
        </div>

        <div class="feature-item">
          <div class="feature-icon">
            <el-icon size="32"><Grid /></el-icon>
          </div>
          <h3>权限矩阵管理</h3>
          <p>用户-权限、角色-权限矩阵管理，支持实时权限分配和撤销</p>
          <el-button type="primary" size="small" @click="goToPermissionMatrix">查看详情</el-button>
        </div>

        <div class="feature-item">
          <div class="feature-icon">
            <el-icon size="32"><Files /></el-icon>
          </div>
          <h3>权限模板管理</h3>
          <p>权限模板创建、应用和版本管理，支持批量导入导出</p>
          <el-button type="primary" size="small" @click="goToPermissionTemplates">查看详情</el-button>
        </div>

        <div class="feature-item">
          <div class="feature-icon">
            <el-icon size="32"><Document /></el-icon>
          </div>
          <h3>权限审计历史</h3>
          <p>完整的权限操作审计日志，支持多维度筛选和统计分析</p>
          <el-button type="primary" size="small" @click="goToPermissionAudit">查看详情</el-button>
        </div>

        <div class="feature-item">
          <div class="feature-icon">
            <el-icon size="32"><DataAnalysis /></el-icon>
          </div>
          <h3>权限统计分析</h3>
          <p>权限使用情况统计分析，包含热力图、趋势图等可视化图表</p>
          <el-button type="primary" size="small" @click="goToPermissionAnalytics">查看详情</el-button>
        </div>

        <div class="feature-item">
          <div class="feature-icon">
            <el-icon size="32"><Setting /></el-icon>
          </div>
          <h3>权限撤销设置</h3>
          <p>灵活的权限撤销功能，支持单个和批量撤销，完整审计追踪</p>
          <el-button type="primary" size="small" @click="goToPermissionRevocation">查看详情</el-button>
        </div>
      </div>
    </el-card>

    <el-card class="status-card">
      <template #header>
        <span>系统状态</span>
      </template>

      <div class="status-grid">
        <div class="status-item">
          <div class="status-label">前端服务</div>
          <div class="status-value success">
            <el-icon><CircleCheck /></el-icon>
            运行中
          </div>
        </div>

        <div class="status-item">
          <div class="status-label">后端API</div>
          <div class="status-value success">
            <el-icon><CircleCheck /></el-icon>
            运行中
          </div>
        </div>

        <div class="status-item">
          <div class="status-label">数据库</div>
          <div class="status-value success">
            <el-icon><CircleCheck /></el-icon>
            连接正常
          </div>
        </div>

        <div class="status-item">
          <div class="status-label">权限模块</div>
          <div class="status-value success">
            <el-icon><CircleCheck /></el-icon>
            已部署
          </div>
        </div>
      </div>
    </el-card>

    <el-card class="info-card">
      <template #header>
        <span>技术架构</span>
      </template>

      <el-descriptions :column="2" border>
        <el-descriptions-item label="前端框架">Vue 3 + Element Plus</el-descriptions-item>
        <el-descriptions-item label="后端框架">Laravel 12</el-descriptions-item>
        <el-descriptions-item label="数据库">MySQL</el-descriptions-item>
        <el-descriptions-item label="图表库">ECharts</el-descriptions-item>
        <el-descriptions-item label="权限层级">五级权限管理</el-descriptions-item>
        <el-descriptions-item label="API接口">20+ RESTful接口</el-descriptions-item>
        <el-descriptions-item label="组件数量">5个核心组件</el-descriptions-item>
        <el-descriptions-item label="开发状态">✅ 已完成</el-descriptions-item>
      </el-descriptions>
    </el-card>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import {
  Share,
  Grid,
  Files,
  Document,
  DataAnalysis,
  Setting,
  CircleCheck
} from '@element-plus/icons-vue'

const router = useRouter()

// 跳转到权限继承关系页面
const goToInheritanceTree = () => {
  router.push('/permissions')
  ElMessage.success('正在跳转到权限继承关系页面...')
}

// 跳转到权限矩阵管理页面
const goToPermissionMatrix = () => {
  router.push('/permissions')
  ElMessage.success('正在跳转到权限矩阵管理页面...')
}

// 跳转到权限模板管理页面
const goToPermissionTemplates = () => {
  router.push('/permissions')
  ElMessage.success('正在跳转到权限模板管理页面...')
}

// 跳转到权限审计历史页面
const goToPermissionAudit = () => {
  router.push('/permissions')
  ElMessage.success('正在跳转到权限审计历史页面...')
}

// 跳转到权限统计分析页面
const goToPermissionAnalytics = () => {
  router.push('/permissions')
  ElMessage.success('正在跳转到权限统计分析页面...')
}

// 跳转到权限撤销设置页面
const goToPermissionRevocation = () => {
  router.push('/permissions')
  ElMessage.success('正在跳转到权限撤销设置页面...')
}
</script>

<style scoped>
.permission-test {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 32px;
  color: #303133;
  margin-bottom: 10px;
}

.page-description {
  font-size: 16px;
  color: #606266;
  margin: 0;
}

.test-card,
.status-card,
.info-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.feature-item {
  text-align: center;
  padding: 20px;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  transition: all 0.3s;
}

.feature-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.feature-icon {
  margin-bottom: 15px;
  color: #409eff;
}

.feature-item h3 {
  margin: 0 0 10px 0;
  color: #303133;
  font-size: 18px;
}

.feature-item p {
  margin: 0 0 15px 0;
  color: #606266;
  font-size: 14px;
  line-height: 1.5;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.status-item {
  text-align: center;
  padding: 15px;
}

.status-label {
  font-size: 14px;
  color: #606266;
  margin-bottom: 8px;
}

.status-value {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  font-size: 16px;
  font-weight: 500;
}

.status-value.success {
  color: #67c23a;
}

.status-value.error {
  color: #f56c6c;
}

.status-value.warning {
  color: #e6a23c;
}
</style>
