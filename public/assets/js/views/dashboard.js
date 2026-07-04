import { toCurrency, toYMD, api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div>
    <article>
      <header class="flex spread">
        <b>Calendar</b>
        <div class="flex" style="gap: .5em">
          <button class="x-small bottom-clear" @click="curMonthIdx = now.getMonth()" :disabled="curMonthIdx === now.getMonth()" title="Go to current month"><i class="bi bi-calendar3"></i></button>
          <div role="group" class="bottom-clear" style="width:auto">
            <button class="x-small" title="Previous month" @click="curMonthIdx--"><i class="bi bi-chevron-left"></i></button>
            <button class="x-small nowrap" disabled>{{ new Date(now.getFullYear(), curMonthIdx).toLocaleString('default', { month: 'long', year: 'numeric' }) }}</button>
            <button class="x-small" title="Next month" @click="curMonthIdx++"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
      </header>
      <div class="calendar">
        <div v-for="(day, index) in monthDays" :key="curMonthIdx + '-' + (day?.number || 'i' + index)"
          :class="['item', { 'today': day?.isToday, 'has-invoices': day?.count > 0 }]">
          <div>{{ day?.number || '' }}</div>
          <div v-if="day && day.count > 0" :class="['total', { danger: day.overdueCount > 0, success: day.paidCount == day.count }]">
            {{ day.income }}
            <i :class="['bi', 'bi-' + day.count + '-circle-fill']" :title="day.count + ' invoices'"></i>
            <i v-if="day.pendingCount > 0" class="bi bi-alarm warning-text" :title="day.pendingCount + ' pending invoices'"></i>
            <i v-if="day.paidCount > 0" class="bi bi-check-circle-fill success-text" :title="day.paidCount + ' paid invoices'"></i>
          </div>
        </div>
      </div>
    </article>
    <article>
      <header>
        <b>Stats</b>
      </header>
    </article>
  </div>`,
  setup() {
    const now = new Date()
    const curMonthIdx = Vue.ref(now.getMonth())
    const invoices = Vue.ref([])

    const monthDays = Vue.computed(() => {
      const days = Array.from({ length: new Date(now.getFullYear(), curMonthIdx.value + 1, 0).getDate() }, (_, i) => i + 1)
      const firstDayOfMonth = new Date(now.getFullYear(), curMonthIdx.value, 1).getDay()
      const arr = Array.from({ length: firstDayOfMonth }, () => null).concat(days)
      while (arr.length % 7 !== 0)
        arr.push(null)
      return arr.map((number, index) => {
        if (number === null) return null
        const date = new Date(now.getFullYear(), curMonthIdx.value, number)
        const items = invoices.value.filter(e => e.due_date === toYMD(date))
        return {
          number,
          isToday: date.getDate() === now.getDate() && curMonthIdx.value === now.getMonth(),
          items,
          count: items.length,
          income: toCurrency(items.reduce((sum, e) => sum + e.total_amount, 0)),
          pendingCount: items.filter(e => !e.paid_date && !e.paid_amount).length,
          paidCount: items.filter(e => !!e.paid_date && !!e.paid_amount).length,
          overdueCount: items.filter(e => e.due_date < toYMD(now) && !e.paid_date && !e.paid_amount).length
        }
      })
    })

    Vue.watch(() => curMonthIdx.value, async () => {
      try {
        clearToasts()
        const startDate = new Date(now.getFullYear(), curMonthIdx.value, 1)
        const endDate = new Date(now.getFullYear(), curMonthIdx.value + 1, 0)
        invoices.value = await api(`invoices?start=${toYMD(startDate)}&end=${toYMD(endDate)}`)
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get invoices: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }, { immediate: true })

    return {
      monthDays, now, curMonthIdx, invoices,
      toCurrency, toYMD
    }
  }
}