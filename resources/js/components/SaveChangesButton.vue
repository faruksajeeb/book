<template>
  <button
    type="submit"
    :disabled="isSubmitting"
    class="btn btn-lg my-btn-success float-right"
  >
    <div v-if="isSubmitting">
      <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
      </span>
      Changing...Data
    </div>
    <div v-else><i class="fa fa-save"> </i> Save Changes</div>
  </button>
</template>

<script>
export default {
  props: {
    isSubmitting: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
};
</script>

<style></style>
