export default Vue.reactive({
  user: null,
  org_logo: null,
  hasOne(perms) {
    return perms.some(perm => this.user?.permissions?.includes(perm)) || false
  }
})