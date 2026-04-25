import { createRouter, createWebHistory } from 'vue-router'
import { getToken } from '@/utils/storage'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue')
  },
  {
    path: '/',
    component: () => import('@/layout/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'Dashboard', component: () => import('@/views/Dashboard.vue'), meta: { title: '控制台' } },
      { path: 'user', name: 'User', component: () => import('@/views/User.vue'), meta: { title: '会员管理' } },
      { path: 'product', name: 'Product', component: () => import('@/views/Product.vue'), meta: { title: '商品管理' } },
      { path: 'category', name: 'Category', component: () => import('@/views/Category.vue'), meta: { title: '分类管理' } },
      { path: 'order', name: 'Order', component: () => import('@/views/Order.vue'), meta: { title: '订单管理' } },
      { path: 'statistics', name: 'Statistics', component: () => import('@/views/Statistics.vue'), meta: { title: '数据统计' } },
      { path: 'config', name: 'Config', component: () => import('@/views/Config.vue'), meta: { title: '系统配置' } }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const hasToken = !!getToken()
  
  if (to.meta.requiresAuth && !hasToken) {
    next('/login')
  } else if (to.path === '/login' && hasToken) {
    next('/')
  } else {
    next()
  }
})

export default router