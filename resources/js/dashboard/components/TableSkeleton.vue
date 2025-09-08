<template>
  <div class="overflow-x-auto shadow-lg sm:rounded-lg border border-gray-200">
    <table class="min-w-full bg-white">
      <thead class="bg-gray-800 text-white">
        <tr>
          <th v-for="i in 5" :key="i" scope="col" class="px-8 py-4">
            <div class="h-4 bg-gray-700 rounded w-3/4 animate-pulse"></div>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="i in 10" :key="i">
          <td v-for="j in 5" :key="j" class="px-8 py-4">
            <div class="h-4 bg-gray-300 rounded w-full animate-pulse"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'TableSkeleton',
};
</script>
