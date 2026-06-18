export default Vue.reactive({
  user: null,
  hasRoles(roles) {
    return roles.includes(this.user?.role) || false
  }
})