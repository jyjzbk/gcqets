<template>
  <div class="organization-tree">
    <div class="tree-header">
      <h3>组织机构树</h3>
      <el-button
        v-permission="'organization.create'"
        type="primary"
        size="small"
        @click="handleAdd"
      >
        <el-icon><Plus /></el-icon>
        添加组织
      </el-button>
    </div>
    
    <el-tree
      ref="treeRef"
      :data="treeData"
      :props="treeProps"
      :expand-on-click-node="false"
      :default-expand-all="false"
      node-key="id"
      draggable
      @node-click="handleNodeClick"
      @node-drop="handleNodeDrop"
    >
      <template #default="{ node, data }">
        <div class="tree-node">
          <div class="node-content">
            <el-icon class="node-icon">
              <OfficeBuilding />
            </el-icon>
            <span class="node-label">{{ data.name }}</span>
            <el-tag size="small" :type="getLevelTagType(data.level)">
              {{ getLevelName(data.level) }}
            </el-tag>
            <el-tag v-if="!data.status" size="small" type="danger">禁用</el-tag>
          </div>
          
          <div class="node-actions">
            <el-button
              v-permission="'organization.create'"
              type="primary"
              text
              size="small"
              @click.stop="handleAddChild(data)"
            >
              <el-icon><Plus /></el-icon>
            </el-button>
            
            <el-button
              v-permission="'organization.update'"
              type="primary"
              text
              size="small"
              @click.stop="handleEdit(data)"
            >
              <el-icon><Edit /></el-icon>
            </el-button>
            
            <el-button
              v-permission="'organization.delete'"
              type="danger"
              text
              size="small"
              @click.stop="handleDelete(data)"
            >
              <el-icon><Delete /></el-icon>
            </el-button>
          </div>
        </div>
      </template>
    </el-tree>
    
    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
      @close="resetForm"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="组织名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入组织名称" />
        </el-form-item>
        
        <el-form-item label="组织编码" prop="code">
          <el-input v-model="form.code" placeholder="请输入组织编码" />
        </el-form-item>
        
        <el-form-item label="组织级别" prop="level">
          <el-select v-model="form.level" placeholder="请选择组织级别" style="width: 100%">
            <el-option label="省级" :value="1" />
            <el-option label="市级" :value="2" />
            <el-option label="区县级" :value="3" />
            <el-option label="学区级" :value="4" />
            <el-option label="学校级" :value="5" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入描述信息"
          />
        </el-form-item>
        
        <el-form-item label="状态" prop="status">
          <el-switch v-model="form.status" />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="loading">
          {{ isEdit ? '更新' : '创建' }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useOrganizationStore } from '../stores/organization'
import { ElMessage, ElMessageBox } from 'element-plus'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['node-click', 'refresh'])

const organizationStore = useOrganizationStore()

const treeRef = ref()
const dialogVisible = ref(false)
const loading = ref(false)
const currentNode = ref(null)

// 表单数据
const form = reactive({
  name: '',
  code: '',
  parent_id: null,
  level: 1,
  description: '',
  status: true
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入组织名称', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入组织编码', trigger: 'blur' }
  ],
  level: [
    { required: true, message: '请选择组织级别', trigger: 'change' }
  ]
}

const treeData = computed(() => props.data)
const isEdit = computed(() => !!currentNode.value)
const dialogTitle = computed(() => isEdit.value ? '编辑组织机构' : '创建组织机构')

// 树形配置
const treeProps = {
  children: 'children',
  label: 'name'
}

// 获取级别名称
const getLevelName = (level) => {
  const levelMap = {
    1: '省级',
    2: '市级',
    3: '区县级',
    4: '学区级',
    5: '学校级'
  }
  return levelMap[level] || '未知'
}

// 获取级别标签类型
const getLevelTagType = (level) => {
  const typeMap = {
    1: 'danger',
    2: 'warning',
    3: 'info',
    4: 'success',
    5: 'primary'
  }
  return typeMap[level] || 'info'
}

// 节点点击
const handleNodeClick = (data) => {
  emit('node-click', data)
}

// 节点拖拽
const handleNodeDrop = async (draggingNode, dropNode, dropType) => {
  try {
    const draggingData = draggingNode.data
    let parentId = null
    
    if (dropType === 'inner') {
      parentId = dropNode.data.id
    } else if (dropNode.parent && dropNode.parent.data) {
      parentId = dropNode.parent.data.id
    }
    
    await organizationStore.moveOrganization(draggingData.id, {
      parent_id: parentId
    })
    
    ElMessage.success('移动成功')
    emit('refresh')
  } catch (error) {
    console.error('Move organization error:', error)
  }
}

// 添加根组织
const handleAdd = () => {
  currentNode.value = null
  form.parent_id = null
  form.level = 1
  dialogVisible.value = true
}

// 添加子组织
const handleAddChild = (data) => {
  currentNode.value = null
  form.parent_id = data.id
  form.level = Math.min(data.level + 1, 5)
  dialogVisible.value = true
}

// 编辑组织
const handleEdit = (data) => {
  currentNode.value = data
  Object.assign(form, {
    name: data.name,
    code: data.code,
    parent_id: data.parent_id,
    level: data.level,
    description: data.description || '',
    status: data.status
  })
  dialogVisible.value = true
}

// 删除组织
const handleDelete = async (data) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除组织机构"${data.name}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    await organizationStore.deleteOrganization(data.id)
    ElMessage.success('删除成功')
    emit('refresh')
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Delete organization error:', error)
    }
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    loading.value = true
    
    if (isEdit.value) {
      await organizationStore.updateOrganization(currentNode.value.id, form)
      ElMessage.success('更新成功')
    } else {
      await organizationStore.createOrganization(form)
      ElMessage.success('创建成功')
    }
    
    dialogVisible.value = false
    emit('refresh')
  } catch (error) {
    console.error('Submit error:', error)
  } finally {
    loading.value = false
  }
}

// 重置表单
const resetForm = () => {
  Object.assign(form, {
    name: '',
    code: '',
    parent_id: null,
    level: 1,
    description: '',
    status: true
  })
  currentNode.value = null
}
</script>

<style scoped>
.organization-tree {
  padding: 20px;
}

.tree-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.tree-header h3 {
  margin: 0;
  color: #333;
}

.tree-node {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 4px 0;
}

.node-content {
  display: flex;
  align-items: center;
  flex: 1;
}

.node-icon {
  margin-right: 8px;
  color: #409EFF;
}

.node-label {
  margin-right: 8px;
  font-weight: 500;
}

.node-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.3s;
}

.tree-node:hover .node-actions {
  opacity: 1;
}
</style> 