<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeguridadEndpointsTest extends TestCase
{
    public function test_endpoint_personas_requiere_autenticacion()
    {
        $response = $this->getJson('/api/v1/personas');

        $response->assertStatus(401);
    }

    public function test_endpoint_reportes_requiere_autenticacion()
    {
        $response = $this->getJson('/api/v1/fincas/1/reporte');

        $response->assertStatus(401);
    }

    public function test_endpoint_exportar_excel_requiere_autenticacion()
    {
        $response = $this->getJson('/api/v1/fincas/1/reporte/excel');

        $response->assertStatus(401);
    }

    public function test_endpoint_exportar_pdf_requiere_autenticacion()
    {
        $response = $this->getJson('/api/v1/fincas/1/reporte/pdf');

        $response->assertStatus(401);
    }
}
