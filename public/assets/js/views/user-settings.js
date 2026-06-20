import state from '../state.js'
import { api } from '../utils.js'
import { clearToasts, appendToast } from '../comps/toasts.js'

export default {
  template: `<article>
    <header><b><i class="bi bi-person-fill"></i> Profile</b></header>
    <article class="alert info">
      <i class="bi bi-info-circle"></i> To update your username, please contact your organization administrator
    </article>
    <form @submit.prevent="updateUser">
      <label>Display Name
        <input type="text" v-model="user.display_name" required>
      </label>
      <button type="submit" class="bottom-clear" :aria-busy="updating" :disabled="updating"><i v-if="!updating" class="bi bi-check-circle"></i> Save</button>
    </form>
  </article>
  <article>
    <header><b><i class="bi bi-shield-lock-fill"></i> Security</b></header>
    <article class="alert warning">
      <i class="bi bi-exclamation-circle"></i> Changing your password will require you to log in again with the new password after saving changes
    </article>
    <article class="alert info">
      <i class="bi bi-info-circle"></i> Password must be at least 8 characters and contain at least one lowercase letter, one uppercase letter, one number, and one special character, i.e. <code>!@#$%^&*()+=_-</code>
    </article>
    <form @submit.prevent="updateUserPassword">
      <label>Current Password
        <peak-password></peak-password>
        <input type="password" v-model="passwords.current" required>
      </label>
      <label>New Password
        <peak-password></peak-password>
        <input type="password" v-model="passwords.new" :aria-invalid="passwords.new ? !isNewValid : undefined" required>
        <small v-if="passwords.new && !isNewValid">Password does not meet requirements</small>
      </label>
      <label>Confirm New Password
        <peak-password></peak-password>
        <input type="password" v-model="passwords.confirm" :aria-invalid="passwords.new && passwords.confirm ? !isMatching : undefined" required>
        <small v-if="passwords.new && passwords.confirm && !isMatching">Passwords do not match</small>
      </label>
      <button type="submit" class="bottom-clear" :aria-busy="updatingPassword" :disabled="updatingPassword"><i v-if="!updatingPassword" class="bi bi-check-circle"></i> Update Password</button>
    </form>
  </article>`,
  setup() {
    const user = Vue.ref({ display_name: state.user.display_name })
    const passwords = Vue.ref({ current: '', new: '', confirm: '' })
    const updating = Vue.ref(false)
    const updatingPassword = Vue.ref(false)

    const updateUser = async () => {
      try {
        clearToasts()
        updating.value = true
        await api('auth/me', 'PUT', user.value)
        appendToast('User updated successfully', { variant: 'success' })
        state.user = await api('auth/me')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update user: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updating.value = false
      }
    }

    const updateUserPassword = async () => {
      try {
        clearToasts()
        updatingPassword.value = true
        if (!isMatching.value || !isNewValid.value) return
        await api('auth/me/password', 'POST', passwords.value)
        appendToast('Password updated successfully, please log in again with your new password', { variant: 'success' })
        await api('auth/logout', 'POST')
        state.user = null
        VueRouter.useRouter().replace('/login')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update password: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updatingPassword.value = false
      }
    }

    Vue.watch(() => passwords.value.current, () => {
      if (!passwords.value.current) passwords.value.new = ''
    })

    const isNewValid = Vue.computed(() => /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()+=_-]).{8,24}$/.test(passwords.value.new))
    const isMatching = Vue.computed(() => passwords.value.new === passwords.value.confirm)

    return {
      user, passwords, updating, updatingPassword, isNewValid, isMatching,
      updateUser, updateUserPassword
    }
  }
}