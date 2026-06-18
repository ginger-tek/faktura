import state from './state.js'

export default {
  template: `<header class="container">
    <nav>
      <ul>
        <li><router-link to="/">
          <div class="logo">
            <i class="bi bi-currency-dollar"></i>
            <i class="bi bi-journal"></i>
          </div>
          <b>Faktura</b>
        </router-link></li>
      </ul>
      <template v-if="state.user?.id">
        <ul>
          <li>
            <details class="dropdown">
              <summary><i class="bi bi-person-circle"></i> {{ state.user.display_name }}</summary>
              <ul dir="rtl">
                <li dir="ltr" v-if="state.hasRoles(['admin','invoice_manager'])"><router-link to="/invoices"><i class="bi bi-file-earmark-text-fill"></i> Invoices</router-link></li>
                <li dir="ltr" v-if="state.hasRoles(['admin','expense_manager'])"><router-link to="/expenses"><i class="bi bi-receipt"></i> Expenses</router-link></li>
                <li dir="ltr" v-if="state.hasRoles(['admin','client_manager'])"><router-link to="/clients"><i class="bi bi-person-lines-fill"></i> Clients</router-link></li>
                <template v-if="state.hasRoles(['admin','user_manager','role_manager','org_settings_manager'])">
                  <li><hr></li>
                  <li dir="ltr" v-if="state.hasRoles(['admin','user_manager'])"><router-link to="/users"><i class="bi bi-people-fill"></i> Users</router-link></li>
                  <li dir="ltr" v-if="state.hasRoles(['admin','role_manager'])"><router-link to="/roles"><i class="bi bi-shield-lock-fill"></i> Roles</router-link></li>
                  <li dir="ltr" v-if="state.hasRoles(['admin','org_settings_manager'])"><router-link to="/settings"><i class="bi bi-gear-fill"></i> Settings</router-link></li>
                </template>
                <li><hr></li>
                <li dir="ltr"><a role="link" @click="submitLogout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
              </ul>
            </details>
          </li>
        </ul>
      </template>
      <template v-else>
        <ul>
          <li><router-link to="/login">Login</router-link></li>
          <li><router-link to="/signup">Sign Up</router-link></li>
        </ul>
      </template>
    </nav>
  </header>
  <main class="container" style="flex:1">
    <router-view></router-view>
    <toaster></toaster>
  </main>
  <footer class="container bottom-spacing">
    <small>&copy; GingerTek Solutions</small>
  </footer>`,
  setup() {
    const router = VueRouter.useRouter()

    const submitLogout = async () => {
      await fetch('/api/logout', { method: 'POST' })
      state.user = null
      router.replace('/login')
    }

    return {
      state,
      submitLogout
    }
  }
}