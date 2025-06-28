<template>
  <div class="role-form">
    <div class="page-header">
      <h2>{{ isEdit ? '编辑角色' : '创建角色' }}</h2>
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
            <el-form-item label="角色名称" prop="name">
              <el-input
                v-model="form.name"
                placeholder="请输入角色名称"
                maxlength="50"
                :disabled="isEdit && form.is_system"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="显示名称" prop="display_name">
              <el-input
                v-model="form.display_name"
                placeholder="请输入显示名称"
                maxlength="100"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="角色级别" prop="level">
              <el-select v-model="form.level" placeholder="请选择角色级别" style="width: 100%">
                <el-option label="系统管理员" :value="1" />
                <el-option label="组织管理员" :value="2" />
                <el-option label="部门管理员" :value="3" />
                <el-option label="普通用户" :value="4" />
                <el-option label="受限用户" :value="5" />
              </el-select>
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="状态" prop="status">
              <el-select v-model="form.status" placeholder="请选择状态" style="width: 100%">
                <el-option label="启用" value="active" />
                <el-option label="禁用" value="inactive" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="4"
            placeholder="请输入描述信息"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
        
        <el-form-item label="权限" prop="permission_ids">
          <el-tree
            ref="permissionTreeRef"
            :data="permissionTree"
            :props="treeProps"
            show-checkbox
            node-key="id"
            :default-checked-keys="form.permission_ids"
            style="max-height: 400px; overflow-y: auto"
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
import { useRoleStore } from '../../stores/role'
import { permissionApi } from '../../api/permission'
import { ElMessage } from 'element-plus'

const route = useRoute()
const router = useRouter()
const roleStore = useRoleStore()

const formRef = ref()
const permissionTreeRef = ref()
const loading = ref(false)
const permissionTree = ref([])

const isEdit = computed(() => !!route.params.id)

// 表单数据
const form = reactive({
  name: '',
  display_name: '',
  description: '',
  level: 4,
  status: 'active',
  is_system: false,
  sort_order: 0,
  permission_ids: []
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入角色名称', trigger: 'blur' },
    { max: 50, message: '角色名称不能超过50个字符', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_-]+$/, message: '角色名称只能包含字母、数字、下划线和连字符', trigger: 'blur' }
  ],
  display_name: [
    { required: true, message: '请输入显示名称', trigger: 'blur' },
    { max: 100, message: '显示名称不能超过100个字符', trigger: 'blur' }
  ],
  level: [
    { required: true, message: '请选择角色级别', trigger: 'change' }
  ]
}

// 树形配置
const treeProps = {
  children: 'children',
  label: 'display_name',
  value: 'id'
}

// 获取权限树
const getPermissionTree = async () => {
  try {
    const response = await permissionApi.getTree()
    permissionTree.value = response.data.data
  } catch (error) {
    console.error('Get permission tree error:', error)
  }
}

// 获取角色详情
const getRole = async () => {
  try {
    await roleStore.getRole(route.params.id)
    const role = roleStore.currentRole
    
    Object.assign(form, {
      name: role.name,
      display_name: role.display_name,
      description: role.description || '',
      level: role.level,
      status: role.status === true ? 'active' : (role.status === false ? 'inactive' : role.status),
      is_system: role.is_system,
      sort_order: role.sort_order,
      permission_ids: role.permissions?.map(p => p.id) || []
    })
  } catch (error) {
    console.error('Get role error:', error)
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    loading.value = true
    
    // 获取选中的权限
    const checkedNodes = permissionTreeRef.value.getCheckedNodes()
    form.permission_ids = checkedNodes.map(node => node.id)
    
    if (isEdit.value) {
      await roleStore.updateRole(route.params.id, form)
      ElMessage.success('更新成功')
    } else {
      await roleStore.createRole(form)
      ElMessage.success('创建成功')
    }
    
    router.push('/roles')
  } catch (error) {
    console.error('Submit error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await getPermissionTree()
  
  if (isEdit.value) {
    await getRole()
  }
})
</script>

<style scoped>
.role-form {
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