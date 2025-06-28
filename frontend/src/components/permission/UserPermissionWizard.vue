<template>
  <el-dialog
    v-model="visible"
    title="用户权限分配向导"
    width="800px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-steps :active="currentStep" align-center class="wizard-steps">
      <el-step title="选择用户类型" />
      <el-step title="填写用户信息" />
      <el-step title="选择组织机构" />
      <el-step title="分配权限" />
      <el-step title="确认创建" />
    </el-steps>

    <div class="wizard-content">
      <!-- 步骤1: 选择用户类型 -->
      <div v-if="currentStep === 0" class="step-content">
        <h3>请选择要创建的用户类型</h3>
        <el-row :gutter="20" class="user-type-selection">
          <el-col :span="8">
            <el-card 
              class="type-card" 
              :class="{ active: form.userType === 'district' }"
              @click="selectUserType('district')"
            >
              <div class="type-content">
                <el-icon class="type-icon"><OfficeBuilding /></el-icon>
                <h4>学区管理员</h4>
                <p>管理整个学区的用户和权限</p>
                <ul class="permission-list">
                  <li>用户管理</li>
                  <li>学校管理</li>
                  <li>权限分配</li>
                </ul>
              </div>
            </el-card>
          </el-col>
          
          <el-col :span="8">
            <el-card 
              class="type-card" 
              :class="{ active: form.userType === 'school_admin' }"
              @click="selectUserType('school_admin')"
            >
              <div class="type-content">
                <el-icon class="type-icon"><School /></el-icon>
                <h4>学校管理员</h4>
                <p>管理学校内部用户和权限</p>
                <ul class="permission-list">
                  <li>教师管理</li>
                  <li>班级管理</li>
                  <li>教学管理</li>
                </ul>
              </div>
            </el-card>
          </el-col>
          
          <el-col :span="8">
            <el-card 
              class="type-card" 
              :class="{ active: form.userType === 'teacher' }"
              @click="selectUserType('teacher')"
            >
              <div class="type-content">
                <el-icon class="type-icon"><User /></el-icon>
                <h4>教师用户</h4>
                <p>普通教师用户权限</p>
                <ul class="permission-list">
                  <li>查看信息</li>
                  <li>教学记录</li>
                  <li>学生管理</li>
                </ul>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </div>

      <!-- 步骤2: 填写用户信息 -->
      <div v-if="currentStep === 1" class="step-content">
        <h3>填写用户基本信息</h3>
        <el-form :model="form" :rules="rules" ref="userFormRef" label-width="100px">
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="用户名" prop="username">
                <el-input v-model="form.username" placeholder="请输入用户名" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="真实姓名" prop="realName">
                <el-input v-model="form.realName" placeholder="请输入真实姓名" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="邮箱" prop="email">
                <el-input v-model="form.email" placeholder="请输入邮箱" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="手机号" prop="phone">
                <el-input v-model="form.phone" placeholder="请输入手机号" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="职位" prop="position">
                <el-input v-model="form.position" placeholder="请输入职位" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="部门" prop="department">
                <el-input v-model="form.department" placeholder="请输入部门" />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>

      <!-- 步骤3: 选择组织机构 -->
      <div v-if="currentStep === 2" class="step-content">
        <h3>选择所属组织机构</h3>
        <div class="org-selection">
          <el-tree
            ref="orgTreeRef"
            :data="organizationTree"
            :props="treeProps"
            node-key="id"
            :default-expand-all="true"
            :highlight-current="true"
            @node-click="handleOrgSelect"
          >
            <template #default="{ node, data }">
              <div class="org-node">
                <el-icon>
                  <component :is="getOrgIcon(data.type)" />
                </el-icon>
                <span>{{ data.name }}</span>
                <el-tag v-if="data.id === form.organizationId" type="success" size="small">
                  已选择
                </el-tag>
              </div>
            </template>
          </el-tree>
        </div>
      </div>

      <!-- 步骤4: 分配权限 -->
      <div v-if="currentStep === 3" class="step-content">
        <h3>分配用户权限</h3>
        <div class="permission-assignment">
          <div class="template-selection">
            <h4>选择权限模板（推荐）</h4>
            <el-radio-group v-model="form.permissionTemplate" @change="applyTemplate">
              <el-radio-button 
                v-for="template in permissionTemplates" 
                :key="template.id" 
                :label="template.id"
              >
                {{ template.name }}
              </el-radio-button>
            </el-radio-group>
          </div>
          
          <div class="custom-permissions">
            <h4>自定义权限</h4>
            <el-tree
              ref="permissionTreeRef"
              :data="permissionTree"
              :props="permissionTreeProps"
              show-checkbox
              node-key="id"
              :default-checked-keys="form.permissionIds"
              @check="handlePermissionCheck"
            >
              <template #default="{ node, data }">
                <div class="permission-node">
                  <span>{{ data.display_name }}</span>
                  <el-tag v-if="data.is_system" type="warning" size="small">系统</el-tag>
                </div>
              </template>
            </el-tree>
          </div>
        </div>
      </div>

      <!-- 步骤5: 确认创建 -->
      <div v-if="currentStep === 4" class="step-content">
        <h3>确认用户信息</h3>
        <div class="confirmation">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="用户类型">
              {{ getUserTypeLabel(form.userType) }}
            </el-descriptions-item>
            <el-descriptions-item label="用户名">
              {{ form.username }}
            </el-descriptions-item>
            <el-descriptions-item label="真实姓名">
              {{ form.realName }}
            </el-descriptions-item>
            <el-descriptions-item label="邮箱">
              {{ form.email }}
            </el-descriptions-item>
            <el-descriptions-item label="所属组织">
              {{ selectedOrgName }}
            </el-descriptions-item>
            <el-descriptions-item label="权限数量">
              {{ form.permissionIds.length }} 个权限
            </el-descriptions-item>
          </el-descriptions>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="wizard-footer">
        <el-button v-if="currentStep > 0" @click="prevStep">上一步</el-button>
        <el-button v-if="currentStep < 4" type="primary" @click="nextStep" :disabled="!canNext">
          下一步
        </el-button>
        <el-button v-if="currentStep === 4" type="primary" @click="createUser" :loading="creating">
          创建用户
        </el-button>
        <el-button @click="handleClose">取消</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { ElMessage } from 'element-plus'
import {
  OfficeBuilding,
  School,
  User
} from '@element-plus/icons-vue'

// Props
const props = defineProps({
  modelValue: Boolean
})

// Emits
const emit = defineEmits(['update:modelValue', 'success'])

// 响应式数据
const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const currentStep = ref(0)
const creating = ref(false)
const userFormRef = ref()
const orgTreeRef = ref()
const permissionTreeRef = ref()

// 表单数据
const form = reactive({
  userType: '',
  username: '',
  realName: '',
  email: '',
  phone: '',
  position: '',
  department: '',
  organizationId: null,
  permissionTemplate: null,
  permissionIds: []
})

// 验证规则
const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' }
  ],
  realName: [
    { required: true, message: '请输入真实姓名', trigger: 'blur' }
  ],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '邮箱格式不正确', trigger: 'blur' }
  ]
}

// 模拟数据
const organizationTree = ref([])
const permissionTree = ref([])
const permissionTemplates = ref([])
const selectedOrgName = ref('')

// 树形配置
const treeProps = {
  children: 'children',
  label: 'name'
}

const permissionTreeProps = {
  children: 'children',
  label: 'display_name'
}

// 计算属性
const canNext = computed(() => {
  switch (currentStep.value) {
    case 0: return form.userType !== ''
    case 1: return form.username && form.realName && form.email
    case 2: return form.organizationId !== null
    case 3: return form.permissionIds.length > 0
    default: return true
  }
})

// 方法
const selectUserType = (type) => {
  form.userType = type
}

const getUserTypeLabel = (type) => {
  const labels = {
    district: '学区管理员',
    school_admin: '学校管理员',
    teacher: '教师用户'
  }
  return labels[type] || type
}

const getOrgIcon = (type) => {
  const icons = {
    district: OfficeBuilding,
    school: School
  }
  return icons[type] || OfficeBuilding
}

const handleOrgSelect = (data) => {
  form.organizationId = data.id
  selectedOrgName.value = data.name
}

const applyTemplate = (templateId) => {
  // 应用权限模板
  const template = permissionTemplates.value.find(t => t.id === templateId)
  if (template) {
    form.permissionIds = [...template.permission_ids]
    permissionTreeRef.value?.setCheckedKeys(form.permissionIds)
  }
}

const handlePermissionCheck = (data, checked) => {
  form.permissionIds = checked.checkedKeys
}

const nextStep = async () => {
  if (currentStep.value === 1) {
    // 验证表单
    try {
      await userFormRef.value.validate()
    } catch {
      return
    }
  }
  
  if (currentStep.value < 4) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const createUser = async () => {
  try {
    creating.value = true
    
    // 调用API创建用户
    // await userApi.create(form)
    
    ElMessage.success('用户创建成功')
    emit('success')
    handleClose()
  } catch (error) {
    ElMessage.error('创建用户失败')
  } finally {
    creating.value = false
  }
}

const handleClose = () => {
  visible.value = false
  currentStep.value = 0
  Object.assign(form, {
    userType: '',
    username: '',
    realName: '',
    email: '',
    phone: '',
    position: '',
    department: '',
    organizationId: null,
    permissionTemplate: null,
    permissionIds: []
  })
}

// 监听对话框打开
watch(visible, (val) => {
  if (val) {
    // 加载数据
    loadData()
  }
})

const loadData = async () => {
  // 加载组织树、权限树、权限模板等数据
  // 这里使用模拟数据
  organizationTree.value = [
    {
      id: 1,
      name: '廉州学区',
      type: 'district',
      children: [
        { id: 2, name: '东城小学', type: 'school' },
        { id: 3, name: '西城小学', type: 'school' }
      ]
    }
  ]
  
  permissionTemplates.value = [
    { id: 1, name: '学区管理员模板', permission_ids: [1, 2, 3, 4, 5] },
    { id: 2, name: '学校管理员模板', permission_ids: [2, 3, 4] },
    { id: 3, name: '教师模板', permission_ids: [2, 3] }
  ]
  
  permissionTree.value = [
    {
      id: 1,
      display_name: '用户管理',
      children: [
        { id: 2, display_name: '查看用户' },
        { id: 3, display_name: '创建用户' }
      ]
    }
  ]
}
</script>

<style scoped>
.wizard-steps {
  margin-bottom: 30px;
}

.wizard-content {
  min-height: 400px;
  padding: 20px 0;
}

.step-content h3 {
  margin-bottom: 20px;
  color: #303133;
}

.user-type-selection .type-card {
  cursor: pointer;
  transition: all 0.3s;
  height: 200px;
}

.user-type-selection .type-card:hover,
.user-type-selection .type-card.active {
  border-color: #409eff;
  box-shadow: 0 2px 12px rgba(64, 158, 255, 0.2);
}

.type-content {
  text-align: center;
  padding: 20px;
}

.type-icon {
  font-size: 40px;
  color: #409eff;
  margin-bottom: 10px;
}

.permission-list {
  list-style: none;
  padding: 0;
  margin: 10px 0 0 0;
}

.permission-list li {
  font-size: 12px;
  color: #909399;
  margin-bottom: 4px;
}

.org-selection {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 10px;
}

.org-node {
  display: flex;
  align-items: center;
  gap: 8px;
}

.permission-assignment .template-selection {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #ebeef5;
}

.permission-assignment h4 {
  margin-bottom: 10px;
  color: #606266;
}

.custom-permissions {
  max-height: 250px;
  overflow-y: auto;
}

.permission-node {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.confirmation {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 4px;
}

.wizard-footer {
  text-align: right;
}
</style>
