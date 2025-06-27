<template>
  <div class="permission-settings">
    <div class="page-header">
      <div class="header-content">
        <h2>组织权限设置</h2>
        <el-text type="info">
          可视化管理组织机构权限继承关系、权限矩阵和审计历史
        </el-text>
      </div>
      <div class="header-actions">
        <el-button @click="refreshAll" :loading="refreshing">
          <el-icon><Refresh /></el-icon>
          全部刷新
        </el-button>
        <el-button @click="showHelp" type="primary">
          <el-icon><QuestionFilled /></el-icon>
          使用帮助
        </el-button>
      </div>
    </div>

    <div class="page-content">
      <el-tabs v-model="activeTab" class="permission-tabs" @tab-change="handleTabChange">
        <!-- 权限继承关系 -->
        <el-tab-pane label="权限继承关系" name="inheritance">
          <div class="tab-pane-content">
            <PermissionInheritanceTree ref="inheritanceTreeRef" />
          </div>
        </el-tab-pane>

        <!-- 权限矩阵管理 -->
        <el-tab-pane label="权限矩阵管理" name="matrix">
          <div class="tab-pane-content">
            <PermissionMatrix ref="permissionMatrixRef" />
          </div>
        </el-tab-pane>

        <!-- 权限审计历史 -->
        <el-tab-pane label="权限审计历史" name="audit">
          <div class="tab-pane-content">
            <PermissionAudit ref="permissionAuditRef" />
          </div>
        </el-tab-pane>

        <!-- 权限模板管理 -->
        <el-tab-pane label="权限模板管理" name="templates">
          <div class="tab-pane-content">
            <PermissionTemplates ref="permissionTemplatesRef" />
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>

    <!-- 使用帮助对话框 -->
    <el-dialog
      v-model="helpDialogVisible"
      title="使用帮助"
      width="800px"
    >
      <div class="help-content">
        <el-collapse v-model="activeHelp">
          <el-collapse-item title="权限继承关系" name="inheritance">
            <div class="help-section">
              <h4>功能说明</h4>
              <p>展示组织机构间的权限继承关系和传递路径，帮助理解权限的来源和流向。</p>
              
              <h4>主要功能</h4>
              <ul>
                <li>树形展示组织权限继承关系</li>
                <li>查看权限继承路径</li>
                <li>检测权限冲突</li>
                <li>权限覆盖管理</li>
              </ul>
              
              <h4>使用方法</h4>
              <ol>
                <li>选择要查看的组织机构</li>
                <li>查看该组织的权限继承关系树</li>
                <li>点击权限节点查看详细信息</li>
                <li>使用"检测冲突"功能发现权限问题</li>
              </ol>
            </div>
          </el-collapse-item>

          <el-collapse-item title="权限矩阵管理" name="matrix">
            <div class="help-section">
              <h4>功能说明</h4>
              <p>提供权限矩阵视图，支持批量权限分配和权限模板应用。</p>
              
              <h4>主要功能</h4>
              <ul>
                <li>用户、角色、组织权限矩阵展示</li>
                <li>批量权限授予和撤销</li>
                <li>权限模板应用</li>
                <li>权限变更预览</li>
              </ul>
              
              <h4>使用方法</h4>
              <ol>
                <li>选择矩阵类型（用户/角色/组织）</li>
                <li>选择具体的目标对象</li>
                <li>在矩阵中勾选或取消权限</li>
                <li>使用批量操作提高效率</li>
                <li>应用权限模板快速配置</li>
              </ol>
            </div>
          </el-collapse-item>

          <el-collapse-item title="权限审计历史" name="audit">
            <div class="help-section">
              <h4>功能说明</h4>
              <p>记录和展示所有权限变更操作，提供完整的审计追踪。</p>
              
              <h4>主要功能</h4>
              <ul>
                <li>权限变更历史记录</li>
                <li>操作审计日志</li>
                <li>权限冲突管理</li>
                <li>统计分析报告</li>
              </ul>
              
              <h4>使用方法</h4>
              <ol>
                <li>在审计日志中查看历史操作</li>
                <li>使用筛选条件精确查找</li>
                <li>处理权限冲突问题</li>
                <li>查看统计分析数据</li>
              </ol>
            </div>
          </el-collapse-item>

          <el-collapse-item title="权限模板管理" name="templates">
            <div class="help-section">
              <h4>功能说明</h4>
              <p>管理权限模板，支持快速应用预定义的权限配置。</p>
              
              <h4>主要功能</h4>
              <ul>
                <li>权限模板创建和编辑</li>
                <li>模板应用到角色或用户</li>
                <li>模板复制和导入导出</li>
                <li>推荐模板建议</li>
              </ul>
              
              <h4>使用方法</h4>
              <ol>
                <li>创建或选择权限模板</li>
                <li>配置模板的权限内容</li>
                <li>将模板应用到目标对象</li>
                <li>管理模板的生命周期</li>
              </ol>
            </div>
          </el-collapse-item>
        </el-collapse>
      </div>
      <template #footer>
        <el-button @click="helpDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import PermissionInheritanceTree from '@/components/permission/PermissionInheritanceTree.vue'
import PermissionMatrix from '@/components/permission/PermissionMatrix.vue'
import PermissionAudit from '@/components/permission/PermissionAudit.vue'
import PermissionTemplates from '@/components/permission/PermissionTemplates.vue'

// 响应式数据
const activeTab = ref('inheritance')
const refreshing = ref(false)
const helpDialogVisible = ref(false)
const activeHelp = ref(['inheritance'])

// 组件引用
const inheritanceTreeRef = ref(null)
const permissionMatrixRef = ref(null)
const permissionAuditRef = ref(null)
const permissionTemplatesRef = ref(null)

// 方法
const handleTabChange = (tabName) => {
  // 可以在这里添加标签页切换时的逻辑
  console.log('切换到标签页:', tabName)
}

const refreshAll = async () => {
  refreshing.value = true
  try {
    // 根据当前活跃的标签页刷新对应的组件
    switch (activeTab.value) {
      case 'inheritance':
        if (inheritanceTreeRef.value) {
          await inheritanceTreeRef.value.refreshTree()
        }
        break
      case 'matrix':
        if (permissionMatrixRef.value) {
          await permissionMatrixRef.value.loadMatrix()
        }
        break
      case 'audit':
        if (permissionAuditRef.value) {
          await permissionAuditRef.value.refreshData()
        }
        break
      case 'templates':
        if (permissionTemplatesRef.value) {
          await permissionTemplatesRef.value.refreshData()
        }
        break
    }
    ElMessage.success('刷新成功')
  } catch (error) {
    console.error('刷新失败:', error)
    ElMessage.error('刷新失败')
  } finally {
    refreshing.value = false
  }
}

const showHelp = () => {
  helpDialogVisible.value = true
  // 根据当前标签页设置默认展开的帮助项
  activeHelp.value = [activeTab.value]
}

// 生命周期
onMounted(() => {
  // 页面加载完成后的初始化逻辑
  console.log('权限设置页面已加载')
})
</script>

<style scoped>
.permission-settings {
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
  overflow: hidden;
}

.permission-tabs {
  height: 100%;
  background: #fff;
  margin: 0;
}

.permission-tabs :deep(.el-tabs__header) {
  margin: 0;
  padding: 0 24px;
  background: #fff;
  border-bottom: 1px solid #ebeef5;
}

.permission-tabs :deep(.el-tabs__content) {
  height: calc(100% - 56px);
  padding: 0;
}

.tab-pane-content {
  height: 100%;
  overflow: hidden;
}

.help-content {
  max-height: 500px;
  overflow-y: auto;
}

.help-section {
  padding: 10px 0;
}

.help-section h4 {
  margin: 15px 0 10px 0;
  color: #303133;
  font-size: 14px;
  font-weight: 600;
}

.help-section p {
  margin: 0 0 10px 0;
  color: #606266;
  line-height: 1.6;
}

.help-section ul,
.help-section ol {
  margin: 0 0 10px 0;
  padding-left: 20px;
  color: #606266;
}

.help-section li {
  margin: 5px 0;
  line-height: 1.5;
}
</style>
