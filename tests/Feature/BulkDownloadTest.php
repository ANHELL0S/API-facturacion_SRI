<?php

namespace Tests\Feature;

use App\Jobs\CreateBulkDownloadZipJob;
use App\Models\BulkDownloadJob;
use App\Models\User;
use App\Models\Comprobante;
use App\Services\FileGenerationService;
use App\Services\SriComprobanteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Enums\EstadosComprobanteEnum;
use App\Enums\BulkDownloadStatusEnum;
use Mockery\MockInterface;

class BulkDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Storage::fake('public');
    }

    public function test_can_initiate_bulk_download_and_dispatches_job()
    {
        // 1. Setup
        $user = User::factory()->create();
        $comprobantes = Comprobante::factory()->count(3)->for($user)->create([
            'estado' => EstadosComprobanteEnum::AUTORIZADO->value,
        ]);
        $clavesAcceso = $comprobantes->pluck('clave_acceso')->toArray();

        // 2. Action
        $response = $this->actingAs($user)->postJson('/api/comprobantes/descargar-masivo', [
            'claves_acceso' => $clavesAcceso,
            'format' => 'pdf',
        ]);

        // 3. Assertions
        $response->assertStatus(202);
        $response->assertJsonStructure(['data' => ['job_id']]);

        $this->assertDatabaseHas('bulk_download_jobs', [
            'user_id' => $user->id,
            'format' => 'pdf',
            'total_files' => 3,
            'status' => BulkDownloadStatusEnum::PENDING->value,
        ]);

        Bus::assertDispatched(CreateBulkDownloadZipJob::class);
    }

    public function test_can_get_bulk_download_status()
    {
        // 1. Setup
        $user = User::factory()->create();
        $job = BulkDownloadJob::factory()->for($user)->create();

        // 2. Action
        $response = $this->actingAs($user)->getJson("/api/comprobantes/descargar-masivo/{$job->id}/status");

        // 3. Assertions
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $job->id]);
    }

    public function test_can_download_completed_zip_file()
    {
        // 1. Setup
        $user = User::factory()->create();
        $job = BulkDownloadJob::factory()->for($user)->create([
            'status' => BulkDownloadStatusEnum::COMPLETED,
            'file_path' => 'bulk-downloads/test.zip',
        ]);
        Storage::disk('public')->put('bulk-downloads/test.zip', 'zip content');

        // 2. Action
        $response = $this->actingAs($user)->get("/api/comprobantes/descargar-masivo/{$job->id}/download");

        // 3. Assertions
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
    }

    public function test_job_creates_zip_and_caches_pdfs_correctly()
    {
        // Use the real Bus for this test
        Bus::fake([CreateBulkDownloadZipJob::class]);

        // 1. Setup
        $user = User::factory()->create();
        $comprobantes = Comprobante::factory()->count(2)->for($user)->create([
            'estado' => EstadosComprobanteEnum::AUTORIZADO->value,
        ]);
        $clavesAcceso = $comprobantes->pluck('clave_acceso')->toArray();
        $xmlString = file_get_contents(base_path('tests/fixtures/sample_invoice.xml'));

        // Mock the SriComprobanteService to be called for each PDF generation
        $sriMock = $this->mock(SriComprobanteService::class);
        $sriMock->shouldReceive('consultarXmlAutorizado')->times(2)->andReturn($xmlString);

        $jobModel = BulkDownloadJob::create([
            'user_id' => $user->id,
            'format' => 'pdf',
            'total_files' => 2,
        ]);

        // 2. Action
        $job = new CreateBulkDownloadZipJob($jobModel, $clavesAcceso);
        // We need the real FileGenerationService, but with a mocked SriComprobanteService
        $fileGenerationService = new FileGenerationService($sriMock);
        $job->handle($fileGenerationService);

        // 3. Assertions
        $jobModel->refresh();
        $this->assertEquals(BulkDownloadStatusEnum::COMPLETED, $jobModel->status);
        $this->assertEquals(2, $jobModel->processed_files);
        $this->assertNotNull($jobModel->file_path);

        // Assert that the final zip file exists
        Storage::disk('public')->assertExists($jobModel->file_path);

        // Assert that the individual PDFs were cached
        foreach ($comprobantes as $comprobante) {
            Storage::disk('public')->assertExists("pdfs/{$comprobante->clave_acceso}.pdf");
        }
    }
}
