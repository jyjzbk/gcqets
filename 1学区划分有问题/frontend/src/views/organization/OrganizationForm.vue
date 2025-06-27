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
                :data="organizationTreeOptions" 
                :props="treeSelectProps"
                placeholder="请选择父级组织"
                check-strictly
                clearable
                filterable
                style="width: 100%"
                :disabled="!canSelectParent"
                node-key="id"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="组织类型" prop="type">
              <el-select v-model="form.type" placeholder="请选择组织类型" style="width: 100%">
                <el-option label="公司" value="company" />
                <el-option label="部门" value="department" />
                <el-option label="小组" value="group" />
              </el-select>
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
    // 不能选择自己或自己的后代作为父级
    if (!isEdit.value) return false;
    const currentId = route.params.id;
    return data.id === currentId || 
           (data.path && data.path.includes(currentId)) ||
           (data.children && data.children.some(child => child.id === currentId));
  }
}

// 获取组织树
const getOrganizationTree = async () => {
  try {
    await organizationStore.getOrganizationTree()
    organizationTree.value = organizationStore.organizationTree
    
    // 确保根组织存在
    if (organizationTree.value.length === 0) {
      const res = await organizationStore.getOrganizations({ level: 1 })
      organizationTree.value = res.data
    }
  } catch (error) {
    console.error('Get organization tree error:', error)
  }
}

// 获取组织详情
const getOrganization = async () => {
  try {
    await organizationStore.getOrganization(route.params.id)
    const org = organizationStore.currentOrganization
    
    Object.assign(form, {
      name: org.name,
      code: org.code,
      parent_id: org.parent_id,
      level: org.level,
      description: org.description || '',
      status: org.status,
      sort_order: org.sort_order,
      contact_person: org.contact_person || '',
      contact_phone: org.contact_phone || '',
      contact_email: org.contact_email || '',
      address: org.address || ''
    })
  } catch (error) {
    console.error('Get organization error:', error)
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    loading.value = true
    
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
</style>  
