export default {
  props: {
    hideClose: { type: Boolean, default: false }
  },
  template: `<dialog ref="modal">
    <article class="modal-content">
      <header>
        <span v-if="!hideClose" @click="close" class="close" aria-label="Close"></span>
        <b><slot name="title"></slot></b>
      </header>
      <slot></slot>
    </article>
  </dialog>`,
  setup(props, { expose, emit }) {
    const modal = Vue.ref(null)
    const open = () => {
      modal.value?.showModal()
      emit('open')
      setTimeout(() => emit('opened'), 200)
    }
    const close = () => {
      modal.value?.close()
      emit('close')
      setTimeout(() => emit('closed'), 200)
    }
    expose({ open, close })
    return { modal, close }
  }
}