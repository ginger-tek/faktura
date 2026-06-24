import state from './state.js'

const router = VueRouter.createRouter({
  history: VueRouter.createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', component: () => import('./views/login.js'), name: 'Login' },
    { path: '/dashboard', component: () => import('./views/dashboard.js'), name: 'Dashboard', meta: { auth: 1 } },
    { path: '/invoices', component: () => import('./views/invoices.js'), name: 'Invoices', meta: { auth: 1, perms: ['invoice_read_all'] } },
    { path: '/invoices/:id', component: () => import('./views/invoice-details.js'), name: 'Invoice Details', meta: { auth: 1, perms: ['invoice_read'] } },
    { path: '/expenses', component: () => import('./views/expenses.js'), name: 'Expenses', meta: { auth: 1, perms: ['expense_read_all'] } },
    { path: '/expenses/:id', component: () => import('./views/expense-details.js'), name: 'Expense Details', meta: { auth: 1, perms: ['expense_read'] } },
    { path: '/clients', component: () => import('./views/clients.js'), name: 'Clients', meta: { auth: 1, perms: ['client_read_all'] } },
    { path: '/clients/:id', component: () => import('./views/client-details.js'), name: 'Client Details', meta: { auth: 1, perms: ['client_read'] } },
    { path: '/users', component: () => import('./views/users.js'), name: 'Users', meta: { auth: 1, perms: ['user_read_all'] } },
    { path: '/users/:id', component: () => import('./views/user-details.js'), name: 'User Details', meta: { auth: 1, perms: ['user_read'] } },
    { path: '/roles', component: () => import('./views/roles.js'), name: 'Roles', meta: { auth: 1, perms: ['role_read_all'] } },
    { path: '/roles/:id', component: () => import('./views/role-details.js'), name: 'Role Details', meta: { auth: 1, perms: ['role_read'] } },
    { path: '/org-settings', component: () => import('./views/org-settings.js'), name: 'Settings', meta: { auth: 1, perms: ['org_settings_read'] } },
    { path: '/org-settings/:key', component: () => import('./views/org-setting-details.js'), name: 'Setting Details', meta: { auth: 1, perms: ['org_settings_read'] } },
    { path: '/settings', component: () => import('./views/user-settings.js'), name: 'User Settings', meta: { auth: 1 } },
    { path: '/unauthorized', component: () => import('./views/unauthorized.js'), name: 'Unauthorized' },
    { path: '/:pathMatch(.*)*', component: () => import('./views/not-found.js'), name: 'Page Not Found' }
  ]
})

router.beforeEach((to, from) => {
  document.title = to.name ? `${to.name} | Faktura` : 'Faktura'
  document.querySelector('nav details')?.removeAttribute('open')
  if (to.meta.auth && !state.user?.id)
    return '/login' + (!to.path.match(/^(\/login|\/dashboard|\/)$/) ? '?redirect=' + to.path : '')
  else if ((to.meta?.perms && !state.hasOne(to.meta.perms)))
    return '/unauthorized'
  else if (to.path === '/login' && state.user?.id)
    return '/invoices'
  return
})

export default router