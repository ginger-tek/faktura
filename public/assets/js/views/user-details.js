import state from '../state.js'
import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div :aria-busy="fetching"></div>
  <form v-if="user" @submit.prevent="updateUser">
    <div class="flex stack spread bottom-spacing">
      <div>
        <div class="bottom-spacing-sm secondary" role="link" @click="$router.back()"><i class="bi bi-arrow-left"></i> Back</div>
        <h4 class="bottom-clear">{{ user?.display_name || 'Unnamed User' }}</h4>
      </div>
      <div class="flex" style="gap:.5rem">
        <button type="submit" class="nowrap" :aria-busy="updating" :disabled="updating"><i v-if="!updating" class="bi bi-floppy"></i> Save</button>
        <button type="button" @click="deleteModalRef.open()" class="danger nowrap"><i class="bi bi-trash"></i> Delete</button>
      </div>
    </div>
    <div class="grid">
      <label>Username
        <input type="text" v-model="user.username" required>
      </label>
      <label>Display Name
        <input type="text" v-model="user.display_name" required>
      </label>
      <label>Role
        <select v-model="user.role_id" required>
          <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.role_name }}</option>
        </select>
      </label>
    </div>
    <label>
      <input type="checkbox" v-model="user.active" :true-value="1" :false-value="0">
      Active
    </label>
    <div class="bottom-spacing">
      <created-updated :obj="user"></created-updated>
      <small>ID: {{ user?.id }}</small>
    </div>
  </form>
  <modal ref="deleteModalRef">
    <template #title>Confirm Delete</template>
    <p>Are you sure you want to delete this user? This action cannot be undone.</p>
    <div class="flex stretch">
      <button type="button" class="secondary" @click="deleteModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
      <button type="button" class="danger" @click="deleteUser" :aria-busy="deleting" :disabled="deleting"><i class="bi bi-trash"></i> Delete User</button>
    </div>
  </modal>`,
  setup() {
    const route = VueRouter.useRoute()
    const fetching = Vue.ref(false)
    const updating = Vue.ref(false)
    const deleting = Vue.ref(false)
    const user = Vue.ref(null)
    const roles = Vue.ref([])
    const router = VueRouter.useRouter()
    const deleteModalRef = Vue.ref(null)

    const fetchUser = async () => {
      try {
        fetching.value = true
        clearToasts()
        user.value = await api(`users/${route.params.id}`)
        roles.value = await api('roles')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get user: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const updateUser = async () => {
      try {
        updating.value = true
        clearToasts()
        user.value = await api(`users/${user.value.id}`, 'PUT', user.value)
        appendToast('User updated successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update user: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updating.value = false
      }
    }

    const deleteUser = async () => {
      try {
        deleting.value = true
        clearToasts()
        await api(`users/${user.value.id}`, 'DELETE')
        appendToast('User deleted successfully', { variant: 'success' })
        router.push('/users')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to delete user: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        deleting.value = false
      }
    }

    Vue.onBeforeMount(fetchUser)

    return {
      state, fetching, updating, deleting, user, deleteModalRef, roles,
      fetchUser, updateUser, deleteUser
    }
  }
}