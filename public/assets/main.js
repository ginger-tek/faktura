import state from './js/state.js'
import { api } from './js/utils.js'
import router from './js/router.js'
import App from './js/app.js'
import Modal from './js/comps/modal.js'
import { Toaster } from './js/comps/toasts.js'
import AutoTable from './js/comps/auto-table.js'
import CreatedUpdated from './js/comps/created-updated.js'
import PeakPassword from './js/comps/peak-password.js'

const hasCookie = await cookieStore.get('token_exp')
if (hasCookie?.name === 'token_exp') {
  const res = await utils.api('me')
  state.user = res
  router.push('/invoices')
} else {
  cookieStore.delete('token_exp')
}

const app = Vue.createApp(App)
  .component('toaster', Toaster)
  .component('auto-table', AutoTable)
  .component('modal', Modal)
  .component('created-updated', CreatedUpdated)
  .component('peak-password', PeakPassword)
  .use(router)

app.mount('#app')