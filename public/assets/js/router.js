import state from './state.js'

const router = VueRouter.createRouter({
  history: VueRouter.createWebHistory(),
  routes: [
    { path: '/', redirect: '/invoices' },
    { path: '/login', component: () => import('./views/login.js'), name: 'Login' },
    { path: '/invoices', component: () => import('./views/invoices.js'), name: 'Invoices', meta: { auth: 1, roles: ['admin','invoice_manager'] } },
    { path: '/invoices/:id', component: () => import('./views/invoice-details.js'), name: 'Invoice Details', meta: { auth: 1, roles: ['admin','invoice_manager'] } },
    { path: '/expenses', component: () => import('./views/expenses.js'), name: 'Expenses', meta: { auth: 1, roles: ['admin','expense_manager'] } },
    { path: '/expenses/:id', component: () => import('./views/expense-details.js'), name: 'Expense Details', meta: { auth: 1, roles: ['admin','expense_manager'] } },
    { path: '/clients', component: () => import('./views/clients.js'), name: 'Clients', meta: { auth: 1, roles: ['admin','client_manager'] } },
    { path: '/clients/:id', component: () => import('./views/client-details.js'), name: 'Client Details', meta: { auth: 1, roles: ['admin','client_manager'] } },
    { path: '/users', component: () => import('./views/users.js'), name: 'Users', meta: { auth: 1, roles: ['admin','user_manager'] } },
    { path: '/users/:id', component: () => import('./views/user-details.js'), name: 'User Details', meta: { auth: 1, roles: ['admin','user_manager'] } },
    { path: '/roles', component: () => import('./views/roles.js'), name: 'Roles', meta: { auth: 1, roles: ['admin','role_manager'] } },
    { path: '/roles/:id', component: () => import('./views/role-details.js'), name: 'Role Details', meta: { auth: 1, roles: ['admin','role_manager'] } },
    { path: '/settings', component: () => import('./views/settings.js'), name: 'Settings', meta: { auth: 1, roles: ['admin'] } },
    { path: '/settings/:key', component: () => import('./views/setting-details.js'), name: 'Setting Details', meta: { auth: 1, roles: ['admin'] } },
    { path: '/unauthorized', component: () => import('./views/unauthorized.js'), name: 'Unauthorized' },
    { path: '/:pathMatch(.*)*', component: () => import('./views/not-found.js'), name: 'Page Not Found' }
  ]
})

router.beforeEach((to, from) => {
  document.title = to.name ? `${to.name} | Faktura` : 'Faktura'
  document.querySelector('nav details')?.removeAttribute('open')
  if (to.meta.auth && !state.user?.id)
    return '/login' + (!to.path.match(/^(\/login|\/invoices|\/)$/) ? '?redirect=' + to.path : '')
  else if (to.meta.roles && !state.hasRoles(to.meta.roles))
    return '/unauthorized'
  else if (to.path === '/login' && state.user?.id)
    return '/invoices'
  return
})

export default router