import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

const routes = [
  {
    path: '/login',
    component: () => import('@/views/auth/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '',              redirect: '/dashboard' },
      { path: 'dashboard',    component: () => import('@/views/Dashboard.vue') },
      { path: 'medicaments',  component: () => import('@/views/medicaments/Index.vue') },
      { path: 'ventes',       component: () => import('@/views/ventes/Index.vue') },
      { path: 'stock',        component: () => import('@/views/stock/Index.vue') },
      { path: 'achats',       component: () => import('@/views/achats/Index.vue') },
      { path: 'fournisseurs', component: () => import('@/views/fournisseurs/Index.vue') },
      { path: 'rapports',     component: () => import('@/views/rapports/Index.vue') },
      {
        path: 'utilisateurs',
        component: () => import('@/views/utilisateurs/Index.vue'),
        meta: { role: 'admin' }
      },
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    component: () => import('@/views/NotFound.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  // ✅ Import statique en haut du fichier — pas de require()
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next('/dashboard')
  }

  if (to.meta.role && !auth.hasRole(to.meta.role)) {
    return next('/dashboard')
  }

  next()
})

export default router