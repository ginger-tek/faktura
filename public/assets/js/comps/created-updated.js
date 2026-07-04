import { toDate } from '../utils.js'

export default {
  props: {
    obj: { type: Object, required: true }
  },
  template: `<small>
    <div v-if="obj?.created_by"><i class="bi bi-plus-circle"></i> Created by {{ created }}</div>
    <div v-if="obj?.updated_by"><i class="bi bi-clock-history"></i> Updated by {{ updated }}</div>
  </small>`,
  setup(props) {
    const created = Vue.computed(() => props.obj?.created_by ? `${props.obj.created_by?.split('|')[0]} @ ${toDate(props.obj.created_at)}` : '')
    const updated = Vue.computed(() => props.obj?.updated_by ? `${props.obj.updated_by?.split('|')[0]} @ ${toDate(props.obj.updated_at)}` : '')

    return { created, updated }
  }
}