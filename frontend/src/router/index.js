import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/auth/Login.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    name: 'Layout',
    component: () => import('../views/layout/Layout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/dashboard/Dashboard.vue'),
        meta: { title: '仪表板' }
      },
      {
        path: 'organizations',
        name: 'Organizations',
        component: () => import('../views/organization/OrganizationList.vue'),
        meta: { title: '组织机构管理' }
      },
      {
        path: 'organizations/create',
        name: 'OrganizationCreate',
        component: () => import('../views/organization/OrganizationForm.vue'),
        meta: { title: '创建组织机构' }
      },
      {
        path: 'organizations/:id/edit',
        name: 'OrganizationEdit',
        component: () => import('../views/organization/OrganizationForm.vue'),
        meta: { title: '编辑组织机构' }
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/user/UserList.vue'),
        meta: { title: '用户管理' }
      },
      {
        path: 'users/create',
        name: 'UserCreate',
        component: () => import('../views/user/UserForm.vue'),
        meta: { title: '创建用户' }
      },
      {
        path: 'users/:id/edit',
        name: 'UserEdit',
        component: () => import('../views/user/UserForm.vue'),
        meta: { title: '编辑用户' }
      },
      {
        path: 'roles',
        name: 'Roles',
        component: () => import('../views/role/RoleList.vue'),
        meta: { title: '角色管理' }
      },
      {
        path: 'roles/create',
        name: 'RoleCreate',
        component: () => import('../views/role/RoleForm.vue'),
        meta: { title: '创建角色' }
      },
      {
        path: 'roles/:id/edit',
        name: 'RoleEdit',
        component: () => import('../views/role/RoleForm.vue'),
        meta: { title: '编辑角色' }
      },
      {
        path: 'permissions',
        name: 'PermissionVisualization',
        component: () => import('../views/permission/PermissionVisualizationSimple.vue'),
        meta: { title: '权限可视化管理' }
      },
      {
        path: 'education-permissions',
        name: 'EducationPermissionManager',
        component: () => import('../views/permission/EducationPermissionManager.vue'),
        meta: { title: '教育权限管理' }
      },
      {
        path: 'permission-test',
        name: 'PermissionVisualizationTest',
        component: () => import('../views/permission/PermissionVisualizationTest.vue'),
        meta: { title: '权限管理测试' }
      },
      {
        path: 'permission-test',
        name: 'PermissionTest',
        component: () => import('../views/permission/PermissionTest.vue'),
        meta: { title: '权限功能测试' }
      },
      {
        path: 'schools',
        name: 'Schools',
        component: () => import('../views/organization/SchoolManagement.vue'),
        meta: { title: '学校管理' }
      },
      {
        path: 'schools/import',
        name: 'SchoolImport',
        component: () => import('../views/organization/SchoolManagement.vue'),
        meta: { title: '学校批量导入' }
      },
      {
        path: 'schools/dashboard',
        name: 'SchoolDashboard',
        component: () => import('../views/organization/SchoolManagement.vue'),
        meta: { title: '统计仪表板' }
      },
      // 实验目录管理路由
      {
        path: 'experiment-catalogs',
        name: 'ExperimentCatalogs',
        component: () => import('../views/experiment/ExperimentCatalogList.vue'),
        meta: { title: '实验目录管理' }
      },
      {
        path: 'curriculum-standards',
        name: 'CurriculumStandards',
        component: () => import('../views/experiment/CurriculumStandardList.vue'),
        meta: { title: '课程标准管理' }
      },
      {
        path: 'photo-templates',
        name: 'PhotoTemplates',
        component: () => import('../views/experiment/PhotoTemplateList.vue'),
        meta: { title: '照片模板管理' }
      },
      // 设备物料管理路由
      {
        path: 'equipment',
        name: 'Equipment',
        component: () => import('../views/equipment/EquipmentList.vue'),
        meta: { title: '设备管理' }
      },
      {
        path: 'materials',
        name: 'Materials',
        component: () => import('../views/equipment/MaterialList.vue'),
        meta: { title: '材料管理' }
      },
      {
        path: 'equipment-borrowings',
        name: 'EquipmentBorrowings',
        component: () => import('../views/equipment/BorrowingList.vue'),
        meta: { title: '设备借用管理' }
      },
      {
        path: 'material-usages',
        name: 'MaterialUsages',
        component: () => import('../views/equipment/UsageList.vue'),
        meta: { title: '材料使用记录' }
      },
      // 实验过程管理路由
      {
        path: 'experiment-plans',
        name: 'ExperimentPlans',
        component: () => import('../views/experiment-process/plan/PlanList.vue'),
        meta: { title: '实验计划管理' }
      },
      {
        path: 'experiment-records',
        name: 'ExperimentRecords',
        component: () => import('../views/experiment-process/record/RecordList.vue'),
        meta: { title: '实验记录管理' }
      },
      {
        path: 'experiment-review',
        name: 'ExperimentReview',
        component: () => import('../views/experiment-process/review/ReviewList.vue'),
        meta: { title: '实验记录审核' }
      },
      {
        path: 'experiment-calendar',
        name: 'ExperimentCalendar',
        component: () => import('../views/experiment-process/calendar/ExperimentCalendar.vue'),
        meta: { title: '实验日历' }
      },
      {
        path: 'experiment-monitor',
        name: 'ExperimentMonitor',
        component: () => import('../views/experiment-process/monitor/Dashboard.vue'),
        meta: { title: '实验执行监控' }
      },
      {
        path: 'system-test',
        name: 'SystemTest',
        component: () => import('../views/test/SystemTest.vue'),
        meta: { title: '系统功能测试' }
      },
      {
        path: 'menu-test',
        name: 'MenuTest',
        component: () => import('../views/test/MenuTest.vue'),
        meta: { title: '菜单功能测试' }
      },
      {
        path: 'icon-test',
        name: 'IconTest',
        component: () => import('../views/test/IconTest.vue'),
        meta: { title: '图标测试' }
      },
      {
        path: 'role-test',
        name: 'RoleTest',
        component: () => import('../views/test/RoleTest.vue'),
        meta: { title: '角色权限测试' }
      },
      {
        path: 'calendar-test',
        name: 'CalendarTest',
        component: () => import('../views/test/CalendarTest.vue'),
        meta: { title: '日历功能测试' }
      },
      {
        path: 'monitor-test',
        name: 'MonitorTest',
        component: () => import('../views/test/MonitorTest.vue'),
        meta: { title: '监控功能测试' }
      },
      {
        path: 'import-test',
        name: 'ImportTest',
        component: () => import('../views/test/ImportTest.vue'),
        meta: { title: '导入测试' }
      },
      {
        path: 'experiment-calendar',
        name: 'ExperimentCalendar',
        component: () => import('../views/experiment-process/calendar/ExperimentCalendar.vue'),
        meta: { title: '实验日历' }
      },
      {
        path: 'calendar-test',
        name: 'CalendarTest',
        component: () => import('../views/experiment-process/calendar/CalendarTest.vue'),
        meta: { title: '日历功能测试' }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// 路由守卫
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router 