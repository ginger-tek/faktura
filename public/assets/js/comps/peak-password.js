export default {
  template: `<span ref="peakRef"><i class="bi bi-eye"></i></span>`,
  setup() {
    const peakRef = Vue.ref(null)

    const toggle = (show = true) => {
      const input = peakRef.value.nextElementSibling
      if (input) input.type = show ? 'text' : 'password'
    }

    Vue.onMounted(() => {
      peakRef.value.addEventListener('mousedown', () => toggle(true))
      peakRef.value.addEventListener('mouseup', () => toggle(false))
      peakRef.value.addEventListener('mouseleave', () => toggle(false))
      peakRef.value.addEventListener('touchstart', () => toggle(true))
      peakRef.value.addEventListener('touchend', () => toggle(false))
    })

    return { peakRef }
  }
}