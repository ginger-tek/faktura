import { api, toDate } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div>
    <h2>Users</h2>
    <div class="flex spread bottom-spacing" style="gap:.5rem">
      <input type="search" v-model="filter" placeholder="Filter users...">
      <button class="nowrap" @click="fetchUsers" :aria-busy="fetching" :disabled="fetching"><i v-show="!fetching" class="bi bi-arrow-clockwise"></i> <span>Refresh</span></button>
      <button class="nowrap" @click="newUserModalRef.open()"><i class="bi bi-file-earmark-plus"></i> <span>New User</span></button>
    </div>
    <auto-table :data="users" :columns="userColumns" :bordered="true" :filter="filter" class="nowrap">
      <template #display_name="{ id, display_name }"><router-link :to="'/users/'+id">{{ display_name }}</router-link></template>
      <template #role_name="{ role_id, role_name }"><router-link :to="'/roles/'+role_id">{{ role_name }}</router-link></template>
      <template #created_at="{ created_at }">{{ toDate(created_at) }}</template>
      <template #updated_at="{ updated_at }">{{ toDate(updated_at) }}</template>
      <template #empty-data>No users</template>
      <template #empty-filter="value">No users match the filter "{{ value }}"</template>
    </auto-table>
    <modal ref="newUserModalRef" hide-close>
      <template #title>New User</template>
      <form @submit.prevent="createUser">
        <label>Display Name
          <input type="text" v-model="newUser.display_name" required>
        </label>
        <label>Username
          <input type="text" v-model="newUser.username" required>
        </label>
        <label>Password
          <input type="password" v-model="newUser.password" required>
        </label>
        <div class="flex stretch">
          <button type="button" class="secondary" @click="newUserModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
          <button type="submit" :aria-busy="submitting" :disabled="submitting"><i class="bi bi-check-circle"></i> Submit</button>
        </div>
      </form>
    </modal>
  </div>`,
  setup() {
    const filter = Vue.ref('')
    const users = Vue.ref([])
    const fetching = Vue.ref(false)
    const submitting = Vue.ref(false)
    const newUserModalRef = Vue.ref(null)
    const newUser = Vue.ref({ display_name: '', username: '', password: '' })
    const userColumns = [
      { key: 'display_name', label: 'Display Name' },
      { key: 'username', label: 'Username' },
      { key: 'role_name', label: 'Role' },
      { key: 'created_at', label: 'Created At' },
      { key: 'updated_at', label: 'Updated At' }
    ]
    const router = VueRouter.useRouter()

    const fetchUsers = async () => {
      try {
        const res = await api('users')
        users.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get users: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    const createUser = async () => {
      try {
        clearToasts()
        submitting.value = true
        const res = await api('users', 'POST', newUser.value)
        newUserModalRef.value.close()
        appendToast('User created successfully', { variant: 'success' })
        router.push(`/users/${res.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to create user: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    Vue.onBeforeMount(fetchUsers)

    return {
      users, userColumns, filter, newUserModalRef, newUser, submitting, fetching,
      fetchUsers, createUser, toDate
    }
  }
}