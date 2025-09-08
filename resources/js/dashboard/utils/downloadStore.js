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
                    this.activeBulkDownloads.splice(jobIndex, 1, job);
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

    async downloadAll(invoices, format) {
        if (invoices.length === 0) {
            this.emitter.emit('show-alert', { type: 'info', message: 'No hay facturas para descargar.' });
            return;
        }

        this.emitter.emit('show-alert', { type: 'info', message: `Preparando la descarga de ${invoices.length} facturas. Te notificaremos cuando esté lista.` });

        try {
            const claves_acceso = invoices.map(invoice => invoice.clave_acceso);
            const response = await axios.post('/api/comprobantes/descargar-masivo', {
                claves_acceso,
                format,
            }, {
                headers: { 'Authorization': `Bearer ${this.token}` },
            });

            if (response.status === 202) {
                const jobId = response.data.data.job_id;
                this.activeBulkDownloads.push({ id: jobId, status: 'pending', format, total_files: invoices.length, processed_files: 0 });
                this.bulkDownloadPollers[jobId] = setInterval(() => this.pollJobStatus(jobId), 3000);
            }
        } catch (error) {
            console.error('Error starting bulk download:', error);
            this.emitter.emit('show-alert', { type: 'error', message: 'Ocurrió un error al iniciar la descarga masiva.' });
        }
    },

    clearPollers() {
        Object.keys(this.bulkDownloadPollers).forEach(jobId => {
            clearInterval(this.bulkDownloadPollers[jobId]);
        });
    }
});

export default store;
