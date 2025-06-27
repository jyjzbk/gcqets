<template>
  <div class="permission-visualization">
    <div class="page-header">
      <h1>权限可视化管理</h1>
      <p class="page-description">
        通过可视化界面管理组织权限继承关系、权限矩阵、权限模板和审计日志
      </p>
    </div>

    <el-tabs v-model="activeTab" type="card" class="visualization-tabs">
      <!-- 权限继承关系 -->
      <el-tab-pane label="权限继承关系" name="inheritance">
        <template #label>
          <span class="tab-label">
            <el-icon><Share /></el-icon>
            权限继承关系
          </span>
        </template>
        <div class="tab-content">
          <PermissionInheritanceTree />
        </div>
      </el-tab-pane>

      <!-- 权限矩阵管理 -->
      <el-tab-pane label="权限矩阵管理" name="matrix">
        <template #label>
          <span class="tab-label">
            <el-icon><Grid /></el-icon>
            权限矩阵管理
          </span>
        </template>
        <div class="tab-content">
          <PermissionMatrix />
        </div>
      </el-tab-pane>

      <!-- 权限模板管理 -->
      <el-tab-pane label="权限模板管理" name="templates">
        <template #label>
          <span class="tab-label">
            <el-icon><Files /></el-icon>
            权限模板管理
          </span>
        </template>
        <div class="tab-content">
          <PermissionTemplates />
        </div>
      </el-tab-pane>

      <!-- 权限审计历史 -->
      <el-tab-pane label="权限审计历史" name="audit">
        <template #label>
          <span class="tab-label">
            <el-icon><Document /></el-icon>
            权限审计历史
          </span>
        </template>
        <div class="tab-content">
          <PermissionAudit />
        </div>
      </el-tab-pane>

      <!-- 权限统计分析 -->
      <el-tab-pane label="权限统计分析" name="analytics">
        <template #label>
          <span class="tab-label">
            <el-icon><DataAnalysis /></el-icon>
            权限统计分析
          </span>
        </template>
        <div class="tab-content">
          <PermissionAnalytics />
        </div>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  Share,
  Grid,
  Files,
  Document,
  DataAnalysis
} from '@element-plus/icons-vue'
import PermissionInheritanceTree from '../../components/permission/PermissionInheritanceTree.vue'
import PermissionMatrix from '../../components/permission/PermissionMatrix.vue'
import PermissionTemplates from '../../components/permission/PermissionTemplates.vue'
import PermissionAudit from '../../components/permission/PermissionAudit.vue'
import PermissionAnalytics from '../../components/permission/PermissionAnalytics.vue'

// 当前激活的标签页
const activeTab = ref('inheritance')
</script>

<style scoped>
.permission-visualization {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f5f7fa;
}

.page-header {
  padding: 24px 24px 16px;
  background: white;
  border-bottom: 1px solid #e4e7ed;
}

.page-header h1 {
  margin: 0 0 8px 0;
  font-size: 24px;
  font-weight: 600;
  color: #303133;
}

.page-description {
  margin: 0;
  color: #606266;
  font-size: 14px;
  line-height: 1.5;
}

.visualization-tabs {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin: 16px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.visualization-tabs :deep(.el-tabs__header) {
  margin: 0;
  padding: 0 16px;
  background: #fafbfc;
  border-bottom: 1px solid #e4e7ed;
  border-radius: 8px 8px 0 0;
}

.visualization-tabs :deep(.el-tabs__nav-wrap) {
  padding: 8px 0;
}

.visualization-tabs :deep(.el-tabs__item) {
  height: 40px;
  line-height: 40px;
  padding: 0 20px;
  border-radius: 6px;
  margin-right: 8px;
  border: 1px solid transparent;
  transition: all 0.3s;
}

.visualization-tabs :deep(.el-tabs__item:hover) {
  background: #ecf5ff;
  color: #409eff;
}

.visualization-tabs :deep(.el-tabs__item.is-active) {
  background: #409eff;
  color: white;
  border-color: #409eff;
}

.visualization-tabs :deep(.el-tabs__content) {
  flex: 1;
  padding: 0;
  overflow: hidden;
}

.visualization-tabs :deep(.el-tab-pane) {
  height: 100%;
}

.tab-label {
  display: flex;
  align-items: center;
  gap: 6px;
}

.tab-content {
  height: 100%;
  padding: 16px;
  overflow: hidden;
}
</style>
