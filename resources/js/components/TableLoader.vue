<!-- Loading State -->
<template v-if="loading">
    <!-- Skeleton loader while applying filters -->
    <tr v-for="i in 10" :key="i" class="hover:bg-gray-100 focus-within:bg-gray-100">
      <td class="border-t px-6 py-4"><div class="skeleton h-4 w-32"></div></td>
      <td class="border-t px-6 py-4"><div class="skeleton h-4 w-24"></div></td>
      <td class="border-t px-6 py-4"><div class="skeleton h-4 w-40"></div></td>
    </tr>
  </template>
  <script>

</script>