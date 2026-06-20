import state from '../state.js'
import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<article style="max-inline-size:400px;margin-inline:auto">
    <h2>Login</h2>
    <form v-if="!org" @submit.prevent="submitFindOrg">
      <label>Organization
        <input type="text" v-model="orgCode" @input="orgCode = orgCode.replaceAll(' ', '')" autofocus required>
      </label>
      <button type="submit" class="button is-primary" :aria-busy="submitting" :disabled="submitting">Next <i class="bi bi-arrow-right"></i></button>
    </form>
    <form v-else-if="org?.id" @submit.prevent="submitLogin">
      <span role="link" @click="org = null" style="display:inline-block" class="bottom-spacing"><i class="bi bi-arrow-left"></i> Back</span>
      <h3>{{ org.display_name }}</h3>
      <p>Please enter your credentials to login</p>
      <label>Username
        <input type="text" v-model="username" autofocus required>
      </label>
      <label>Password
        <input type="password" v-model="password" required>
      </label>
      <label class="bottom-spacing">
        <input type="checkbox" v-model="remember"> Remember me for 30 days
      </label>
      <div class="flex stretch">
        <button type="submit" :aria-busy="submitting" :disabled="submitting"><i class="bi bi-box-arrow-in-right"></i> Login</button>
      </div>
    </form>
  </article>`,
  setup() {
    const username = Vue.ref('')
    const password = Vue.ref('')
    const remember = Vue.ref(false)
    const orgCode = Vue.ref('')
    const org = Vue.ref(null)
    const submitting = Vue.ref(false)
    const route = VueRouter.useRoute()
    const router = VueRouter.useRouter()

    const submitFindOrg = async () => {
      try {
        clearToasts()
        submitting.value = true
        const body = { org_code: orgCode.value?.trim() }
        if (localStorage.hasOwnProperty('org_id')) {
          delete body.org_code
          body.org_id = localStorage.getItem('org_id')?.trim()
        }
        const res = await api('auth/find-org', 'POST', body)
        org.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to find organization: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    const submitLogin = async () => {
      try {
        clearToasts()
        submitting.value = true
        const res = await api('auth/login', 'POST', {
          org_id: org.value.id.trim(),
          username: username.value.trim(),
          password: password.value.trim(),
          remember: remember.value
        })
        state.user = await api('auth/me')
        router.push(route.query.redirect || '/')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to login: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    Vue.watch(() => org.value?.id, (n) => {
      if (!n)
        return localStorage.removeItem('org_id')
      localStorage.setItem('org_id', n.trim())
      Vue.nextTick(() => document.querySelector('input[autofocus]')?.focus())
    })

    Vue.onMounted(() => {
      if (localStorage.hasOwnProperty('org_id'))
        submitFindOrg()
    })

    return {
      username, password, remember, orgCode, org, submitting,
      submitFindOrg, submitLogin
    }
  }
}