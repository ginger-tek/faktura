import { appendToast, clearToasts } from '../comps/toasts.js'
import { api, money } from '../utils.js'

export default {
  template: `<div>
    <h2>Invoices</h2>
    <div class="flex spread bottom-spacing" style="gap:.5rem">
      <input v-model="filter" type="search" placeholder="Filter invoices...">
      <button class="nowrap" @click="fetchInvoices" :aria-busy="fetching" :disabled="fetching"><i v-show="!fetching" class="bi bi-arrow-clockwise"></i> <span>Refresh</span></button>
      <button class="nowrap" @click="newInvoiceModalRef.open()"><i class="bi bi-file-earmark-plus"></i> <span>New Invoice</span></button>
    </div>
    <auto-table :data="invoices" :columns="invoiceColumns" :bordered="true" :filter="filter" class="nowrap">
      <template #id="{ id }"><router-link :to="'/invoices/'+id">{{ id }}</router-link></template>
      <template #client_full_name="{ client_id, client_full_name }"><router-link :to="'/clients/'+client_id">{{ client_full_name }}</router-link></template>
      <template #labor_amount="{ labor_amount }">{{ money(labor_amount) }}</template>
      <template #expense_amount="{ expense_amount }">{{ money(expense_amount) }}</template>
      <template #total_amount="{ total_amount }">{{ money(total_amount) }}</template>
      <template #due_date="{ due_date }">{{ new Date(due_date).toLocaleDateString() }}</template>
      <template #empty-data>No invoices</template>
      <template #empty-filter>No invoices found for that filter</template>
    </auto-table>
    <modal ref="newInvoiceModalRef" @opened="fetchClients" hide-close>
      <template #title>New Invoice</template>
      <form @submit.prevent="createInvoice">
        <label>Client
          <select v-model="newInvoice.client_id" required>
            <option value="" disabled>Select a client</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.full_name }}</option>
          </select>
        </label>
        <label>Summary
          <input type="text" v-model="newInvoice.summary" required>
        </label>
        <div class="flex stretch">
          <button type="button" class="secondary" @click="newInvoiceModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
          <button type="submit" :aria-busy="submitting" :disabled="submitting"><i class="bi bi-check-circle"></i> Submit</button>
        </div>
      </form>
    </modal>
  </div>`,
  setup() {
    const filter = Vue.ref('')
    const invoices = Vue.ref([])
    const fetching = Vue.ref(false)
    const submitting = Vue.ref(false)
    const invoiceColumns = [
      { key: 'id', label: 'Invoice #' },
      { key: 'summary', label: 'Summary' },
      { key: 'client_full_name', label: 'Client' },
      { key: 'labor_amount', label: 'Labor' },
      { key: 'expense_amount', label: 'Expense' },
      { key: 'total_amount', label: 'Total' },
      { key: 'due_date', label: 'Due' }
    ]
    const newInvoiceModalRef = Vue.ref(null)
    const newInvoice = Vue.ref({ client_id: '', summary: '' })
    const clients = Vue.ref([])
    const router = VueRouter.useRouter()

    const fetchInvoices = async () => {
      try {
        clearToasts()
        filter.value = ''
        fetching.value = true
        const res = await api('invoices')
        invoices.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get invoices: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const fetchClients = async () => {
      try {
        const res = await api('clients')
        clients.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get clients: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    const createInvoice = async () => {
      try {
        clearToasts()
        submitting.value = true
        const res = await api('invoices', 'POST', newInvoice.value)
        newInvoiceModalRef.value.close()
        appendToast('Invoice created successfully', { variant: 'success' })
        router.push(`/invoices/${res.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to create invoice: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    Vue.onBeforeMount(fetchInvoices)

    return {
      filter, fetching, submitting, invoices, invoiceColumns, newInvoiceModalRef, newInvoice, clients,
      fetchInvoices, fetchClients, createInvoice, money
    }
  }
}