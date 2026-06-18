export default {
  template: `<div class="text-center">
    <h2>Unauthorized</h2>
    <p>You do not have permission to view this page.</p>
    <button class="button" @click="$router.back()"><i class="bi bi-arrow-left"></i> Go Back</button>
  </div>`,
}