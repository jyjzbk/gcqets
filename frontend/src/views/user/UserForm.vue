<template>
  <div class="user-form">
    <div class="page-header">
      <h2>{{ isEdit ? '编辑用户' : '创建用户' }}</h2>
      <el-button @click="$router.back()">
        <el-icon><ArrowLeft /></el-icon>
        返回
      </el-button>
    </div>
    
    <el-card>
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="120px"
        @submit.prevent="handleSubmit"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="用户名" prop="username">
              <el-input
                v-model="form.username"
                placeholder="请输入用户名"
                maxlength="50"
                :disabled="isEdit"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="真实姓名" prop="real_name">
              <el-input
                v-model="form.real_name"
                placeholder="请输入真实姓名"
                maxlength="50"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="邮箱" prop="email">
              <el-input
                v-model="form.email"
                placeholder="请输入邮箱"
                maxlength="100"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="手机号" prop="phone">
              <el-input
                v-model="form.phone"
                placeholder="请输入手机号"
                maxlength="20"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="工号" prop="employee_id">
              <el-input
                v-model="form.employee_id"
                placeholder="请输入工号"
                maxlength="50"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="职位" prop="position">
              <el-input
                v-model="form.position"
                placeholder="请输入职位"
                maxlength="100"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="部门" prop="department">
              <el-input
                v-model="form.department"
                placeholder="请输入部门"
                maxlength="100"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="性别" prop="gender">
              <el-select v-model="form.gender" placeholder="请选择性别" style="width: 100%">
                <el-option label="男" value="male" />
                <el-option label="女" value="female" />
                <el-option label="其他" value="other" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="生日" prop="birth_date">
              <el-date-picker
                v-model="form.birth_date"
                type="date"
                placeholder="请选择生日"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>

          <el-col :span="12">
            <el-form-item label="入职日期" prop="hire_date">
              <el-date-picker
                v-model="form.hire_date"
                type="date"
                placeholder="请选择入职日期"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="主要组织" prop="primary_organization_id">
              <el-tree-select
                v-model="form.primary_organization_id"
                :data="organizationTree"
                :props="treeProps"
                placeholder="请选择主要组织"
                check-strictly
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="状态" prop="status">
              <el-select v-model="form.status" placeholder="请选择状态" style="width: 100%">
                <el-option label="激活" value="active" />
                <el-option label="未激活" value="inactive" />
                <el-option label="锁定" value="locked" />
                <el-option label="待审核" value="pending" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="所属组织" prop="organization_ids">
          <el-tree-select
            v-model="form.organization_ids"
            :data="organizationTree"
            :props="treeProps"
            placeholder="请选择所属组织"
            multiple
            style="width: 100%"
          />
        </el-form-item>
        
        <el-form-item label="角色" prop="role_ids">
          <el-select
            v-model="form.role_ids"
            placeholder="请选择角色"
            multiple
            style="width: 100%"
          >
            <el-option
              v-for="role in roleOptions"
              :key="role.id"
              :label="role.display_name"
              :value="role.id"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="备注" prop="remarks">
          <el-input
            v-model="form.remarks"
            type="textarea"
            :rows="4"
            placeholder="请输入备注信息"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
        
        <el-form-item v-if="!isEdit" label="密码" prop="password">
          <el-input
            v-model="form.password"
            type="password"
            placeholder="请输入密码"
            show-password
            maxlength="50"
          />
        </el-form-item>

        <el-form-item v-if="!isEdit" label="确认密码" prop="password_confirmation">
          <el-input
            v-model="form.password_confirmation"
            type="password"
            placeholder="请再次输入密码"
            show-password
            maxlength="50"
          />
        </el-form-item>

        <el-form-item v-if="isEdit" label="新密码" prop="password">
          <el-input
            v-model="form.password"
            type="password"
            placeholder="留空则不修改密码"
            show-password
            maxlength="50"
          />
        </el-form-item>

        <el-form-item v-if="isEdit && form.password" label="确认新密码" prop="password_confirmation">
          <el-input
            v-model="form.password_confirmation"
            type="password"
            placeholder="请再次输入新密码"
            show-password
            maxlength="50"
          />
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleSubmit" :loading="loading">
            {{ isEdit ? '更新' : '创建' }}
          </el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { useOrganizationStore } from '../../stores/organization'
import { roleApi } from '../../api/role'
import { ElMessage } from 'element-plus'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const organizationStore = useOrganizationStore()

const formRef = ref()
const loading = ref(false)
const organizationTree = ref([])
const roleOptions = ref([])

const isEdit = computed(() => !!route.params.id)

// 表单数据
const form = reactive({
  username: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  real_name: '',
  employee_id: '',
  position: '',
  department: '',
  status: 'active',
  gender: 'other',
  birth_date: null,
  hire_date: null,
  remarks: '',
  primary_organization_id: null,
  organization_ids: [],
  role_ids: []
})

// 表单验证规则
const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { max: 50, message: '用户名不能超过50个字符', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_-]+$/, message: '用户名只能包含字母、数字、下划线和连字符', trigger: 'blur' }
  ],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '邮箱格式不正确', trigger: 'blur' },
    { max: 100, message: '邮箱不能超过100个字符', trigger: 'blur' }
  ],
  real_name: [
    { required: true, message: '请输入真实姓名', trigger: 'blur' },
    { max: 50, message: '真实姓名不能超过50个字符', trigger: 'blur' }
  ],
  primary_organization_id: [
    { required: true, message: '请选择主要组织', trigger: 'change' }
  ],
  password: [
    { required: !isEdit.value, message: '请输入密码', trigger: 'blur' },
    { min: 6, message: '密码不能少于6个字符', trigger: 'blur' }
  ],
  password_confirmation: [
    { required: !isEdit.value, message: '请确认密码', trigger: 'blur' },
    {
      validator: (rule, value, callback) => {
        // 在编辑模式下，如果密码为空，则不需要验证确认密码
        if (isEdit.value && (!form.password || form.password.trim() === '')) {
          callback()
        } else if (value !== form.password) {
          callback(new Error('两次输入的密码不一致'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
}

// 树形选择器配置
const treeProps = {
  children: 'children',
  label: 'name',
  value: 'id'
}

// 获取组织树
const getOrganizationTree = async () => {
  try {
    await organizationStore.getOrganizationTree()
    organizationTree.value = organizationStore.organizationTree
    console.log('UserForm - Organization tree loaded:', organizationTree.value)
  } catch (error) {
    console.error('Get organization tree error:', error)
  }
}

// 获取角色选项
const getRoleOptions = async () => {
  try {
    const response = await roleApi.getOptions()
    roleOptions.value = response.data.data
  } catch (error) {
    console.error('Get role options error:', error)
  }
}

// 获取用户详情
const getUser = async () => {
  try {
    await userStore.getUser(route.params.id)
    const user = userStore.currentUser
    
    Object.assign(form, {
      username: user.username,
      email: user.email,
      phone: user.phone || '',
      real_name: user.real_name,
      employee_id: user.employee_id || '',
      position: user.position || '',
      department: user.department || '',
      status: user.status === true ? 'active' : (user.status === false ? 'inactive' : user.status),
      gender: user.gender,
      birth_date: user.birth_date,
      hire_date: user.hire_date,
      remarks: user.remarks || '',
      primary_organization_id: user.primary_organization_id,
      organization_ids: user.organizations?.map(org => org.id) || [],
      role_ids: user.roles?.map(role => role.id) || []
    })
  } catch (error) {
    console.error('Get user error:', error)
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    loading.value = true

    // 准备提交数据
    const submitData = { ...form }

    // 如果是编辑模式且密码为空，则不发送密码字段
    if (isEdit.value && (!submitData.password || submitData.password.trim() === '')) {
      delete submitData.password
      delete submitData.password_confirmation
    }

    console.log('Submit data:')
    console.table(JSON.parse(JSON.stringify(submitData)))

    if (isEdit.value) {
      await userStore.updateUser(route.params.id, submitData)
      ElMessage.success('更新成功')
    } else {
      await userStore.createUser(submitData)
      ElMessage.success('创建成功')
    }

    router.push('/users')
  } catch (error) {
    console.error('Submit error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    getOrganizationTree(),
    getRoleOptions()
  ])
  
  if (isEdit.value) {
    await getUser()
  }
})
</script>

<style scoped>
.user-form {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #333;
}
</style> 