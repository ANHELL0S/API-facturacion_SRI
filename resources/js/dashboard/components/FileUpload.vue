<template>
  <div
    class="p-6 border-2 border-dashed rounded-lg text-center cursor-pointer transition-colors duration-200 ease-in-out"
    :class="{ 'border-blue-500 bg-blue-50': isDragging, 'hover:border-gray-400': !isDragging }"
    @click="triggerFileInput"
    @dragover.prevent="handleDragOver"
    @dragleave.prevent="handleDragLeave"
    @drop.prevent="handleDrop"
  >
    <input type="file" ref="fileInput" @change="handleFileChange" accept=".xlsx,.pdf" class="hidden">

    <div v-if="!fileName">
      <p class="text-gray-500">Arrastra y suelta tu archivo aquí</p>
      <p class="text-sm text-gray-400">o</p>
      <p class="text-blue-600 font-semibold">haz clic para seleccionar</p>
    </div>

    <div v-else>
      <p class="text-green-600 font-semibold">Archivo seleccionado:</p>
      <p class="text-gray-700">{{ fileName }}</p>
    </div>

    <div v-if="error" class="text-red-500 mt-2 text-sm">
      {{ error }}
    </div>
  </div>
</template>

<script>
import * as XLSX from 'xlsx';
import * as pdfjsLib from 'pdfjs-dist/build/pdf';

pdfjsLib.GlobalWorkerOptions.workerSrc = `//cdnjs.cloudflare.com/ajax/libs/pdf.js/${pdfjsLib.version}/pdf.worker.min.js`;

export default {
  name: 'FileUpload',
  data() {
    return {
      error: '',
      isDragging: false,
      fileName: '',
    };
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    handleDragOver() {
      this.isDragging = true;
    },
    handleDragLeave() {
      this.isDragging = false;
    },
    handleDrop(event) {
      this.isDragging = false;
      const file = event.dataTransfer.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    processFile(file) {
      this.error = '';
      this.fileName = file.name;
      const fileExtension = file.name.split('.').pop().toLowerCase();

      this.$emit('parsing-start');

      if (fileExtension === 'xlsx') {
        this.parseExcel(file);
      } else if (fileExtension === 'pdf') {
        this.parsePdf(file);
      } else {
        this.error = 'Tipo de archivo no soportado. Por favor, sube un archivo Excel (.xlsx) o PDF (.pdf).';
        this.fileName = '';
        this.$emit('parsing-complete');
      }
    },
    parseExcel(file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const data = new Uint8Array(e.target.result);
          const workbook = XLSX.read(data, { type: 'array' });
          let allJsonData = [];

          for (const sheetName of workbook.SheetNames) {
            const worksheet = workbook.Sheets[sheetName];
            const sheetData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });

            if (sheetData.length === 0) {
              continue; // Skip empty sheets
            }

            // Find the header row by looking for key columns
            let headerRowIndex = -1;
            let headers = [];
            for (let i = 0; i < sheetData.length; i++) {
              const row = sheetData[i];
              const normalizedRow = row.map(h => typeof h === 'string' ? h.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") : '');
              if (normalizedRow.includes('codigo') && normalizedRow.includes('cedula')) {
                headerRowIndex = i;
                headers = row;
                break;
              }
            }

            if (headerRowIndex === -1) {
              // If no header found in this sheet, skip it.
              // This allows for sheets that are not data tables.
              continue;
            }

            const dataRows = sheetData.slice(headerRowIndex + 1);
            const jsonData = dataRows.map(row => {
              let rowData = {};
              headers.forEach((header, index) => {
                if (header) { // Only add if header is not empty
                  rowData[header] = row[index];
                }
              });
              return rowData;
            }).filter(row => Object.values(row).some(val => val)); // Filter out completely empty rows

            allJsonData.push(...jsonData);
          }

          if (allJsonData.length === 0) {
            this.error = 'No se encontraron datos válidos en ninguna de las hojas del archivo Excel. Asegúrate de que al menos una hoja contenga las columnas "Código" y "Cédula".';
            return;
          }

          this.$emit('file-parsed', allJsonData);
        } catch (err) {
          this.error = 'Error al procesar el archivo Excel. Asegúrate de que el formato es correcto.';
          console.error(err);
        } finally {
            this.$emit('parsing-complete');
        }
      };
      reader.onerror = () => {
        this.error = 'Error al leer el archivo.';
        this.$emit('parsing-complete');
      };
      reader.readAsArrayBuffer(file);
    },
    async parsePdf(file) {
      // PDF parsing logic remains the same
      const reader = new FileReader();
      reader.onload = async (e) => {
        try {
          const data = new Uint8Array(e.target.result);
          const pdf = await pdfjsLib.getDocument({ data }).promise;
          let allRows = [];
          for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const textContent = await page.getTextContent();
            let pageText = textContent.items.map(item => item.str).join('');
            const lines = pageText.split(/\r\n?|\n/);
            if (i === 1 && lines.length > 0) {
              const headers = lines[0].split(/\s{2,}/);
              const rows = lines.slice(1).map(line => {
                const values = line.split(/\s{2,}/);
                let rowData = {};
                headers.forEach((header, index) => {
                  rowData[header] = values[index];
                });
                return rowData;
              });
              allRows.push(...rows);
            } else {
               const rows = lines.map(line => ({ raw: line }));
               allRows.push(...rows);
            }
          }
          this.$emit('file-parsed', allRows);
        } catch (err) {
          this.error = 'Error al procesar el archivo PDF.';
          console.error(err);
        } finally {
            this.$emit('parsing-complete');
        }
      };
      reader.onerror = () => {
        this.error = 'Error al leer el archivo.';
        this.$emit('parsing-complete');
      };
      reader.readAsArrayBuffer(file);
    },
  },
};
</script>
