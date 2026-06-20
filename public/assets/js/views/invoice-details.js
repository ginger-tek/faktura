import state from '../state.js'
import { appendToast, clearToasts } from '../comps/toasts.js'
import { api, toMoney, toDate } from '../utils.js'

export default {
  template: `<div :aria-busy="fetching"></div>
  <form v-if="invoice" @submit.prevent="updateInvoice" :readonly="!state.hasOne(['invoice_update']) || undefined">
    <div class="flex stack spread bottom-spacing">
      <div>
        <div class="bottom-spacing-sm secondary" role="link" @click="$router.back()"><i class="bi bi-arrow-left"></i> Back</div>
        <h4 class="bottom-clear">{{ invoice.summary }}</h4>
      </div>
      <created-updated :obj="invoice"></created-updated>
      <div class="flex" style="gap:.5rem">
        <button type="button" @click="cloneModalRef.open()" class="nowrap"><i class="bi bi-copy"></i> <span>Clone</span></button>
        <button type="button" @click="printInvoice" class="nowrap"><i class="bi bi-printer"></i> <span>Print</span></button>
        <button type="submit" class="nowrap" :aria-busy="updating" :disabled="updating"><i v-if="!updating" class="bi bi-floppy"></i> <span>Save</span></button>
        <button type="button" @click="deleteModalRef.open()" class="danger nowrap"><i class="bi bi-trash"></i> <span>Delete</span></button>
      </div>
    </div>
    <div class="grid">
      <label class="clear-children">Summary
        <input type="text" class="bottom-clear" v-model="invoice.summary" required>
      </label>
      <label>Client
        <div class="flex top-spacing-sm" style="gap:.5rem">
          <details ref="selector" class="dropdown" @click="fetchClients">
            <summary>{{ invoice.client_full_name }}</summary>
            <ul dir="rtl">
              <li dir="ltr" v-for="client in clients" :key="client.id">
                <a class="pointer" @click="setClient(client)">{{ client.full_name }}</a>
              </li>
            </ul>
          </details>
          <router-link style="flex:0" :to="'/clients/'+invoice.client_id" role="button" class="nowrap"><i class="bi bi-person-fill"></i> <span>View</span></router-link>
        </div>
      </label>
    </div>
    <label>Details
      <textarea v-model="invoice.details" :rows="invoice.details?.split('\\n')?.length || 1" ></textarea>
    </label>
    <div class="grid">
      <label>Due Date
        <input type="date" v-model="invoice.due_date">
      </label>
      <label>Paid Date
        <input type="date" v-model="invoice.paid_date">
      </label>
    </div>
    <div class="grid">
      <label>Labor Hours
        <input type="number" v-model.number="invoice.labor_hours" min="1" required>
      </label>
      <label>Labor Rate
        <input type="number" v-model.number="invoice.labor_rate" step="0.01" min="1" required>
      </label>
      <label>Labor Amount
        <input :value="invoice.labor_hours * invoice.labor_rate" readonly>
      </label>
    </div>
    <div class="flex spread bottom-spacing-sm">
      <label>Itemization</label>
      <button type="button" @click="addExpenseModalRef.open()" class="nowrap x-small"><i class="bi bi-receipt"></i><i class="bi bi-plus"></i> <span>Add Expense</span></button>
    </div>
    <auto-table :data="invoiceItems" :columns="itemColumns" :bordered="true" class="nowrap">
      <template #empty-data>No invoice items</template>
      <template #summary="{ expense_id, summary }"><router-link :to="'/expenses/' + expense_id">{{ summary }}</router-link></template>
      <template #purchase_date="{ purchase_date }">{{ toDate(purchase_date, 'date') }}</template>
      <template #unit_price="{ unit_price }">{{ toMoney(unit_price) }}</template>
      <template #total_amount="{ total_amount, expense_id }">
        {{ toMoney(total_amount) }}
        <div style="float:right" data-tooltip="Remove Expense" data-placement="left"><i class="bi bi-x-circle danger-text pointer" @click="removeExpense({ expense_id })"></i></div>
      </template>
    </auto-table>
    <article class="flex alert info top-spacing" style="max-inline-size:max-content;margin-inline:auto">
      <div class="text-center nowrap">
        <small>Expenses</small>
        <h3>{{ toMoney(invoiceItems.reduce((sum, item) => sum + item.total_amount, 0)) }}</h3>
      </div>
      <div></div>
      <div class="text-center nowrap">
        <small>Invoice Total</small>
        <h3>{{ toMoney(invoice.labor_hours * invoice.labor_rate + invoiceItems.reduce((sum, item) => sum + item.total_amount, 0)) }}</h3>
      </div>
    </article>
  </form>
  <modal ref="cloneModalRef" hide-close>
    <template #title>Clone Invoice</template>
    <p>This will create a new invoice with the same client, summary, details, labor hours, labor rate, and items,
      but with "Copy of " prefixed to the summary and no due date or paid date set.</p>
    <div class="flex stretch">
      <button type="button" class="secondary" @click="cloneModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
      <button type="button" class="primary" @click="cloneInvoice" :aria-busy="cloning" :disabled="cloning"><i class="bi bi-copy"></i> Clone Invoice</button>
    </div>
  </modal>
  <modal ref="addExpenseModalRef" @opened="fetchExpenses" @closed="selectedExpense.expense_id = ''" hide-close>
    <template #title>Add Expense</template>
    <form @submit.prevent="addExpense">
      <label>Expense
        <select v-model="selectedExpense.expense_id" required>
          <option value="" disabled>Select an expense</option>
          <option v-for="expense in expenses" :key="expense.id" :value="expense.id">
            {{ expense.summary }} | {{ toMoney(expense.total_amount) }} | {{ toDate(expense.purchase_date, 'date') }}
          </option>
        </select>
      </label>
      <div class="flex stretch">
        <button type="button" class="secondary" @click="addExpenseModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
        <button type="submit" :aria-busy="adding" :disabled="adding"><i class="bi bi-plus-circle"></i> Add Expense</button>
      </div>
    </form>
  </modal>
  <modal ref="deleteModalRef" hide-close>
    <template #title>Confirm Delete</template>
    <p>Are you sure you want to delete this invoice? This action cannot be undone.</p>
    <div class="flex stretch">
      <button type="button" class="secondary" @click="deleteModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
      <button type="button" class="danger" @click="deleteInvoice" :aria-busy="deleting" :disabled="deleting"><i class="bi bi-trash"></i> Delete Invoice</button>
    </div>
  </modal>`,
  setup() {
    const route = VueRouter.useRoute()
    const fetching = Vue.ref(false)
    const cloning = Vue.ref(false)
    const updating = Vue.ref(false)
    const deleting = Vue.ref(false)
    const invoice = Vue.ref(null)
    const invoiceItems = Vue.ref([])
    const clients = Vue.ref([])
    const selector = Vue.ref(null)
    const router = VueRouter.useRouter()
    const cloneModalRef = Vue.ref(null)
    const deleteModalRef = Vue.ref(null)
    const addExpenseModalRef = Vue.ref(null)
    const expenses = Vue.ref([])
    const selectedExpense = Vue.ref({ expense_id: '' })
    const adding = Vue.ref(false)
    const itemColumns = [
      { key: 'summary', label: 'Summary' },
      { key: 'purchase_date', label: 'Purchase Date' },
      { key: 'unit_price', label: 'Unit Price' },
      { key: 'quantity', label: 'Quantity' },
      { key: 'total_amount', label: 'Total Amount' }
    ]

    const fetchInvoiceDetails = async () => {
      try {
        fetching.value = true
        clearToasts()
        const res = await api(`invoices/${route.params.id}`)
        invoice.value = res
        const items = await api(`invoices/${route.params.id}/items`)
        invoiceItems.value = items
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get invoice details: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const fetchClients = async () => {
      if (clients.value.length) return
      try {
        const res = await api('clients')
        clients.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get clients: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    const setClient = (client) => {
      invoice.value.client_id = client.id
      invoice.value.client_full_name = client.full_name
      selector.value.removeAttribute('open')
    }

    const cloneInvoice = async () => {
      try {
        cloning.value = true
        clearToasts()
        const res = await api(`invoices/${invoice.value.id}/clone`, 'POST')
        appendToast('Invoice cloned successfully', { variant: 'success' })
        cloneModalRef.value.close()
        router.push(`/invoices/${res.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to clone invoice: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        cloning.value = false
      }
    }

    const updateInvoice = async () => {
      try {
        updating.value = true
        clearToasts()
        invoice.value = await api(`invoices/${invoice.value.id}`, 'PUT', invoice.value)
        appendToast('Invoice updated successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update invoice: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updating.value = false
      }
    }

    const fetchExpenses = async () => {
      try {
        expenses.value = await api('expenses')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get expenses: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    const addExpense = async () => {
      try {
        adding.value = true
        clearToasts()
        const item = await api(`invoices/${invoice.value.id}/add-expense`, 'POST', selectedExpense.value)
        invoiceItems.value.push(item)
        addExpenseModalRef.value.close()
        appendToast('Expense added successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to add expense: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        adding.value = false
      }
    }

    const removeExpense = async (item) => {
      try {
        clearToasts()
        await api(`invoices/${invoice.value.id}/remove-expense`, 'POST', { expense_id: item.expense_id })
        invoiceItems.value = invoiceItems.value.filter(i => i.expense_id !== item.expense_id)
        appendToast('Expense removed successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to remove expense: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    const deleteInvoice = async () => {
      try {
        deleting.value = true
        clearToasts()
        await api(`invoices/${invoice.value.id}`, 'DELETE')
        appendToast('Invoice deleted successfully', { variant: 'success' })
        router.replace('/invoices')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to delete invoice: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        deleting.value = false
      }
    }

    const printInvoice = async () => {
      try {
        clearToasts()
        const html = await api(`invoices/${invoice.value.id}/print`, 'GET', null, 'text/html')
        try {
          const printWindow = window.open(`about:blank`, '_blank')
          printWindow.document.write(`<html>
            <head><title>Invoice #${invoice.value.id} - ${invoice.value.summary} - ${invoice.value.client_full_name}</title></head>
            <body>${html}</body>
          </html>`)
          printWindow.document.close()
          printWindow.focus()
          printWindow.print()
          printWindow.close()
        } catch (ex) {
          console.error(ex)
          appendToast(`Failed to print invoice: ${ex.message}`, { variant: 'danger', stay: true })
        }
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to render invoice: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    Vue.onBeforeMount(fetchInvoiceDetails)

    return {
      state, fetching, cloning, updating, deleting, invoice, invoiceItems, clients, selector, deleteModalRef, itemColumns, selectedExpense, adding, expenses, addExpenseModalRef, cloneModalRef,
      fetchClients, setClient, fetchInvoiceDetails, updateInvoice, deleteInvoice, printInvoice, addExpense, removeExpense, fetchExpenses, cloneInvoice, toMoney, toDate
    }
  }
}