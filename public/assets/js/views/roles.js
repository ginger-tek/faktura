import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div>
    <h2>Roles</h2>
    <div class="flex spread bottom-spacing" style="gap:.5rem">
      <input v-model="filter" type="search" placeholder="Filter roles...">
      <button class="nowrap" @click="fetchRoles" :aria-busy="fetching" :disabled="fetching"><i v-show="!fetching" class="bi bi-arrow-clockwise"></i> <span>Refresh</span></button>
      <button class="nowrap" @click="newRoleModalRef.open()"><i class="bi bi-file-earmark-plus"></i> <span>New Role</span></button>
    </div>
    <auto-table :data="roles" :columns="roleColumns" :bordered="true" :filter="filter">
      <template #role_name="{ id, role_name }"><router-link :to="'/roles/'+id">{{ role_name }}</router-link></template>
      <template #created_at="{ created_at }">{{ new Date(created_at * 1000).toLocaleString() }}</template>
      <template #updated_at="{ updated_at }">{{ new Date(updated_at * 1000).toLocaleString() }}</template>
      <template #empty-data>No roles</template>
      <template #empty-filter="value">No roles match the filter "{{ value }}"</template>
    </auto-table>
    <modal ref="newRoleModalRef" hide-close>
      <template #title>New Role</template>
      <form @submit.prevent="createRole">
        <label>Role Name
          <input type="text" v-model="newRole.role_name" @input="newRole.role_name = newRole.role_name.replaceAll(/\\s+/g, '_').replaceAll(/[^a-zA-Z_]/g, '').trim()" required>
        </label>
        <div class="flex stretch">
          <button type="button" class="secondary" @click="newRoleModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
          <button type="submit" :aria-busy="submitting" :disabled="submitting"><i class="bi bi-check-circle"></i> Submit</button>
        </div>
      </form>
    </modal>
  </div>`,
  setup() {
    const filter = Vue.ref('')
    const roles = Vue.ref([])
    const fetching = Vue.ref(false)
    const submitting = Vue.ref(false)
    const roleColumns = [
      { key: 'role_name', label: 'Name' },
      { key: 'created_at', label: 'Created At' },
      { key: 'updated_at', label: 'Updated At' }
    ]
    const newRoleModalRef = Vue.ref(null)
    const newRole = Vue.ref({ role_name: '' })
    const router = VueRouter.useRouter()

    const fetchRoles = async () => {
      try {
        const res = await api('roles')
        roles.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get roles: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    const createRole = async () => {
      try {
        clearToasts()
        submitting.value = true
        const res = await api('roles', 'POST', newRole.value)
        newRoleModalRef.value.close()
        appendToast('Role created successfully', { variant: 'success' })
        router.push(`/roles/${res.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to create role: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    Vue.onBeforeMount(fetchRoles)

    return {
      roles, roleColumns, filter, newRoleModalRef, newRole, fetching, submitting,
      fetchRoles, createRole
    }
  }
}