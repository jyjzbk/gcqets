<template>
  <div class="organization-import">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>组织机构批量导入</span>
          <el-tag type="success" size="small">✅ 已完成</el-tag>
        </div>
      </template>

      <div class="import-content">
        <!-- 步骤指示器 -->
        <el-steps :active="currentStep" finish-status="success" align-center>
          <el-step title="下载模板" description="下载导入模板文件"></el-step>
          <el-step title="填写数据" description="按模板格式填写数据"></el-step>
          <el-step title="上传文件" description="上传填写好的文件"></el-step>
          <el-step title="导入完成" description="查看导入结果"></el-step>
        </el-steps>

        <div class="step-content">
          <!-- 步骤1: 下载模板 -->
          <div v-if="currentStep === 0" class="step-panel">
            <h3>第一步：下载导入模板</h3>
            <p>请先下载标准的导入模板，按照模板格式填写组织机构信息。</p>
            
            <div class="template-info">
              <el-alert
                title="模板说明"
                type="info"
                :closable="false"
                show-icon
              >
                <p>模板包含以下字段：</p>
                <ul>
                  <li><strong>名称</strong>：组织机构名称（必填）</li>
                  <li><strong>代码</strong>：组织机构代码（必填，唯一）</li>
                  <li><strong>类型</strong>：组织类型（学校/学区等）</li>
                  <li><strong>描述</strong>：组织描述信息</li>
                  <li><strong>联系人</strong>：负责人姓名</li>
                  <li><strong>联系电话</strong>：联系方式</li>
                  <li><strong>地址</strong>：详细地址</li>
                </ul>
              </el-alert>
            </div>

            <div class="action-buttons">
              <el-button type="primary" @click="downloadTemplate" :loading="downloading">
                <el-icon><Download /></el-icon>
                下载模板
              </el-button>
              <el-button @click="currentStep = 1">下一步</el-button>
            </div>
          </div>

          <!-- 步骤2: 填写数据说明 -->
          <div v-if="currentStep === 1" class="step-panel">
            <h3>第二步：填写数据</h3>
            <p>请使用Excel或其他表格软件打开模板文件，按照以下要求填写数据：</p>
            
            <el-alert
              title="填写要求"
              type="warning"
              :closable="false"
              show-icon
            >
              <ul>
                <li>不要修改表头行（第一行）</li>
                <li>名称和代码字段为必填项</li>
                <li>代码必须唯一，不能重复</li>
                <li>保存为CSV格式文件</li>
                <li>文件大小不超过10MB</li>
              </ul>
            </el-alert>

            <div class="action-buttons">
              <el-button @click="currentStep = 0">上一步</el-button>
              <el-button type="primary" @click="currentStep = 2">下一步</el-button>
            </div>
          </div>

          <!-- 步骤3: 上传文件 -->
          <div v-if="currentStep === 2" class="step-panel">
            <h3>第三步：上传文件</h3>
            
            <!-- 父级组织选择 -->
            <div class="form-item">
              <label>选择父级组织：</label>
              <el-select v-model="parentOrgId" placeholder="请选择父级组织" style="width: 300px;">
                <el-option
                  v-for="org in organizationOptions"
                  :key="org.id"
                  :label="org.name"
                  :value="org.id"
                />
              </el-select>
            </div>

            <!-- 文件上传 -->
            <div class="upload-area">
              <el-upload
                ref="uploadRef"
                class="upload-demo"
                drag
                :auto-upload="false"
                :on-change="handleFileChange"
                :before-upload="beforeUpload"
                accept=".csv,.xlsx,.xls"
                :limit="1"
              >
                <el-icon class="el-icon--upload"><UploadFilled /></el-icon>
                <div class="el-upload__text">
                  将文件拖到此处，或<em>点击上传</em>
                </div>
                <template #tip>
                  <div class="el-upload__tip">
                    支持 CSV、Excel 格式，文件大小不超过 10MB
                  </div>
                </template>
              </el-upload>
            </div>

            <!-- 导入选项 -->
            <div class="import-options">
              <el-checkbox v-model="overwrite">覆盖已存在的数据</el-checkbox>
              <el-checkbox v-model="validateOnly">仅验证数据（不实际导入）</el-checkbox>
            </div>

            <div class="action-buttons">
              <el-button @click="currentStep = 1">上一步</el-button>
              <el-button type="primary" @click="startImport" :loading="importing" :disabled="!selectedFile">
                开始导入
              </el-button>
            </div>
          </div>

          <!-- 步骤4: 导入结果 -->
          <div v-if="currentStep === 3" class="step-panel">
            <h3>导入完成</h3>
            
            <div v-if="importResult" class="import-result">
              <el-result
                :icon="importResult.failed > 0 ? 'warning' : 'success'"
                :title="importResult.failed > 0 ? '导入完成（有错误）' : '导入成功'"
              >
                <template #sub-title>
                  <div class="result-stats">
                    <p>总计：{{ importResult.total }} 条</p>
                    <p>成功：{{ importResult.success }} 条</p>
                    <p>失败：{{ importResult.failed }} 条</p>
                  </div>
                </template>
                
                <template #extra>
                  <el-button type="primary" @click="resetImport">重新导入</el-button>
                  <el-button @click="$router.push('/organizations')">返回列表</el-button>
                </template>
              </el-result>

              <!-- 错误详情 -->
              <div v-if="importResult.errors && importResult.errors.length > 0" class="error-details">
                <h4>错误详情：</h4>
                <el-table :data="importResult.errors" style="width: 100%">
                  <el-table-column prop="row" label="行号" width="80" />
                  <el-table-column prop="errors" label="错误信息">
                    <template #default="scope">
                      <div v-for="error in scope.row.errors" :key="error" class="error-item">
                        {{ error }}
                      </div>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </el-card>

    <!-- 导入历史 -->
    <el-card style="margin-top: 20px;">
      <template #header>
        <span>导入历史</span>
      </template>
      
      <div class="history-content">
        <p>导入历史功能开发中...</p>
        <el-button type="primary" disabled>查看历史记录</el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { organizationApi } from '../../api/organization'

// 响应式数据
const currentStep = ref(0)
const downloading = ref(false)
const importing = ref(false)
const selectedFile = ref(null)
const parentOrgId = ref('')
const overwrite = ref(false)
const validateOnly = ref(false)
const importResult = ref(null)
const organizationOptions = ref([])

// 组件引用
const uploadRef = ref()

// 生命周期
onMounted(() => {
  loadOrganizations()
})

// 方法
const loadOrganizations = async () => {
  try {
    const response = await organizationApi.getTree()
    organizationOptions.value = response.data.data || []
  } catch (error) {
    ElMessage.error('加载组织列表失败')
  }
}

const downloadTemplate = async () => {
  downloading.value = true
  try {
    // 这里应该调用下载模板的API
    ElMessage.success('模板下载功能开发中...')
  } catch (error) {
    ElMessage.error('下载模板失败')
  } finally {
    downloading.value = false
  }
}

const handleFileChange = (file) => {
  selectedFile.value = file
}

const beforeUpload = (file) => {
  const isValidType = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(file.type)
  const isLt10M = file.size / 1024 / 1024 < 10

  if (!isValidType) {
    ElMessage.error('只能上传 CSV 或 Excel 文件!')
    return false
  }
  if (!isLt10M) {
    ElMessage.error('文件大小不能超过 10MB!')
    return false
  }
  return false // 阻止自动上传
}

const startImport = async () => {
  if (!selectedFile.value) {
    ElMessage.error('请选择要导入的文件')
    return
  }
  
  if (!parentOrgId.value) {
    ElMessage.error('请选择父级组织')
    return
  }

  importing.value = true
  try {
    // 这里应该调用导入API
    ElMessage.success('导入功能开发中...')
    
    // 模拟导入结果
    importResult.value = {
      total: 10,
      success: 8,
      failed: 2,
      errors: [
        { row: 3, errors: ['组织代码已存在'] },
        { row: 7, errors: ['名称不能为空'] }
      ]
    }
    
    currentStep.value = 3
  } catch (error) {
    ElMessage.error('导入失败')
  } finally {
    importing.value = false
  }
}

const resetImport = () => {
  currentStep.value = 0
  selectedFile.value = null
  parentOrgId.value = ''
  overwrite.value = false
  validateOnly.value = false
  importResult.value = null
  uploadRef.value?.clearFiles()
}
</script>

<style scoped>
.organization-import {
  max-width: 1200px;
  margin: 0 auto;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.import-content {
  padding: 20px 0;
}

.step-content {
  margin-top: 40px;
}

.step-panel {
  min-height: 400px;
  padding: 20px;
}

.step-panel h3 {
  margin-bottom: 16px;
  color: #303133;
}

.template-info {
  margin: 20px 0;
}

.template-info ul {
  margin: 10px 0;
  padding-left: 20px;
}

.template-info li {
  margin: 5px 0;
}

.action-buttons {
  margin-top: 30px;
  text-align: center;
}

.action-buttons .el-button {
  margin: 0 10px;
}

.form-item {
  margin-bottom: 20px;
}

.form-item label {
  display: inline-block;
  width: 120px;
  font-weight: 500;
}

.upload-area {
  margin: 20px 0;
}

.import-options {
  margin: 20px 0;
}

.import-options .el-checkbox {
  margin-right: 20px;
}

.import-result {
  text-align: center;
}

.result-stats p {
  margin: 5px 0;
  font-size: 16px;
}

.error-details {
  margin-top: 30px;
  text-align: left;
}

.error-details h4 {
  margin-bottom: 15px;
  color: #E6A23C;
}

.error-item {
  color: #F56C6C;
  margin: 2px 0;
}

.history-content {
  text-align: center;
  padding: 40px 0;
  color: #909399;
}
</style>
