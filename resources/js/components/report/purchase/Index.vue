<template>
  <div class="text-center">
      <h1 class="text-center my-5 text-bold">Purchase Report</h1>
      <hr>
      <router-link
          to="/reports/purchase/supplier-wise-purchase"
          >
      <button class="btn btn-lg btn-outline-primary p-5 fw-bold m-3">Supplier Wise Purchase</button>
      </router-link>
      <router-link
          to="/reports/purchase/category-wise-purchase"
          >
      <button class="btn btn-lg btn-outline-primary p-5 fw-bold m-3">Category Wise Purchase</button>
    </router-link>
  </div>
</template>

<script>
  export default {
    
  }
</script>

<style scoped>

</style>