<template>
  <div class="education-zone-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <h2>学区管理</h2>
          <div class="header-actions">
            <el-button type="primary" @click="openCreateDialog">新增学区</el-button>
            <el-button type="success" @click="openAutoAssignDialog">自动分配学校</el-button>
          </div>
        </div>
      </template>
      
      <!-- 搜索区域 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="区县">
          <el-select v-model="searchForm.district_id" placeholder="选择区县" clearable filterable>
            <el-option
              v-for="district in districtOptions"
              :key="district.id"
              :label="district.name"
              :value="district.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="选择状态" clearable>
            <el-option label="活跃" value="active" />
            <el-option label="非活跃" value="inactive" />
            <el-option label="规划中" value="planning" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="searchForm.search" placeholder="学区名称/编码" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="searchZones">搜索</el-button>
          <el-button @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>
      
      <!-- 学区列表 -->
      <el-table :data="zones" style="width: 100%" border v-loading="loading">
        <el-table-column prop="name" label="学区名称" min-width="150" />
        <el-table-column prop="code" label="学区编码" width="120" />
        <el-table-column label="所属区县" width="150">
          <template #default="scope">
            {{ scope.row.district?.name || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="school_count" label="学校数量" width="100" sortable />
        <el-table-column label="学生容量" width="180">
          <template #default="scope">
            <el-progress 
              :percentage="scope.row.utilization_percentage || 0" 
              :format="percentageFormat"
              :status="getUtilizationStatus(scope.row.utilization_percentage)"
            />
            <div class="capacity-text">
              {{ scope.row.current_students || 0 }}/{{ scope.row.student_capacity || 0 }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">{{ getStatusText(scope.row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="管理员" width="150">
          <template #default="scope">
            {{ scope.row.manager_name || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" width="250" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="viewZone(scope.row)">查看</el-button>
            <el-button type="success" size="small" @click="openSchoolsDialog(scope.row)">学校管理</el-button>
            <el-button type="warning" size="small" @click="editZone(scope.row)">编辑</el-button>
            <el-button type="danger" size="small" @click="deleteZone(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
    
    <!-- 创建/编辑学区对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑学区' : '新增学区'"
      width="60%"
    >
      <el-form :model="zoneForm" ref="zoneFormRef" label-width="120px" :rules="rules">
        <el-form-item label="学区名称" prop="name">
          <el-input v-model="zoneForm.name" placeholder="请输入学区名称" />
        </el-form-item>
        <el-form-item label="学区编码" prop="code">
          <el-input v-model="zoneForm.code" placeholder="请输入学区编码" />
        </el-form-item>
        <el-form-item label="所属区县" prop="district_id">
          <el-select v-model="zoneForm.district_id" placeholder="选择区县" filterable>
            <el-option
              v-for="district in districtOptions"
              :key="district.id"
              :label="district.name"
              :value="district.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input v-model="zoneForm.description" type="textarea" rows="3" placeholder="请输入学区描述" />
        </el-form-item>
        <el-form-item label="学生容量" prop="student_capacity">
          <el-input-number v-model="zoneForm.student_capacity" :min="0" :step="100" />
        </el-form-item>
        <el-form-item label="管理员姓名" prop="manager_name">
          <el-input v-model="zoneForm.manager_name" placeholder="请输入管理员姓名" />
        </el-form-item>
        <el-form-item label="管理员电话" prop="manager_phone">
          <el-input v-model="zoneForm.manager_phone" placeholder="请输入管理员电话" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-select v-model="zoneForm.status" placeholder="选择状态">
            <el-option label="活跃" value="active" />
            <el-option label="非活跃" value="inactive" />
            <el-option label="规划中" value="planning" />
          </el-select>
        </el-form-item>
        <el-form-item label="中心经度" prop="center_longitude">
          <el-input v-model="zoneForm.center_longitude" placeholder="请输入中心经度" />
        </el-form-item>
        <el-form-item label="中心纬度" prop="center_latitude">
          <el-input v-model="zoneForm.center_latitude" placeholder="请输入中心纬度" />
        </el-form-item>
        <el-form-item label="面积(平方公里)" prop="area">
          <el-input-number v-model="zoneForm.area" :min="0" :precision="2" :step="0.1" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="submitZoneForm" :loading="submitting">确定</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 学校管理对话框 -->
    <el-dialog
      v-model="schoolsDialogVisible"
      title="学区学校管理"
      width="80%"
    >
      <div v-if="currentZone">
        <h3>{{ currentZone.name }} - 学校列表</h3>
        
        <div class="schools-actions">
          <el-button type="primary" @click="openAddSchoolsDialog">添加学校</el-button>
        </div>
        
        <el-table :data="zoneSchools" style="width: 100%" border v-loading="loadingSchools">
          <el-table-column prop="name" label="学校名称" min-width="150" />
          <el-table-column prop="code" label="学校编码" width="120" />
          <el-table-column prop="student_count" label="学生数量" width="100" sortable />
          <el-table-column label="分配方式" width="100">
            <template #default="scope">
              <el-tag :type="scope.row.pivot?.assignment_type === 'auto' ? 'success' : 'warning'">
                {{ scope.row.pivot?.assignment_type === 'auto' ? '自动' : '手动' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="距离" width="100">
            <template #default="scope">
              {{ scope.row.pivot?.distance ? scope.row.pivot.distance + 'km' : '-' }}
            </template>
          </el-table-column>
          <el-table-column prop="pivot.assignment_reason" label="分配原因" min-width="150" />
          <el-table-column label="分配时间" width="180">
            <template #default="scope">
              {{ scope.row.pivot?.assigned_at || '-' }}
            </template>
          </el-table-column>
          <el-table-column label="操作" width="100" fixed="right">
            <template #default="scope">
              <el-button type="danger" size="small" @click="removeSchool(scope.row)">移除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="schoolsDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 添加学校对话框 -->
    <el-dialog
      v-model="addSchoolsDialogVisible"
      title="添加学校到学区"
      width="70%"
    >
      <div v-if="currentZone">
        <el-form :inline="true" :model="schoolSearchForm" class="search-form">
          <el-form-item label="学校名称">
            <el-input v-model="schoolSearchForm.name" placeholder="输入学校名称搜索" clearable />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="searchAvailableSchools">搜索</el-button>
          </el-form-item>
        </el-form>
        
        <el-table 
          :data="availableSchools" 
          style="width: 100%" 
          border 
          v-loading="loadingAvailableSchools"
          @selection-change="handleSchoolSelectionChange"
        >
          <el-table-column type="selection" width="55" />
          <el-table-column prop="name" label="学校名称" min-width="150" />
          <el-table-column prop="code" label="学校编码" width="120" />
          <el-table-column prop="student_count" label="学生数量" width="100" />
          <el-table-column prop="address" label="地址" min-width="200" />
          <el-table-column label="坐标" width="180">
            <template #default="scope">
              <span v-if="scope.row.longitude && scope.row.latitude">
                {{ scope.row.longitude }}, {{ scope.row.latitude }}
              </span>
              <span v-else>-</span>
            </template>
          </el-table-column>
        </el-table>
        
        <div class="assignment-form">
          <el-form :model="assignmentForm" label-width="120px">
            <el-form-item label="分配方式">
              <el-radio-group v-model="assignmentForm.assignment_type">
                <el-radio label="manual">手动分配</el-radio>
                <el-radio label="auto">自动分配</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="分配原因">
              <el-input v-model="assignmentForm.assignment_reason" type="textarea" rows="2" placeholder="请输入分配原因" />
            </el-form-item>
          </el-form>
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="addSchoolsDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="assignSchools" :loading="assigningSchools">确定</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 自动分配学校对话框 -->
    <el-dialog
      v-model="autoAssignDialogVisible"
      title="自动分配学校到学区"
      width="50%"
    >
      <el-form :model="autoAssignForm" ref="autoAssignFormRef" label-width="140px" :rules="autoAssignRules">
        <el-form-item label="区县" prop="district_id">
          <el-select v-model="autoAssignForm.district_id" placeholder="选择区县" filterable>
            <el-option
              v-for="district in districtOptions"
              :key="district.id"
              :label="district.name"
              :value="district.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="最大距离(公里)" prop="max_distance">
          <el-input-number v-model="autoAssignForm.max_distance" :min="0" :max="100" :precision="1" :step="0.5" />
        </el-form-item>
        <el-form-item label="覆盖已分配学校">
          <el-switch v-model="autoAssignForm.override_existing" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="autoAssignDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="submitAutoAssign" :loading="autoAssigning">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import axios from 'axios';

// 学区列表数据
const zones = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const pageSize = ref(20);
const total = ref(0);

// 区县选项
const districtOptions = ref([]);

// 搜索表单
const searchForm = reactive({
  district_id: '',
  status: '',
  search: ''
});

// 学区表单
const zoneForm = reactive({
  name: '',
  code: '',
  district_id: '',
  description: '',
  student_capacity: 0,
  manager_name: '',
  manager_phone: '',
  status: 'active',
  center_longitude: '',
  center_latitude: '',
  area: 0
});

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入学区名称', trigger: 'blur' },
    { max: 255, message: '长度不能超过255个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入学区编码', trigger: 'blur' },
    { max: 50, message: '长度不能超过50个字符', trigger: 'blur' }
  ],
  district_id: [
    { required: true, message: '请选择所属区县', trigger: 'change' }
  ],
  status: [
    { required: true, message: '请选择状态', trigger: 'change' }
  ]
};

// 对话框状态
const dialogVisible = ref(false);
const isEdit = ref(false);
const submitting = ref(false);
const zoneFormRef = ref(null);

// 学校管理对话框
const schoolsDialogVisible = ref(false);
const currentZone = ref(null);
const zoneSchools = ref([]);
const loadingSchools = ref(false);

// 添加学校对话框
const addSchoolsDialogVisible = ref(false);
const availableSchools = ref([]);
const loadingAvailableSchools = ref(false);
const selectedSchools = ref([]);
const schoolSearchForm = reactive({
  name: ''
});
const assignmentForm = reactive({
  assignment_type: 'manual',
  assignment_reason: ''
});
const assigningSchools = ref(false);

// 自动分配对话框
const autoAssignDialogVisible = ref(false);
const autoAssignForm = reactive({
  district_id: '',
  max_distance: 5,
  override_existing: false
});
const autoAssignRules = {
  district_id: [
    { required: true, message: '请选择区县', trigger: 'change' }
  ],
  max_distance: [
    { required: true, message: '请输入最大距离', trigger: 'blur' }
  ]
};
const autoAssignFormRef = ref(null);
const autoAssigning = ref(false);

// 获取区县列表
const getDistricts = async () => {
  try {
    const response = await axios.get('/api/organizations', {
      params: {
        type: 'district',
        level: 3
      }
    });
    districtOptions.value = response.data.data;
  } catch (error) {
    console.error('获取区县列表失败:', error);
    ElMessage.error('获取区县列表失败');
  }
};

// 获取学区列表
const getZones = async () => {
  loading.value = true;
  try {
    const params = {
      page: currentPage.value,
      per_page: pageSize.value,
      ...searchForm
    };
    
    const response = await axios.get('/api/education-zones', { params });
    zones.value = response.data.data.data;
    total.value = response.data.data.total;
  } catch (error) {
    console.error('获取学区列表失败:', error);
    ElMessage.error('获取学区列表失败');
  } finally {
    loading.value = false;
  }
};

// 搜索学区
const searchZones = () => {
  currentPage.value = 1;
  getZones();
};

// 重置搜索
const resetSearch = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = '';
  });
  searchZones();
};

// 分页处理
const handleSizeChange = (val) => {
  pageSize.value = val;
  getZones();
};

const handleCurrentChange = (val) => {
  currentPage.value = val;
  getZones();
};

// 打开创建对话框
const openCreateDialog = () => {
  isEdit.value = false;
  Object.keys(zoneForm).forEach(key => {
    zoneForm[key] = key === 'status' ? 'active' : (key === 'student_capacity' || key === 'area' ? 0 : '');
  });
  dialogVisible.value = true;
};

// 编辑学区
const editZone = (zone) => {
  isEdit.value = true;
  currentZone.value = zone;
  Object.keys(zoneForm).forEach(key => {
    zoneForm[key] = zone[key] !== undefined ? zone[key] : '';
  });
  dialogVisible.value = true;
};

// 提交学区表单
const submitZoneForm = async () => {
  if (!zoneFormRef.value) return;
  
  await zoneFormRef.value.validate(async (valid) => {
    if (!valid) {
      ElMessage.error('请完善表单信息');
      return;
    }
    
    submitting.value = true;
    
    try {
      if (isEdit.value) {
        await axios.put(`/api/education-zones/${currentZone.value.id}`, zoneForm);
        ElMessage.success('学区更新成功');
      } else {
        await axios.post('/api/education-zones', zoneForm);
        ElMessage.success('学区创建成功');
      }
      
      dialogVisible.value = false;
      getZones();
    } catch (error) {
      console.error('保存学区失败:', error);
      ElMessage.error(error.response?.data?.message || '保存学区失败');
    } finally {
      submitting.value = false;
    }
  });
};

// 删除学区
const deleteZone = (zone) => {
  ElMessageBox.confirm(
    `确定要删除学区 "${zone.name}" 吗？`,
    '删除确认',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await axios.delete(`/api/education-zones/${zone.id}`);
      ElMessage.success('学区删除成功');
      getZones();
    } catch (error) {
      console.error('删除学区失败:', error);
      ElMessage.error(error.response?.data?.message || '删除学区失败');
    }
  }).catch(() => {});
};

// 查看学区详情
const viewZone = (zone) => {
  // 这里可以实现查看详情的逻辑，例如跳转到详情页
  ElMessage.info('查看学区详情功能尚未实现');
};

// 打开学校管理对话框
const openSchoolsDialog = async (zone) => {
  currentZone.value = zone;
  schoolsDialogVisible.value = true;
  loadingSchools.value = true;
  
  try {
    const response = await axios.get(`/api/education-zones/${zone.id}/schools`);
    zoneSchools.value = response.data.data;
  } catch (error) {
    console.error('获取学区学校列表失败:', error);
    ElMessage.error('获取学区学校列表失败');
  } finally {
    loadingSchools.value = false;
  }
};

// 打开添加学校对话框
const openAddSchoolsDialog = () => {
  addSchoolsDialogVisible.value = true;
  availableSchools.value = [];
  selectedSchools.value = [];
  schoolSearchForm.name = '';
  assignmentForm.assignment_type = 'manual';
  assignmentForm.assignment_reason = '';
  
  // 加载可用学校
  searchAvailableSchools();
};

// 搜索可用学校
const searchAvailableSchools = async () => {
  if (!currentZone.value) return;
  
  loadingAvailableSchools.value = true;
  
  try {
    // 这里应该调用一个专门的API来获取可以添加到学区的学校
    // 为了简化，我们这里使用组织API并过滤
    const params = {
      type: 'school',
      parent_id: currentZone.value.district_id,
      search: schoolSearchForm.name
    };
    
    const response = await axios.get('/api/organizations', { params });
    
    // 过滤掉已经在学区中的学校
    const existingSchoolIds = zoneSchools.value.map(school => school.id);
    availableSchools.value = response.data.data.filter(school => !existingSchoolIds.includes(school.id));
  } catch (error) {
    console.error('获取可用学校列表失败:', error);
    ElMessage.error('获取可用学校列表失败');
  } finally {
    loadingAvailableSchools.value = false;
  }
};

// 处理学校选择变更
const handleSchoolSelectionChange = (selection) => {
  selectedSchools.value = selection;
};

// 分配学校到学区
const assignSchools = async () => {
  if (!currentZone.value || selectedSchools.value.length === 0) {
    ElMessage.warning('请选择要添加的学校');
    return;
  }
  
  assigningSchools.value = true;
  
  try {
    const payload = {
      school_ids: selectedSchools.value.map(school => school.id),
      assignment_type: assignmentForm.assignment_type,
      assignment_reason: assignmentForm.assignment_reason
    };
    
    await axios.post(`/api/education-zones/${currentZone.value.id}/schools`, payload);
    
    ElMessage.success(`成功添加 ${selectedSchools.value.length} 所学校到学区`);
    
    // 关闭对话框并刷新学校列表
    addSchoolsDialogVisible.value = false;
    openSchoolsDialog(currentZone.value);
  } catch (error) {
    console.error('添加学校失败:', error);
    ElMessage.error(error.response?.data?.message || '添加学校失败');
  } finally {
    assigningSchools.value = false;
  }
};

// 从学区移除学校
const removeSchool = (school) => {
  if (!currentZone.value) return;
  
  ElMessageBox.confirm(
    `确定要从学区移除学校 "${school.name}" 吗？`,
    '移除确认',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await axios.delete(`/api/education-zones/${currentZone.value.id}/schools`, {
        data: {
          school_ids: [school.id]
        }
      });
      
      ElMessage.success('学校移除成功');
      
      // 刷新学校列表
      openSchoolsDialog(currentZone.value);
    } catch (error) {
      console.error('移除学校失败:', error);
      ElMessage.error(error.response?.data?.message || '移除学校失败');
    }
  }).catch(() => {});
};

// 打开自动分配对话框
const openAutoAssignDialog = () => {
  autoAssignForm.district_id = '';
  autoAssignForm.max_distance = 5;
  autoAssignForm.override_existing = false;
  autoAssignDialogVisible.value = true;
};

// 提交自动分配
const submitAutoAssign = async () => {
  if (!autoAssignFormRef.value) return;
  
  await autoAssignFormRef.value.validate(async (valid) => {
    if (!valid) {
      ElMessage.error('请完善表单信息');
      return;
    }
    
    autoAssigning.value = true;
    
    try {
      const response = await axios.post('/api/education-zones/auto-assign', autoAssignForm);
      
      ElMessage.success(response.data.message || '自动分配完成');
      autoAssignDialogVisible.value = false;
      
      // 刷新学区列表
      getZones();
    } catch (error) {
      console.error('自动分配失败:', error);
      ElMessage.error(error.response?.data?.message || '自动分配失败');
    } finally {
      autoAssigning.value = false;
    }
  });
};

// 获取状态类型
const getStatusType = (status) => {
  switch (status) {
    case 'active': return 'success';
    case 'inactive': return 'info';
    case 'planning': return 'warning';
    default: return 'info';
  }
};

// 获取状态文本
const getStatusText = (status) => {
  switch (status) {
    case 'active': return '活跃';
    case 'inactive': return '非活跃';
    case 'planning': return '规划中';
    default: return status;
  }
};

// 获取利用率状态
const getUtilizationStatus = (percentage) => {
  if (percentage >= 90) return 'exception';
  if (percentage >= 70) return 'warning';
  return 'success';
};

// 格式化百分比
const percentageFormat = (percentage) => {
  return `${percentage}%`;
};

// 组件挂载时
onMounted(() => {
  getDistricts();
  getZones();
});
</script>

<style scoped>
.education-zone-container {
  padding: 20px;
}

.box-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.search-form {
  margin-bottom: 20px;
}

.assignment-form {
  margin-top: 20px;
}

.capacity-text {
  margin-top: 5px;
  font-size: 12px;
  color: #909399;
}
</style>
