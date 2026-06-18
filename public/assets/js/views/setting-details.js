import state from '../state.js'
import { api } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div :aria-busy="fetching"></div>
  <div class="flex stack spread bottom-spacing">
    <div>
      <div class="bottom-spacing-sm secondary" role="link" @click="$router.back()"><i class="bi bi-arrow-left"></i> Back</div>
      <h4>{{ setting?.setting_key }}</h4>
    </div>
    <div class="flex" style="gap:.5rem">
      <button type="button" @click="updateSetting" class="nowrap" :aria-busy="updating" :disabled="updating"><i v-if="!updating" class="bi bi-floppy"></i> Save</button>
      <button type="button" @click="deleteModalRef.open()" class="danger nowrap"><i class="bi bi-trash"></i> Delete</button>
    </div>
  </div>
  <form v-if="setting" @submit.prevent="updateSetting">
    <label>Value
      <textarea v-model="setting.setting_value" style="font-family: monospace;" :rows="setting.setting_value?.split('\\n')?.length || 1" required></textarea>
    </label>
  </form>
  <modal ref="deleteModalRef">
    <template #title>Confirm Delete</template>
    <p>Are you sure you want to delete this setting? This action cannot be undone.</p>
    <div class="flex stretch">
      <button type="button" class="secondary" @click="deleteModalRef.close()"><i class="bi bi-x-circle"></i> Cancel</button>
      <button type="button" class="danger" @click="deleteSetting" :aria-busy="deleting" :disabled="deleting"><i class="bi bi-trash"></i> Delete Setting</button>
    </div>
  </modal>`,
  setup() {
    const route = VueRouter.useRoute()
    const fetching = Vue.ref(false)
    const updating = Vue.ref(false)
    const deleting = Vue.ref(false)
    const setting = Vue.ref(null)
    const router = VueRouter.useRouter()
    const deleteModalRef = Vue.ref(null)

    const fetchSetting = async () => {
      try {
        fetching.value = true
        clearToasts()
        const res = await api(`settings/${route.params.key}`)
        setting.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get setting: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        fetching.value = false
      }
    }

    const updateSetting = async () => {
      try {
        updating.value = true
        clearToasts()
        const res = await api(`settings/${setting.value.setting_key}`, 'PUT', setting.value)
        setting.value = res
        appendToast('Setting updated successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update setting: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        updating.value = false
      }
    }

    const deleteSetting = async () => {
      try {
        deleting.value = true
        clearToasts()
        await api(`settings/${setting.value.setting_key}`, 'DELETE')
        appendToast('Setting deleted successfully', { variant: 'success' })
        router.push('/settings')
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to delete setting: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        deleting.value = false
      }
    }

    Vue.onBeforeMount(fetchSetting)

    return {
      state, fetching, updating, deleting, setting, deleteModalRef,
      fetchSetting, updateSetting, deleteSetting
    }
  }
}