import state from '../state.js'
import { api, toCurrency, toDate } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div>
    <h2>Expenses</h2>
    <div class="flex spread bottom-spacing" style="gap:.5rem">
      <input v-model="filter" type="search" placeholder="Filter expenses...">
      <button class="nowrap" @click="fetchExpenses" :aria-busy="fetching" :disabled="fetching"><i v-show="!fetching" class="bi bi-arrow-clockwise"></i> <span>Refresh</span></button>
      <button class="nowrap" @click="newExpenseModalRef.open()"><i class="bi bi-file-earmark-plus"></i> <span>New Expense</span></button>
    </div>
    <auto-table :data="expenses" :columns="expenseColumns" :bordered="true" :filter="filter" class="nowrap">
      <template #summary="{ id, summary }"><router-link :to="'/expenses/'+id">{{ summary }}</router-link></template>
      <template #unit_price="{ unit_price }">{{ toCurrency(unit_price) }}</template>
      <template #total_amount="{ total_amount }">{{ toCurrency(total_amount) }}</template>
      <template #purchase_date="{ purchase_date }">{{ toDate(purchase_date, 'date') }}</template>
      <template #empty-data>No expenses</template>
      <template #empty-filter>No expenses found for that filter</template>
    </auto-table>
    <modal ref="newExpenseModalRef" hide-close>
      <template #title>New Expense</template>
      <form @submit.prevent="createExpense">
        <label>Summary
          <input type="text" v-model="newExpense.summary" required>
        </label>
        <div class="grid">
          <label>Unit Price
            <input type="number" v-model="newExpense.unit_price" step="0.01" min="1" required>
          </label>
          <label>Quantity
            <input type="number" v-model="newExpense.quantity" step="1" min="1" required>
          </label>
          <label>Purchase Date
            <input type="date" v-model="newExpense.purchase_date" required>
          </label>
        </div>
        <div class="flex stretch">
          <button type="button" class="secondary" @click="newExpenseModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
          <button type="submit" :aria-busy="submitting" :disabled="submitting"><i class="bi bi-check-circle"></i> Submit</button>
        </div>
      </form>
    </modal>
  </div>`,
  setup() {
    const filter = Vue.ref('')
    const expenses = Vue.ref([])
    const fetching = Vue.ref(false)
    const submitting = Vue.ref(false)
    const expenseColumns = [
      { key: 'summary', label: 'Summary' },
      { key: 'unit_price', label: 'Unit Price' },
      { key: 'quantity', label: 'Quantity' },
      { key: 'total_amount', label: 'Total Amount' },
      { key: 'purchase_date', label: 'Purchase Date' }
    ]
    const newExpenseModalRef = Vue.ref(null)
    const newExpense = Vue.ref({ summary: '', unit_price: 1, quantity: 1, purchase_date: new Date().toISOString().split('T')[0] })
    const router = VueRouter.useRouter()

    const fetchExpenses = async () => {
      try {
        clearToasts()
        filter.value = ''
        fetching.value = true
        expenses.value = await api('expenses')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get expenses: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const createExpense = async () => {
      try {
        clearToasts()
        submitting.value = true
        const res = await api('expenses', 'POST', newExpense.value)
        newExpenseModalRef.value.close()
        appendToast('Expense created successfully', { variant: 'success' })
        router.push(`/expenses/${res.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to create expense: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    Vue.onBeforeMount(fetchExpenses)

    return {
      filter, fetching, submitting, expenses, expenseColumns, newExpenseModalRef, newExpense,
      fetchExpenses, createExpense, toCurrency, toDate
    }
  }
}