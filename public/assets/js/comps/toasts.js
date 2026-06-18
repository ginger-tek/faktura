const toasts = Vue.ref([])

export const appendToast = (message, options = {}) => {
  const id = Date.now()
  toasts.value.push({ id, message, ...options })
  if (!options.stay)
    setTimeout(() => dismissToast(id), options.duration || 3000)
}

export const dismissToast = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

export const clearToasts = () => {
  toasts.value = []
}

export const Toaster = {
  template: `<div class="toaster" style="position:absolute;top:0;right:0;left:0;z-index:10;padding:1rem;max-inline-size:400px;margin-inline:auto">
    <article v-for="toast in toasts" :key="toast.id" :class="'flex spread alert ' + toast.variant">
      <span>{{ toast.message }}</span>
      <span aria-label="Close" @click="dismissToast(toast.id)" class="pointer" style="transform:scale(1.5);vertical-align:middle">&times;</span>
    </article>
  </div>`,
  setup() {
    return { toasts, dismissToast }
  }
}

export default {
  appendToast,
  dismissToast,
  clearToasts,
  Toaster
}