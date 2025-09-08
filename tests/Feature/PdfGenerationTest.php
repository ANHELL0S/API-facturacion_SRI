<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Comprobante;
use App\Models\Establecimiento;
use App\Models\PuntoEmision;
use App\Services\SriComprobanteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;
use App\Enums\EstadosComprobanteEnum;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_and_caches_pdf_for_invoice()
    {
        // 1. Setup
        Storage::fake('public');
        $user = User::factory()->create();
        $comprobante = Comprobante::factory()->for($user)->create([
            'estado' => EstadosComprobanteEnum::AUTORIZADO->value,
        ]);
        $claveAcceso = $comprobante->clave_acceso;
        $cachedPdfPath = "pdfs/{$claveAcceso}.pdf";

        // 2. Mock the SriComprobanteService
        $xmlString = file_get_contents(base_path('tests/fixtures/sample_invoice.xml'));
        $mock = $this->mock(SriComprobanteService::class);
        $mock->shouldReceive('consultarXmlAutorizado')->once()->andReturn($xmlString);

        // 3. Action (First Request)
        $response1 = $this->actingAs($user)->getJson(route('comprobantes.pdf', ['clave_acceso' => $claveAcceso]));

        // 4. Assertions (First Request)
        $response1->assertStatus(200);
        $response1->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response1->getContent());
        Storage::disk('public')->assertExists($cachedPdfPath);

        // 5. Action (Second Request)
        $response2 = $this->actingAs($user)->getJson(route('comprobantes.pdf', ['clave_acceso' => $claveAcceso]));

        // 6. Assertions (Second Request)
        $response2->assertStatus(200);
        $this->assertEquals($response1->getContent(), $response2->getContent());
        // The mock will fail the test if `consultarXmlAutorizado` is called more than once.
    }
}
