import state from '../state.js'
import { api, toDate } from '../utils.js'
import { appendToast, clearToasts } from '../comps/toasts.js'

export default {
  template: `<div>
    <article>
      <form @submit.prevent="updateOrg">
        <div class="flex spread" style="gap:1rem">
          <h2>Organization</h2>
          <button class="nowrap" type="submit" :aria-busy="submitting" :disabled="submitting"><i v-show="!submitting" class="bi bi-check-circle"></i> <span>Save</span></button>
        </div>
        <div class="grid">
          <div>
            <label>Organization Code (readonly)
              <input type="text" :value="org.org_code" readonly>
            </label>
            <label>Display Name
              <input type="text" v-model="org.display_name" required>
            </label>
          </div>
          <div>
            <label>Logo (Max 3MB)</label>
            <img v-if="org.logo" :src="org.logo" alt="Organization Logo" style="max-inline-size: 200px; max-block-size: 100px; margin-inline:auto; object-fit: contain; display: block; margin-bottom: 0.5rem;">
            <div class="flex">
              <button type="button" class="x-small" @click="fromFile"><i class="bi bi-upload"></i> From Device</button>
              <button type="button" class="x-small" @click="urlModal.open()"><i class="bi bi-link-45deg"></i> From URL</button>
            </div>
          </div>
        </div>
      </form>
    </article>
    <article v-if="state.hasOne(['org_settings_read_all'])">
      <h2>Organization Settings</h2>
      <input type="search" v-model="filter" placeholder="Filter settings...">
      <auto-table :data="settings" :columns="settingsColumns" :bordered="true" :filter="filter" class="nowrap">
        <template #setting_key="{ setting_key }"><router-link :to="'/org-settings/' + setting_key">{{ setting_key }}</router-link></template>
        <template #created_at="{ created_at }">{{ toDate(created_at) }}</template>
        <template #updated_at="{ updated_at }">{{ toDate(updated_at) }}</template>
        <template #empty-data>No settings</template>
        <template #empty-filter="value">No settings match the filter "{{ value }}"</template>
      </auto-table>
    </article>
  </div>
  <modal ref="urlModal" hide-close>
    <template #title>Enter Logo URL</template>
    <form @submit.prevent="fromURL">
      <input type="url" name="url" placeholder="https://example.com/logo.png" required>
      <div class="flex">
        <button type="button" class="secondary" @click="cancelUrl"><i class="bi bi-x-circle"></i> Cancel</button>
        <button type="submit" :disabled="submitting" :aria-busy="submitting"><i class="bi bi-check-circle"></i> Submit</button>
      </div>
    </form>
  </modal>`,
  setup() {
    const filter = Vue.ref('')
    const settings = Vue.ref([])
    const settingsColumns = [
      { key: 'setting_key', label: 'Key' },
      { key: 'setting_value', label: 'Value' },
      { key: 'created_at', label: 'Created At' },
      { key: 'updated_at', label: 'Updated At' },
    ]
    const org = Vue.ref({ org_code: '', display_name: '', logo: '' })
    const submitting = Vue.ref(false)
    const urlModal = Vue.ref(null)

    const fetchOrg = async () => {
      try {
        const res = await api('org')
        org.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get organization: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    let controller = null
    const fromURL = async (ev) => {
      try {
        submitting.value = true
        const url = ev.target.url.value.trim()
        controller = new AbortController()
        const res = await fetch(url, { signal: controller.signal })
        if (!res.ok) throw new Error(`${res.status} ${res.statusText}`)
        const blob = await res.blob()
        if (blob.size > 3 * 1024 * 1024)
          throw new Error('File size exceeds 3MB limit')
        const reader = new FileReader()
        reader.onload = (e) => {
          org.value.logo = e.target.result
        }
        reader.readAsDataURL(blob)
        urlModal.value.close()
      } catch (ex) {
        if (ex.name === 'AbortError')
          return appendToast('Image fetch cancelled', { variant: 'warning', stay: true })
        console.error(ex)
        appendToast(`Failed to get image: ${ex.message}`, { variant: 'danger', stay: true })
        org.value.logo = ''
      } finally {
        submitting.value = false
      }
    }

    const cancelUrl = () => {
      if (controller) controller.abort()
      urlModal.value.close()
    }

    const fromFile = () => {
      try {
        submitting.value = true
        const fileInput = document.createElement('input')
        fileInput.type = 'file'
        fileInput.accept = 'image/*'
        fileInput.onchange = () => {
          const file = fileInput.files[0]
          if (file) {
            if (file.size > 3 * 1024 * 1024) {
              appendToast('File size exceeds 3MB limit', { variant: 'danger', stay: true })
              fileInput.value = null
              fileInput.remove()
              return
            }
            const reader = new FileReader()
            reader.onload = (e) => {
              org.value.logo = e.target.result
              fileInput.value = null
              Vue.nextTick(() => fileInput.remove())
            }
            reader.readAsDataURL(file)
          } else {
            appendToast('No file selected', { variant: 'warning', stay: true })
            org.value.logo = ''
          }
        }
        fileInput.click()
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to read file: ${ex.message}`, { variant: 'danger', stay: true })
        org.value.logo = ''
      } finally {
        submitting.value = false
      }
    }

    const updateOrg = async () => {
      try {
        clearToasts()
        submitting.value = true
        await api('org', 'PUT', org.value)
        appendToast('Organization updated successfully', { variant: 'success' })
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to update organization: ${ex.error}`, { variant: 'danger', stay: true })
      } finally {
        submitting.value = false
      }
    }

    const fetchSettings = async () => {
      try {
        const res = await api('settings')
        settings.value = res
      } catch (ex) {
        console.error(ex)
        appendToast(`Failed to get settings: ${ex.error}`, { variant: 'danger', stay: true })
      }
    }

    Vue.onBeforeMount(() => {
      fetchOrg()
      fetchSettings()
    })

    return {
      state, settings, settingsColumns, filter, org, submitting, urlModal,
      fetchSettings, updateOrg, fromURL, fromFile, cancelUrl, toDate
    }
  }
}