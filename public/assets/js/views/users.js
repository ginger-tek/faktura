import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div>
    <h2>Users</h2>
    <input type="search" v-model="filter" placeholder="Filter users...">
    <auto-table :data="users" :columns="userColumns" :bordered="true" :filter="filter">
      <template #display_name="{ id, display_name }"><router-link :to="'/users/'+id">{{ display_name }}</router-link></template>
      <template #role_name="{ role_id, role_name }"><router-link :to="'/roles/'+role_id">{{ role_name }}</router-link></template>
      <template #created_at="{ created_at }">{{ new Date(created_at * 1000).toLocaleString() }}</template>
      <template #updated_at="{ updated_at }">{{ new Date(updated_at * 1000).toLocaleString() }}</template>
      <template #empty-data>No users</template>
      <template #empty-filter="value">No users match the filter "{{ value }}"</template>
    </auto-table>
  </div>`,
  setup() {
    const filter = Vue.ref('')
    const users = Vue.ref([])
    const userColumns = [
      { key: 'display_name', label: 'Display Name' },
      { key: 'username', label: 'Username' },
      { key: 'role_name', label: 'Role' },
      { key: 'created_at', label: 'Created At' },
      { key: 'updated_at', label: 'Updated At' }
    ]

    const fetchUsers = async () => {
      try {
        const res = await api('users')
        users.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get users: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    Vue.onBeforeMount(fetchUsers)

    return {
      users, userColumns, filter,
      fetchUsers
    }
  }
}