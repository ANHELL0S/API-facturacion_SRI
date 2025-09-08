<template>
  <div class="overflow-x-auto sm:rounded-lg">
    <table class="min-w-full bg-white">
      <thead class="bg-gray-800 text-white">
        <tr>
          <th v-for="header in headers" :key="header.value" scope="col"
              class="px-4 py-4 text-left text-sm font-bold uppercase tracking-wider cursor-pointer select-none"
              @click="header.value !== 'acciones' ? $emit('sort', header.value) : null">
            <div class="flex items-center">
              <span>{{ header.text }}</span>
              <span v-if="sortKey === header.value" class="ml-2">
                <svg v-if="sortOrder === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </span>
            </div>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-if="!data || data.length === 0">
          <td :colspan="headers.length" class="px-8 py-5 text-center text-gray-500 text-lg">
            No hay datos para mostrar.
          </td>
        </tr>
        <tr v-for="row in data" :key="row.id" class="hover:bg-gray-100 transition-colors duration-200">
          <td v-for="header in headers" :key="header.value" class="px-8 py-4 whitespace-nowrap text-md text-gray-800">
            <slot :name="`cell(${header.value})`" :row="row">
              <!-- Default Fallback Content -->
              <span v-if="header.value.toLowerCase().includes('estado')" :class="getStatusClass(row[header.value])" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                {{ row[header.value] }}
              </span>
              <span v-else-if="header.value === 'Evento' && row.Evento">
                <span v-if="!row.isExpanded" @click="$emit('toggle-expansion', row.id)" class="cursor-pointer" :title="row.Evento">
                  {{ truncateText(row.Evento, 20) }}
                </span>
                <span v-else @click="$emit('toggle-expansion', row.id)" class="cursor-pointer">
                  {{ row.Evento }}
                </span>
              </span>
              <span v-else-if="header.value === 'errorInfo' && row.errorInfo">
                  <span v-if="!row.isExpanded" @click="$emit('toggle-expansion', row.id)" class="cursor-pointer text-red-600" :title="row.errorInfo">
                      {{ truncateText(row.errorInfo, 30) }}
                  </span>
                  <span v-else @click="$emit('toggle-expansion', row.id)" class="cursor-pointer text-red-600">
                      {{ row.errorInfo }}
                  </span>
              </span>
              <span v-else-if="header.value === 'error_message' && row.error_message">
                <span v-if="!row.isErrorExpanded" @click="$emit('toggle-error-expansion', row.id)" class="cursor-pointer" :title="row.error_message">
                  {{ truncateText(row.error_message, 30) }}
                </span>
                <span v-else @click="$emit('toggle-error-expansion', row.id)" class="cursor-pointer">
                  {{ row.error_message }}
                </span>
              </span>
              <span v-else-if="header.value === 'acciones'" class="space-x-2 text-center">
                  <!-- Download Buttons -->
                  <button v-if="(row.estado === 'autorizado' && row.fecha_autorizacion) || row.error_message === 'ERROR SECUENCIAL REGISTRADO'" @click="$emit('download-xml', row.clave_acceso)" title="Descargar XML" class="p-1 text-blue-600 hover:text-blue-800 transition-colors">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path></svg>
                  </button>
                  <button v-if="(row.estado === 'autorizado' && row.fecha_autorizacion) || row.error_message === 'ERROR SECUENCIAL REGISTRADO'" @click="$emit('download-pdf', row.clave_acceso)" title="Descargar PDF" class="p-1 text-red-600 hover:text-red-800 transition-colors">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                  </button>
                  <button v-if="row.estado === 'autorizado' && row.fecha_autorizacion" @click="$emit('preview-pdf', row.clave_acceso)" title="Previsualizar PDF" class="p-1 text-purple-600 hover:text-purple-800 transition-colors">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  </button>
                  <!-- Edit Button -->
                  <button v-if="showEditButton" @click="$emit('open-edit-modal', row)" title="Editar" class="p-1 text-yellow-600 hover:text-yellow-800 transition-colors">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                  </button>
              </span>
              <span v-else>
                {{ row[header.value] }}
              </span>
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'DataTable',
  props: {
    data: {
      type: Array,
      required: true,
    },
    headers: {
      type: Array,
      required: true,
    },
    showEditButton: {
      type: Boolean,
      default: false,
    },
    sortKey: {
      type: String,
      default: '',
    },
    sortOrder: {
      type: String,
      default: 'asc',
    },
  },
  methods: {
    truncateText(text, length) {
      if (text && text.length > length) {
        return text.substring(0, length) + '...';
      }
      return text;
    },
    getStatusClass(status) {
      const lowerStatus = status ? String(status).toLowerCase() : '';
      switch (lowerStatus) {
        case 'facturado':
        case 'autorizado':
          return 'bg-green-100 text-green-800';
        case 'no facturado':
        case 'rechazado':
        case 'fallido':
          return 'bg-red-100 text-red-800';
        case 'enviado':
        case 'firmado':
            return 'bg-indigo-100 text-indigo-800';
        case 'procesando':
          return 'bg-blue-100 text-blue-800';
        case 'pago pendiente':
            return 'bg-orange-100 text-orange-800';
        case 'pendiente':
        default:
          return 'bg-yellow-100 text-yellow-800';
      }
    },
  },
};
</script>
