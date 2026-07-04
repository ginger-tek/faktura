import state from '../state.js'
import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div :aria-busy="fetching"></div>
  <form v-if="expense" @submit.prevent="updateExpense" :readonly="!state.hasOne(['expense_update']) || undefined">
    <div class="flex stack spread bottom-spacing">
      <div>
        <div class="bottom-spacing-sm secondary" role="link" @click="$router.back()"><i class="bi bi-arrow-left"></i> Back</div>
        <h4 class="bottom-clear">{{ expense.summary }}</h4>
      </div>
      <div class="flex" style="gap:.5rem">
        <button type="button" @click="updateExpense" v-if="state.hasOne(['expense_update'])" class="nowrap" :aria-busy="updating" :disabled="updating"><i v-if="!updating" class="bi bi-floppy"></i> Save</button>
        <button type="button" @click="deleteModalRef.open()" v-if="state.hasOne(['expense_delete'])" class="danger nowrap"><i class="bi bi-trash"></i> Delete</button>
      </div>
    </div>
    <label>Summary
      <input type="text" v-model="expense.summary" required>
    </label>
    <div class="grid">
      <label>Unit Price
        <input type="number" v-model="expense.unit_price" step="0.01" min="1" required>
      </label>
      <label>Quantity
        <input type="number" v-model="expense.quantity" step="1" min="1" required>
      </label>
      <label>Purchase Date
        <input type="date" v-model="expense.purchase_date" required>
      </label>
    </div>
    <div class="bottom-spacing">
      <created-updated :obj="expense"></created-updated>
      <small>ID: {{ expense?.id }}</small>
    </div>
  </form>
  <modal ref="deleteModalRef">
    <template #title>Confirm Delete</template>
    <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
    <div class="flex stretch">
      <button type="button" class="secondary" @click="deleteModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
      <button type="button" class="danger" @click="deleteExpense" :aria-busy="deleting" :disabled="deleting"><i class="bi bi-trash"></i> Delete Expense</button>
    </div>
  </modal>`,
  setup() {
    const route = VueRouter.useRoute()
    const fetching = Vue.ref(false)
    const updating = Vue.ref(false)
    const deleting = Vue.ref(false)
    const expense = Vue.ref(null)
    const router = VueRouter.useRouter()
    const deleteModalRef = Vue.ref(null)

    const fetchExpense = async () => {
      try {
        fetching.value = true
        clearToasts()
        expense.value = await api(`expenses/${route.params.id}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get expense details: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const updateExpense = async () => {
      try {
        updating.value = true
        clearToasts()
        expense.value = await api(`expenses/${expense.value.id}`, 'PUT', expense.value)
        appendToast('Expense updated successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update expense: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updating.value = false
      }
    }

    const deleteExpense = async () => {
      try {
        deleting.value = true
        clearToasts()
        await api(`expenses/${expense.value.id}`, 'DELETE')
        appendToast('Expense deleted successfully', { variant: 'success' })
        router.push('/expenses')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to delete expense: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        deleting.value = false
      }
    }

    Vue.onBeforeMount(fetchExpense)

    return {
      state, fetching, updating, deleting, expense, deleteModalRef,
      fetchExpense, updateExpense, deleteExpense
    }
  }
}