export default {
  props: ['data', 'columns', 'bordered', 'filter'],
  template: `<div class="overflow-auto">
    <table :class="{bordered}">
      <thead>
        <tr>
          <th v-for="col in autoColumns" :key="col.key">{{ col.label }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in filteredData" :key="row.id">
          <td v-for="col in autoColumns" :key="col.key">
            <slot :name="col.key" :="row">{{ row[col.key] }}</slot>
          </td>
        </tr>
        <tr v-if="!data.length">
          <td :colspan="autoColumns.length" class="text-center">
            <slot name="empty-data">No data found</slot>
          </td>
        </tr>
        <tr v-else-if="data.length && !filteredData.length">
          <td :colspan="autoColumns.length" class="text-center">
            <slot name="empty-filter" :="filter">No matching records found for "{{ filter }}"</slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>`,
  setup(props) {
    const filteredData = Vue.computed(() => {
      const filterVal = props.filter?.trim()?.toLowerCase() || ''
      if (!filterVal) return props.data
      return props.data.filter(row => {
        return Object.values(row).some(val =>
          String(val).toLowerCase().includes(filterVal)
        )
      })
    })

    const autoColumns = Vue.computed(() => {
      return props.columns || Object.keys(props.data[0] || {}).map(key => ({ key, label: key })) || []
    })

    return { filteredData, autoColumns }
  }
}