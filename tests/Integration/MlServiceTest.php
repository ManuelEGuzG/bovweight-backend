<?php

namespace Tests\Integration;

use App\Services\MlService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MlServiceTest extends TestCase
{
    private MlService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MlService();
    }

    public function test_estimate_weight_retorna_respuesta_del_ml(): void
    {
        Http::fake([
            '*/api/v1/estimate' => Http::response([
                'bovine_detected'     => true,
                'estimated_weight_kg' => 350.0,
                'confidence'          => 0.92,
                'bounding_box'        => [10, 20, 300, 400],
            ], 200),
        ]);

        $foto = UploadedFile::fake()->image('bovino.jpg', 640, 480);
        $resultado = $this->service->estimateWeight($foto, 'AR-001', 3.5, 'lateral');

        $this->assertTrue($resultado['bovine_detected']);
        $this->assertEquals(350.0, $resultado['estimated_weight_kg']);
        $this->assertEquals(0.92, $resultado['confidence']);
    }

    public function test_estimate_weight_realiza_llamada_al_endpoint_correcto(): void
    {
        Http::fake([
            '*/api/v1/estimate' => Http::response([
                'bovine_detected'     => true,
                'estimated_weight_kg' => 400.0,
                'confidence'          => 0.88,
                'bounding_box'        => [],
            ], 200),
        ]);

        $foto = UploadedFile::fake()->image('bovino.jpg');
        $this->service->estimateWeight($foto, 'AR-002', 5.0, 'frontal');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/estimate');
        });
    }

    public function test_estimate_weight_lanza_excepcion_si_el_ml_retorna_error(): void
    {
        Http::fake([
            '*/api/v1/estimate' => Http::response([], 500),
        ]);

        $this->expectException(\RuntimeException::class);

        $foto = UploadedFile::fake()->image('bovino.jpg');
        $this->service->estimateWeight($foto);
    }

    public function test_send_feedback_envia_los_datos_correctos(): void
    {
        Http::fake([
            '*/api/v1/feedback' => Http::response(['status' => 'ok'], 200),
        ]);

        $this->service->sendFeedback('AR-001', 300.0, 320.0, 'Corrección manual');

        Http::assertSent(function ($request) {
            $body = $request->data();
            return str_contains($request->url(), '/api/v1/feedback')
                && $body['animal_id'] === 'AR-001'
                && $body['estimated_weight_kg'] === 300.0
                && $body['real_weight_kg'] === 320.0
                && $body['notes'] === 'Corrección manual';
        });
    }

    public function test_send_feedback_retorna_array_vacio_si_ml_no_responde(): void
    {
        Http::fake([
            '*/api/v1/feedback' => Http::response(null, 200),
        ]);

        $resultado = $this->service->sendFeedback('AR-001', 300.0, 320.0);

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function test_send_feedback_retorna_array_vacio_si_hay_excepcion(): void
    {
        Http::fake(function () {
            throw new \Exception('Conexión rechazada');
        });

        $resultado = $this->service->sendFeedback('AR-001', 300.0, 320.0);

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}
