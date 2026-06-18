import state from '../state.js'
import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div :aria-busy="fetching"></div>
  <div class="flex stack spread bottom-spacing">
    <div>
      <div class="bottom-spacing-sm secondary" role="link" @click="$router.back()"><i class="bi bi-arrow-left"></i> Back</div>
      <h4>{{ client?.full_name }}</h4>
    </div>
    <div class="flex" style="gap:.5rem" v-if="state.hasRoles(['admin','client_manager'])">
      <button type="button" @click="updateClient" class="nowrap" :aria-busy="updating" :disabled="updating"><i v-if="!updating" class="bi bi-floppy"></i> Save</button>
      <button type="button" @click="deleteModalRef.open()" class="danger nowrap"><i class="bi bi-trash"></i> Delete</button>
    </div>
  </div>
  <form v-if="client" @submit.prevent="updateClient" :readonly="!state.hasRoles(['admin','client_manager']) || undefined">
    <div class="grid">
      <label>Full Name
        <input type="text" v-model="client.full_name" required>
      </label>
      <label>Contact Email
        <input type="email" v-model="client.contact_email" required>
      </label>
      <label>Contact Phone
        <input type="tel" v-model="client.contact_phone">
      </label>
    </div>
    <label>Contact Address
      <textarea v-model="client.contact_address" :rows="client.contact_address.split('\\n').length || 1"></textarea>
    </label>
  </form>
  <modal ref="deleteModalRef">
    <template #title>Confirm Delete</template>
    <p>Are you sure you want to delete this client? This action cannot be undone.</p>
    <div class="flex stretch">
      <button type="button" class="secondary" @click="deleteModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
      <button type="button" class="danger" @click="deleteClient" :aria-busy="deleting" :disabled="deleting"><i class="bi bi-trash"></i> Delete Client</button>
    </div>
  </modal>`,
  setup() {
    const route = VueRouter.useRoute()
    const fetching = Vue.ref(false)
    const updating = Vue.ref(false)
    const deleting = Vue.ref(false)
    const client = Vue.ref(null)
    const router = VueRouter.useRouter()
    const deleteModalRef = Vue.ref(null)

    const fetchClient = async () => {
      try {
        fetching.value = true
        clearToasts()
        client.value = await api(`clients/${route.params.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get client details: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const updateClient = async () => {
      try {
        updating.value = true
        clearToasts()
        client.value = await api(`clients/${client.value.id}`, 'PUT', client.value)
        appendToast('Client updated successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update client: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updating.value = false
      }
    }

    const deleteClient = async () => {
      try {
        deleting.value = true
        clearToasts()
        await api(`clients/${client.value.id}`, 'DELETE')
        appendToast('Client deleted successfully', { variant: 'success' })
        router.push('/clients')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to delete client: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        deleting.value = false
      }
    }

    Vue.onBeforeMount(fetchClient)

    return {
      state, fetching, updating, deleting, client, deleteModalRef,
      fetchClient, updateClient, deleteClient
    }
  }
}