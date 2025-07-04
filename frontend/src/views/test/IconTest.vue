<template>
  <div class="icon-test">
    <el-card>
      <template #header>
        <h3>图标测试页面</h3>
        <p>测试所有使用的Element Plus图标是否正常显示</p>
      </template>

      <div class="icon-sections">
        <!-- 菜单图标 -->
        <div class="icon-section">
          <h4>菜单图标</h4>
          <div class="icon-grid">
            <div class="icon-item">
              <el-icon size="24"><Operation /></el-icon>
              <span>Operation</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Calendar /></el-icon>
              <span>Calendar</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><EditPen /></el-icon>
              <span>EditPen</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Select /></el-icon>
              <span>Select</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Tools /></el-icon>
              <span>Tools</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Grid /></el-icon>
              <span>Grid</span>
            </div>
          </div>
        </div>

        <!-- 审核相关图标 -->
        <div class="icon-section">
          <h4>审核相关图标</h4>
          <div class="icon-grid">
            <div class="icon-item">
              <el-icon size="24"><Search /></el-icon>
              <span>Search</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Refresh /></el-icon>
              <span>Refresh</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Check /></el-icon>
              <span>Check</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Close /></el-icon>
              <span>Close</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><RefreshLeft /></el-icon>
              <span>RefreshLeft</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><ArrowDown /></el-icon>
              <span>ArrowDown</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Picture /></el-icon>
              <span>Picture</span>
            </div>
          </div>
        </div>

        <!-- 审核日志图标 -->
        <div class="icon-section">
          <h4>审核日志图标</h4>
          <div class="icon-grid">
            <div class="icon-item">
              <el-icon size="24"><Document /></el-icon>
              <span>Document</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Warning /></el-icon>
              <span>Warning</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Setting /></el-icon>
              <span>Setting</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Cpu /></el-icon>
              <span>Cpu (AI检查)</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><User /></el-icon>
              <span>User</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Timer /></el-icon>
              <span>Timer</span>
            </div>
          </div>
        </div>

        <!-- 系统测试图标 -->
        <div class="icon-section">
          <h4>系统测试图标</h4>
          <div class="icon-grid">
            <div class="icon-item">
              <el-icon size="24"><VideoPlay /></el-icon>
              <span>VideoPlay</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Delete /></el-icon>
              <span>Delete</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Loading /></el-icon>
              <span>Loading</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><CircleCheck /></el-icon>
              <span>CircleCheck</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><CircleClose /></el-icon>
              <span>CircleClose</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Minus /></el-icon>
              <span>Minus</span>
            </div>
          </div>
        </div>

        <!-- 其他常用图标 -->
        <div class="icon-section">
          <h4>其他常用图标</h4>
          <div class="icon-grid">
            <div class="icon-item">
              <el-icon size="24"><Monitor /></el-icon>
              <span>Monitor</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><OfficeBuilding /></el-icon>
              <span>OfficeBuilding</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Key /></el-icon>
              <span>Key</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><School /></el-icon>
              <span>School</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><DataAnalysis /></el-icon>
              <span>DataAnalysis</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Reading /></el-icon>
              <span>Reading</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Box /></el-icon>
              <span>Box</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><Goods /></el-icon>
              <span>Goods</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><TrendCharts /></el-icon>
              <span>TrendCharts</span>
            </div>
            <div class="icon-item">
              <el-icon size="24"><DataLine /></el-icon>
              <span>DataLine</span>
            </div>
          </div>
        </div>
      </div>

      <div class="test-result">
        <el-alert
          title="图标测试完成"
          type="success"
          description="如果上面的图标都能正常显示，说明图标导入没有问题。如果有图标显示为空白或错误，请检查对应的图标名称。"
          show-icon
          :closable="false"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import {
  // 菜单图标
  Operation,
  Calendar,
  EditPen,
  Select,
  Tools,
  Grid,
  
  // 审核相关图标
  Search,
  Refresh,
  Check,
  Close,
  RefreshLeft,
  ArrowDown,
  Picture,
  
  // 审核日志图标
  Document,
  Warning,
  Setting,
  Cpu,
  User,
  Timer,
  
  // 系统测试图标
  VideoPlay,
  Delete,
  Loading,
  CircleCheck,
  CircleClose,
  Minus,
  
  // 其他常用图标
  Monitor,
  OfficeBuilding,
  Key,
  School,
  DataAnalysis,
  Reading,
  Box,
  Goods,
  TrendCharts,
  DataLine
} from '@element-plus/icons-vue'
</script>

<style scoped>
.icon-test {
  padding: 20px;
}

.icon-sections {
  margin-bottom: 20px;
}

.icon-section {
  margin-bottom: 30px;
}

.icon-section h4 {
  margin-bottom: 15px;
  color: #303133;
  font-size: 16px;
  border-bottom: 1px solid #ebeef5;
  padding-bottom: 8px;
}

.icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 15px;
}

.icon-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  transition: all 0.3s;
}

.icon-item:hover {
  border-color: #409eff;
  background-color: #f0f9ff;
}

.icon-item span {
  margin-top: 8px;
  font-size: 12px;
  color: #606266;
  text-align: center;
  word-break: break-all;
}

.test-result {
  margin-top: 20px;
}
</style>
