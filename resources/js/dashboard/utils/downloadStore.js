import { reactive } from 'vue';
import axios from 'axios';

const store = reactive({
    activeBulkDownloads: [],
    bulkDownloadPollers: {},
    token: null,
    emitter: null,

    setToken(token) {
        this.token = token;
    },

    setEmitter(emitter) {
        this.emitter = emitter;
    },

    async downloadCompletedJob(job) {
        try {
            const response = await axios.get(`/api/comprobantes/descargar-masivo/${job.id}/download`, {
                headers: { 'Authorization': `Bearer ${this.token}` },
                responseType: 'blob',
            });

            const blob = new Blob([response.data], { type: 'application/zip' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `comprobantes-${job.format}.zip`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);

            this.emitter.emit('show-alert', { type: 'success', message: 'La descarga ha comenzado.' });
        } catch (error) {
            console.error('Error downloading completed job:', error);
            this.emitter.emit('show-alert', { type: 'error', message: 'Ocurrió un error al descargar el archivo ZIP.' });
        }
    },

    async pollJobStatus(jobId) {
        try {
            const response = await axios.get(`/api/comprobantes/descargar-masivo/${jobId}/status`, {
                headers: { 'Authorization': `Bearer ${this.token}` },
            });

            const job = response.data.data;
            const jobIndex = this.activeBulkDownloads.findIndex(j => j.id === jobId);

            if (jobIndex !== -1) {
                if (job.status === 'completed') {
                    clearInterval(this.bulkDownloadPollers[jobId]);
                    delete this.bulkDownloadPollers[jobId];
                    this.activeBulkDownloads = this.activeBulkDownloads.filter(j => j.id !== jobId);
                    this.downloadCompletedJob(job);
                } else if (job.status === 'failed') {
                    clearInterval(this.bulkDownloadPollers[jobId]);
                    delete this.bulkDownloadPollers[jobId];
                    this.activeBulkDownloads = this.activeBulkDownloads.filter(j => j.id !== jobId);
                    this.emitter.emit('show-alert', { type: 'error', message: `La descarga masiva de ${job.format.toUpperCase()} ha fallado.` });
                } else {
                    // Mutate the existing object to ensure reactivity
                    const existingJob = this.activeBulkDownloads[jobIndex];
                    existingJob.status = job.status;
                    existingJob.processed_files = job.processed_files;
                    existingJob.total_files = job.total_files;
                }
            }
        } catch (error) {
            console.error(`Error polling for job ${jobId}:`, error);
            clearInterval(this.bulkDownloadPollers[jobId]);
            delete this.bulkDownloadPollers[jobId];
            this.activeBulkDownloads = this.activeBulkDownloads.filter(j => j.id !== jobId);
            this.emitter.emit('show-alert', { type: 'error', message: 'No se pudo verificar el estado de la descarga.' });
        }
    },

    async downloadWithFilters(filters) {
        this.emitter.emit('show-alert', { type: 'info', message: 'Iniciando descarga masiva. Esto puede tardar unos momentos...' });
        try {
            const response = await axios.post('/api/comprobantes/descargar-masivo', filters, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });

            const fullJob = response.data.data;
            this.activeBulkDownloads.push(fullJob);
            this.bulkDownloadPollers[fullJob.id] = setInterval(() => this.pollJobStatus(fullJob.id), 3000);

        } catch (error) {
            console.error('Error en la descarga masiva:', error);
            if (error.response?.status === 404) {
                this.emitter.emit('show-alert', { type: 'warning', message: 'No se encontraron comprobantes con los filtros seleccionados.' });
            } else {
                const errorMessage = error.response?.data?.message || 'Ocurrió un error inesperado.';
                this.emitter.emit('show-alert', { type: 'error', message: `Error en la descarga: ${errorMessage}` });
            }
        }
    },

    clearPollers() {
        Object.keys(this.bulkDownloadPollers).forEach(jobId => {
            clearInterval(this.bulkDownloadPollers[jobId]);
        });
    }
});

export default store;
