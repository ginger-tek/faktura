import state from '../state.js'
import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div :aria-busy="fetching"></div>
  <form v-if="role" @submit.prevent="updateRole">
    <div class="flex stack spread bottom-spacing">
      <div>
        <div class="bottom-spacing-sm secondary" role="link" @click="$router.back()"><i class="bi bi-arrow-left"></i> Back</div>
        <h4 class="bottom-clear">{{ role?.role_name }}</h4>
        <created-updated :obj="role"></created-updated>
      </div>
      <div class="flex" style="gap:.5rem">
        <button type="button" @click="updateRole" class="nowrap" :aria-busy="updating" :disabled="updating"><i v-if="!updating" class="bi bi-floppy"></i> Save</button>
        <button type="button" @click="deleteModalRef.open()" class="danger nowrap"><i class="bi bi-trash"></i> Delete</button>
      </div>
    </div>
    <label>Label
      <input type="text" v-model="role.role_name" @input="role.role_name = role.role_name.replace(/\s/g, '-')"  required>
    </label>
    <label>Permissions <span :data-tooltip="'Bit: ' + role.bit_value"><i class="bi bi-info-circle"></i></span></label>
    <ul class="flex-list">
      <li v-for="key of Object.keys(permissions)" :key="key">
        <label><input type="checkbox" :value="permissions[key]" ref="permissionRefs" :checked="(role.bit_value & permissions[key]) !== 0" @change="setBitValue"> {{ key }}</label>
      </li>
    </ul>
  </form>
  <modal ref="deleteModalRef">
    <template #title>Confirm Delete</template>
    <p>Are you sure you want to delete this role? This action cannot be undone.</p>
    <div class="flex stretch">
      <button type="button" class="secondary" @click="deleteModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
      <button type="button" class="danger" @click="deleteRole" :aria-busy="deleting" :disabled="deleting"><i class="bi bi-trash"></i> Delete Role</button>
    </div>
  </modal>`,
  setup() {
    const route = VueRouter.useRoute()
    const fetching = Vue.ref(false)
    const updating = Vue.ref(false)
    const deleting = Vue.ref(false)
    const role = Vue.ref(null)
    const permissions = Vue.ref([])
    const permissionRefs = Vue.ref([])
    const router = VueRouter.useRouter()
    const deleteModalRef = Vue.ref(null)

    const fetchRole = async () => {
      try {
        fetching.value = true
        clearToasts()
        role.value = await api(`roles/${route.params.id}`)
        permissions.value = await api('roles/permissions')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get role: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const updateRole = async () => {
      try {
        updating.value = true
        clearToasts()
        role.value = await api(`roles/${role.value.id}`, 'PUT', role.value)
        appendToast('Role updated successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update role: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updating.value = false
      }
    }

    const setBitValue = (permission) => {
      role.value.bit_value = permissionRefs.value.reduce((acc, ref) => acc | (ref.checked ? parseInt(ref.value) : 0), 0)
    }

    const deleteRole = async () => {
      try {
        deleting.value = true
        clearToasts()
        await api(`roles/${role.value.id}`, 'DELETE')
        appendToast('Role deleted successfully', { variant: 'success' })
        router.push('/roles')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to delete role: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        deleting.value = false
      }
    }

    Vue.onBeforeMount(fetchRole)

    return {
      state, fetching, updating, deleting, role, deleteModalRef, permissions, permissionRefs,
      fetchRole, setBitValue, updateRole, deleteRole
    }
  }
}