<template>
  <div class="organization-form">
    <div class="page-header">
      <h2>{{ isEdit ? '编辑组织机构' : '创建组织机构' }}</h2>
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
            <el-form-item label="组织名称" prop="name">
              <el-input
                v-model="form.name"
                placeholder="请输入组织名称"
                maxlength="100"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="组织编码" prop="code">
              <el-input
                v-model="form.code"
                placeholder="请输入组织编码"
                maxlength="50"
                show-word-limit
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="父级组织" prop="parent_id">
              <el-tree-select
                v-model="form.parent_id"
                :data="organizationTree"
                :props="treeProps"
                placeholder="请选择父级组织（留空表示根组织）"
                check-strictly
                clearable
                style="width: 100%"
              />
              <div v-if="isEdit && currentParentName" class="parent-info">
                当前父级组织：{{ currentParentName }}
              </div>
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="组织级别" prop="level">
              <el-select v-model="form.level" placeholder="请选择组织级别" style="width: 100%">
                <el-option label="省级" :value="1" />
                <el-option label="市级" :value="2" />
                <el-option label="区县级" :value="3" />
                <el-option label="学区级" :value="4" />
                <el-option label="学校级" :value="5" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人" prop="contact_person">
              <el-input
                v-model="form.contact_person"
                placeholder="请输入联系人姓名"
                maxlength="50"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="联系电话" prop="contact_phone">
              <el-input
                v-model="form.contact_phone"
                placeholder="请输入联系电话"
                maxlength="20"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系邮箱" prop="contact_email">
              <el-input
                v-model="form.contact_email"
                placeholder="请输入联系邮箱"
                maxlength="100"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="排序" prop="sort_order">
              <el-input-number
                v-model="form.sort_order"
                :min="0"
                :max="999"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="地址" prop="address">
          <el-input
            v-model="form.address"
            placeholder="请输入地址"
            maxlength="200"
            show-word-limit
          />
        </el-form-item>
        
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
        
        <el-form-item label="状态" prop="status">
          <el-switch
            v-model="form.status"
            active-text="启用"
            inactive-text="禁用"
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
import { useOrganizationStore } from '../../stores/organization'
import { ElMessage } from 'element-plus'

const route = useRoute()
const router = useRouter()
const organizationStore = useOrganizationStore()

const formRef = ref()
const loading = ref(false)
const organizationTree = ref([])
const currentParentName = ref('')

const isEdit = computed(() => !!route.params.id)

// 表单数据
const form = reactive({
  name: '',
  code: '',
  parent_id: null,
  level: 1,
  description: '',
  status: true,
  sort_order: 0,
  contact_person: '',
  contact_phone: '',
  contact_email: '',
  address: ''
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入组织名称', trigger: 'blur' },
    { max: 100, message: '组织名称不能超过100个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入组织编码', trigger: 'blur' },
    { max: 50, message: '组织编码不能超过50个字符', trigger: 'blur' },
    { pattern: /^[A-Z0-9_-]+$/, message: '组织编码只能包含大写字母、数字、下划线和连字符', trigger: 'blur' }
  ],
  level: [
    { required: true, message: '请选择组织级别', trigger: 'change' }
  ]
}

// 树形选择器配置
const treeProps = {
  children: 'children',
  label: 'name',
  value: 'id',
  disabled: (data) => {
    // 编辑时不能选择自己或自己的后代作为父级
    if (!isEdit.value) return false
    const currentId = parseInt(route.params.id)

    // 不能选择自己
    if (data.id === currentId) return true

    // 不能选择自己的后代（通过路径判断）
    if (data.path && data.path.includes(`/${currentId}/`)) return true

    return false
  }
}

// 获取组织树
const getOrganizationTree = async () => {
  try {
    await organizationStore.getOrganizationTree()
    organizationTree.value = organizationStore.organizationTree

    // 如果树为空，尝试获取所有组织并构建树结构
    if (!organizationTree.value || organizationTree.value.length === 0) {
      await organizationStore.getOrganizations()
      const allOrgs = organizationStore.organizations
      organizationTree.value = buildTree(allOrgs)
    }

    console.log('Organization tree loaded:', organizationTree.value)
  } catch (error) {
    console.error('Get organization tree error:', error)
  }
}

// 构建树结构的辅助函数
const buildTree = (organizations, parentId = null) => {
  const tree = []
  for (const org of organizations) {
    if (org.parent_id === parentId) {
      const children = buildTree(organizations, org.id)
      const node = {
        ...org,
        children: children.length > 0 ? children : undefined
      }
      tree.push(node)
    }
  }
  return tree
}

// 获取组织详情
const getOrganization = async () => {
  try {
    await organizationStore.getOrganization(route.params.id)
    const org = organizationStore.currentOrganization

    console.log('Current organization:', org)
    console.log('Organization tree:', organizationTree.value)

    Object.assign(form, {
      name: org.name,
      code: org.code,
      parent_id: org.parent_id,
      level: org.level,
      description: org.description || '',
      status: org.status !== undefined ? org.status : true,
      sort_order: org.sort_order || 0,
      contact_person: org.contact_person || '',
      contact_phone: org.contact_phone || '',
      contact_email: org.contact_email || '',
      address: org.address || ''
    })

    // 获取父级组织名称
    if (org.parent_id) {
      const parentOrg = findOrgInTree(organizationTree.value, org.parent_id)
      currentParentName.value = parentOrg ? parentOrg.name : `组织ID: ${org.parent_id}`
    } else {
      currentParentName.value = '根组织'
    }

    console.log('Form after assignment:', form)
    console.log('Current parent name:', currentParentName.value)
  } catch (error) {
    console.error('Get organization error:', error)
  }
}

// 在组织树中查找指定ID的组织
const findOrgInTree = (tree, targetId) => {
  for (const org of tree) {
    if (org.id === targetId) {
      return org
    }
    if (org.children) {
      const found = findOrgInTree(org.children, targetId)
      if (found) return found
    }
  }
  return null
}

// 提交表单
const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    loading.value = true

    console.log('Submitting form data:', form)

    if (isEdit.value) {
      await organizationStore.updateOrganization(route.params.id, form)
      ElMessage.success('更新成功')
    } else {
      await organizationStore.createOrganization(form)
      ElMessage.success('创建成功')
    }

    router.push('/organizations')
  } catch (error) {
    console.error('Submit error:', error)

    // 显示具体的验证错误信息
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors || error.response.data.message
      console.error('Validation errors:', errors)

      if (typeof errors === 'object') {
        // 显示第一个验证错误
        const firstError = Object.values(errors)[0]
        ElMessage.error(Array.isArray(firstError) ? firstError[0] : firstError)
      } else {
        ElMessage.error(errors || '数据验证失败')
      }
    } else {
      ElMessage.error('提交失败，请重试')
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await getOrganizationTree()
  
  if (isEdit.value) {
    await getOrganization()
  }
})
</script>

<style scoped>
.organization-form {
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

.parent-info {
  margin-top: 5px;
  font-size: 12px;
  color: #666;
  background: #f5f7fa;
  padding: 4px 8px;
  border-radius: 4px;
  border-left: 3px solid #409eff;
}
</style>