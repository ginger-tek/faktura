import state from '../state.js'
import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div>
    <h2>Clients</h2>
    <div class="flex spread bottom-spacing" style="gap:.5rem">
      <input v-model="filter" type="search" placeholder="Filter clients...">
      <button class="nowrap" @click="fetchClients" :aria-busy="fetching" :disabled="fetching"><i v-show="!fetching" class="bi bi-arrow-clockwise"></i> <span>Refresh</span></button>
      <button class="nowrap" @click="newClientModalRef.open()"><i class="bi bi-file-earmark-plus"></i> <span>New Client</span></button>
    </div>
    <auto-table :data="clients" :columns="clientColumns" :bordered="true" :filter="filter">
      <template #full_name="{ id, full_name }"><router-link :to="'/clients/'+id">{{ full_name }}</router-link></template>
      <template #empty-data>No clients</template>
      <template #empty-filter>No clients found for that filter</template>
    </auto-table>
    <modal ref="newClientModalRef" hide-close>
      <template #title>New Client</template>
      <form @submit.prevent="createClient">
        <label>Full Name
          <input type="text" v-model="newClient.full_name" required>
        </label>
        <label>Contact Email
          <input type="email" v-model="newClient.contact_email" required>
        </label>
        <div class="flex stretch">
          <button type="button" class="secondary" @click="newClientModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
          <button type="submit" :aria-busy="submitting" :disabled="submitting"><i class="bi bi-check-circle"></i> Submit</button>
        </div>
      </form>
    </modal>
  </div>`,
  setup() {
    const filter = Vue.ref('')
    const clients = Vue.ref([])
    const fetching = Vue.ref(false)
    const submitting = Vue.ref(false)
    const clientColumns = [
      { key: 'full_name', label: 'Full Name' },
      { key: 'contact_email', label: 'Email' },
      { key: 'contact_phone', label: 'Phone' }
    ]
    const newClientModalRef = Vue.ref(null)
    const newClient = Vue.ref({ full_name: '', contact_email: '' })
    const router = VueRouter.useRouter()

    const fetchClients = async () => {
      try {
        clearToasts()
        filter.value = ''
        fetching.value = true
        clients.value = await api('clients')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get clients: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const createClient = async () => {
      try {
        clearToasts()
        submitting.value = true
        const res = await api('clients', 'POST', newClient.value)
        newClientModalRef.value.close()
        appendToast('Client created successfully', { variant: 'success' })
        router.push(`/clients/${res.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to create client: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    Vue.onBeforeMount(fetchClients)

    return {
      filter, fetching, submitting, clients, clientColumns, newClientModalRef, newClient,
      fetchClients, createClient
    }
  }
}