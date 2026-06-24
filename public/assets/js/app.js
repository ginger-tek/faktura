import state from './state.js'
import { api } from './utils.js'

export default {
  template: `<header class="container">
    <nav>
      <template v-if="state.user?.id">
        <ul>
          <li><router-link to="/">
            <div class="logo">
              <i class="bi bi-currency-dollar"></i>
              <i class="bi bi-journal"></i>
            </div>
            <b>Faktura</b>
          </router-link></li>
        </ul>
        <ul>
          <li><img v-if="state.user.org_logo" :src="state.user.org_logo" alt="Org Logo" class="org-logo"> <span>{{ state.user.org_display_name }}</span></li>
          <li>
            <details class="dropdown">
              <summary><i class="bi bi-person-circle"></i> {{ state.user.display_name }}</summary>
              <ul dir="rtl">
                <li dir="ltr"><router-link to="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</router-link></li>
                <li dir="ltr" v-if="state.hasOne(['invoice_read'])"><router-link to="/invoices"><i class="bi bi-file-earmark-text-fill"></i> Invoices</router-link></li>
                <li dir="ltr" v-if="state.hasOne(['expense_read'])"><router-link to="/expenses"><i class="bi bi-receipt"></i> Expenses</router-link></li>
                <li dir="ltr" v-if="state.hasOne(['client_read'])"><router-link to="/clients"><i class="bi bi-person-lines-fill"></i> Clients</router-link></li>
                <template v-if="state.hasOne(['user_read','role_read','org_settings_read'])">
                  <li><hr></li>
                  <li dir="ltr" v-if="state.hasOne(['user_read'])"><router-link to="/users"><i class="bi bi-people-fill"></i> Users</router-link></li>
                  <li dir="ltr" v-if="state.hasOne(['role_read'])"><router-link to="/roles"><i class="bi bi-shield-lock-fill"></i> Roles</router-link></li>
                  <li dir="ltr" v-if="state.hasOne(['org_settings_read'])"><router-link to="/org-settings"><i class="bi bi-gear-fill"></i> Org Settings</router-link></li>
                </template>
                <li><hr></li>
                <li dir="ltr"><router-link to="/settings"><i class="bi bi-person-gear"></i> Settings</router-link></li>
                <li dir="ltr"><a role="link" @click="submitLogout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
              </ul>
            </details>
          </li>
        </ul>
      </template>
      <template v-else>
        <ul style="flex:1;justify-content:center">
          <li>
            <h1 class="text-center">
              <div class="logo">
                <i class="bi bi-currency-dollar"></i>
                <i class="bi bi-journal"></i>
              </div>
              <b>Faktura</b>
            </h1>
          </li>
        </ul>
      </template>
    </nav>
  </header>
  <main class="container" style="flex:1">
    <router-view :key="$route.fullPath"></router-view>
    <toaster></toaster>
  </main>
  <footer class="container bottom-spacing">
    <small>&copy; <a href="https://gingerteksolutions.com" target="_blank">GingerTek Solutions</a></small>
  </footer>`,
  setup() {
    const router = VueRouter.useRouter()

    const submitLogout = async () => {
      await api('auth/logout', 'POST')
      state.user = null
      router.replace('/login')
    }

    return {
      state,
      submitLogout
    }
  }
}