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
        path: 'organizations/import',
        name: 'OrganizationImport',
        component: () => import('../views/organization/OrganizationImport.vue'),
        meta: { title: '批量导入组织' }
      },
      {
        path: 'organizations/districts',
        name: 'DistrictManagement',
        component: () => import('../views/district/DistrictManagement.vue'),
        meta: { title: '学区划分管理' }
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
        path: 'organizations/permissions',
        name: 'PermissionSettings',
        component: () => import('../views/permission/PermissionSettings.vue'),
        meta: { title: '权限设置' }
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