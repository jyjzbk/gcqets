<template>
  <el-dialog
    v-model="visible"
    title="组织权限管理"
    width="1000px"
    :close-on-click-modal="false"
  >
    <div class="org-permission-manager">
      <!-- 组织选择 -->
      <div class="org-selector">
        <el-select
          v-model="selectedOrgId"
          placeholder="选择组织机构"
          filterable
          @change="loadOrgPermissions"
          style="width: 300px"
        >
          <el-option
            v-for="org in organizations"
            :key="org.id"
            :label="org.name"
            :value="org.id"
          >
            <span>{{ org.name }}</span>
            <span style="float: right; color: #8492a6; font-size: 13px">
              {{ org.type_label }}
            </span>
          </el-option>
        </el-select>
        
        <el-button type="primary" @click="loadOrgPermissions" :loading="loading">
          查询权限
        </el-button>
      </div>

      <!-- 权限继承关系 -->
      <div v-if="selectedOrgId" class="permission-inheritance">
        <h3>权限继承关系</h3>
        <div class="inheritance-path">
          <el-tag
            v-for="(org, index) in inheritancePath"
            :key="org.id"
            :type="index === inheritancePath.length - 1 ? 'primary' : 'info'"
            class="path-tag"
          >
            {{ org.name }}
          </el-tag>
          <span v-if="inheritancePath.length > 1" class="inheritance-note">
            权限从上级组织继承
          </span>
        </div>
      </div>

      <!-- 权限列表 -->
      <div v-if="selectedOrgId" class="permission-list">
        <div class="list-header">
          <h3>组织权限列表</h3>
          <div class="header-actions">
            <el-button type="success" @click="showAddPermission = true">
              <el-icon><Plus /></el-icon>
              添加权限
            </el-button>
            <el-button type="warning" @click="batchRevoke" :disabled="!selectedPermissions.length">
              <el-icon><Delete /></el-icon>
              批量撤销
            </el-button>
          </div>
        </div>

        <el-table
          :data="orgPermissions"
          @selection-change="handleSelectionChange"
          v-loading="loading"
        >
          <el-table-column type="selection" width="55" />
          <el-table-column prop="permission_name" label="权限名称" width="200" />
          <el-table-column prop="permission_display_name" label="权限描述" />
          <el-table-column prop="source" label="权限来源" width="120">
            <template #default="scope">
              <el-tag :type="getSourceType(scope.row.source)">
                {{ scope.row.source_label }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="granted_at" label="分配时间" width="150" />
          <el-table-column prop="granted_by" label="分配人" width="100" />
          <el-table-column label="操作" width="150">
            <template #default="scope">
              <el-button
                v-if="scope.row.source === 'direct'"
                type="danger"
                size="small"
                @click="revokePermission(scope.row)"
              >
                撤销
              </el-button>
              <el-button
                v-else
                type="info"
                size="small"
                disabled
              >
                继承权限
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <!-- 子组织权限概览 -->
      <div v-if="selectedOrgId && childOrganizations.length" class="child-orgs">
        <h3>下级组织权限概览</h3>
        <el-table :data="childOrganizations">
          <el-table-column prop="name" label="组织名称" />
          <el-table-column prop="type_label" label="组织类型" width="100" />
          <el-table-column prop="permission_count" label="权限数量" width="100">
            <template #default="scope">
              <el-tag type="primary">{{ scope.row.permission_count }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="inherited_count" label="继承权限" width="100">
            <template #default="scope">
              <el-tag type="success">{{ scope.row.inherited_count }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="100">
            <template #default="scope">
              <el-button type="text" @click="selectOrganization(scope.row.id)">
                查看详情
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>

    <!-- 添加权限对话框 -->
    <el-dialog
      v-model="showAddPermission"
      title="为组织添加权限"
      width="600px"
      append-to-body
    >
      <el-form :model="addPermissionForm" label-width="100px">
        <el-form-item label="选择权限">
          <el-tree
            ref="addPermissionTreeRef"
            :data="availablePermissions"
            :props="permissionTreeProps"
            show-checkbox
            node-key="id"
            @check="handleAddPermissionCheck"
          />
        </el-form-item>
        <el-form-item label="分配原因">
          <el-input
            v-model="addPermissionForm.reason"
            type="textarea"
            placeholder="请输入分配原因"
            :rows="3"
          />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="showAddPermission = false">取消</el-button>
        <el-button type="primary" @click="confirmAddPermission" :loading="adding">
          确认添加
        </el-button>
      </template>
    </el-dialog>

    <template #footer>
      <el-button @click="visible = false">关闭</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Delete } from '@element-plus/icons-vue'

// Props & Emits
const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'success'])

// 响应式数据
const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const loading = ref(false)
const adding = ref(false)
const selectedOrgId = ref(null)
const selectedPermissions = ref([])
const showAddPermission = ref(false)

// 数据
const organizations = ref([])
const orgPermissions = ref([])
const inheritancePath = ref([])
const childOrganizations = ref([])
const availablePermissions = ref([])

// 表单
const addPermissionForm = reactive({
  permissionIds: [],
  reason: ''
})

// 树形配置
const permissionTreeProps = {
  children: 'children',
  label: 'display_name'
}

// 方法
const loadOrgPermissions = async () => {
  if (!selectedOrgId.value) return
  
  try {
    loading.value = true
    
    // 模拟API调用
    // const response = await permissionApi.getOrgPermissions(selectedOrgId.value)
    
    // 模拟数据
    orgPermissions.value = [
      {
        id: 1,
        permission_name: 'user.view',
        permission_display_name: '查看用户',
        source: 'direct',
        source_label: '直接分配',
        granted_at: '2025-06-28 10:00:00',
        granted_by: '系统管理员'
      },
      {
        id: 2,
        permission_name: 'user.create',
        permission_display_name: '创建用户',
        source: 'inherited',
        source_label: '继承权限',
        granted_at: '2025-06-28 09:00:00',
        granted_by: '上级组织'
      }
    ]
    
    // 继承路径
    inheritancePath.value = [
      { id: 1, name: '河北省' },
      { id: 2, name: '石家庄市' },
      { id: 3, name: '藁城区' },
      { id: 4, name: '廉州学区' }
    ]
    
    // 子组织
    childOrganizations.value = [
      {
        id: 5,
        name: '东城小学',
        type_label: '学校',
        permission_count: 15,
        inherited_count: 12
      },
      {
        id: 6,
        name: '西城小学',
        type_label: '学校',
        permission_count: 18,
        inherited_count: 12
      }
    ]
    
  } catch (error) {
    ElMessage.error('加载权限失败')
  } finally {
    loading.value = false
  }
}

const handleSelectionChange = (selection) => {
  selectedPermissions.value = selection
}

const getSourceType = (source) => {
  const types = {
    direct: 'primary',
    inherited: 'success',
    template: 'warning'
  }
  return types[source] || 'info'
}

const selectOrganization = (orgId) => {
  selectedOrgId.value = orgId
  loadOrgPermissions()
}

const revokePermission = async (permission) => {
  try {
    await ElMessageBox.confirm(
      `确定要撤销权限"${permission.permission_display_name}"吗？`,
      '确认撤销',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    // 调用API撤销权限
    // await permissionApi.revokeOrgPermission(selectedOrgId.value, permission.id)
    
    ElMessage.success('权限撤销成功')
    loadOrgPermissions()
    emit('success')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('撤销权限失败')
    }
  }
}

const batchRevoke = async () => {
  try {
    await ElMessageBox.confirm(
      `确定要批量撤销 ${selectedPermissions.value.length} 个权限吗？`,
      '确认批量撤销',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )
    
    const permissionIds = selectedPermissions.value.map(p => p.id)
    // await permissionApi.batchRevokeOrgPermissions(selectedOrgId.value, permissionIds)
    
    ElMessage.success('批量撤销成功')
    loadOrgPermissions()
    emit('success')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('批量撤销失败')
    }
  }
}

const handleAddPermissionCheck = (data, checked) => {
  addPermissionForm.permissionIds = checked.checkedKeys
}

const confirmAddPermission = async () => {
  if (!addPermissionForm.permissionIds.length) {
    ElMessage.warning('请选择要添加的权限')
    return
  }
  
  try {
    adding.value = true
    
    // 调用API添加权限
    // await permissionApi.grantOrgPermissions(selectedOrgId.value, addPermissionForm)
    
    ElMessage.success('权限添加成功')
    showAddPermission.value = false
    loadOrgPermissions()
    emit('success')
    
    // 重置表单
    addPermissionForm.permissionIds = []
    addPermissionForm.reason = ''
  } catch (error) {
    ElMessage.error('添加权限失败')
  } finally {
    adding.value = false
  }
}

// 监听对话框打开
watch(visible, (val) => {
  if (val) {
    loadData()
  }
})

const loadData = async () => {
  // 加载组织列表
  organizations.value = [
    { id: 1, name: '河北省', type_label: '省级' },
    { id: 2, name: '石家庄市', type_label: '市级' },
    { id: 3, name: '藁城区', type_label: '区县级' },
    { id: 4, name: '廉州学区', type_label: '学区级' },
    { id: 5, name: '东城小学', type_label: '学校级' }
  ]
  
  // 加载可用权限
  availablePermissions.value = [
    {
      id: 1,
      display_name: '用户管理',
      children: [
        { id: 2, display_name: '查看用户' },
        { id: 3, display_name: '创建用户' },
        { id: 4, display_name: '编辑用户' },
        { id: 5, display_name: '删除用户' }
      ]
    },
    {
      id: 6,
      display_name: '组织管理',
      children: [
        { id: 7, display_name: '查看组织' },
        { id: 8, display_name: '创建组织' },
        { id: 9, display_name: '编辑组织' }
      ]
    }
  ]
}
</script>

<style scoped>
.org-permission-manager {
  padding: 20px 0;
}

.org-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #ebeef5;
}

.permission-inheritance {
  margin-bottom: 24px;
}

.permission-inheritance h3 {
  margin-bottom: 12px;
  color: #303133;
}

.inheritance-path {
  display: flex;
  align-items: center;
  gap: 8px;
}

.path-tag {
  position: relative;
}

.path-tag:not(:last-child)::after {
  content: '→';
  position: absolute;
  right: -12px;
  color: #909399;
}

.inheritance-note {
  margin-left: 12px;
  font-size: 12px;
  color: #909399;
}

.permission-list,
.child-orgs {
  margin-bottom: 24px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.list-header h3 {
  margin: 0;
  color: #303133;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.child-orgs h3 {
  margin-bottom: 16px;
  color: #303133;
}
</style>
