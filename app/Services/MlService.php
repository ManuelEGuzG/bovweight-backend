<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlService
{
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ml.url', 'http://localhost:5000'), '/');
        $this->timeout = (int) config('services.ml.timeout', 60);
    }

    public function estimateWeight(
        UploadedFile $photo,
        ?string $animalId = null,
        ?float $distanceMeters = null,
        ?string $photoAngle = null,
    ): array {
        try {
            $params = array_filter([
                'animal_id'       => $animalId,
                'distance_meters' => $distanceMeters,
                'photo_angle'     => $photoAngle,
            ], fn($v) => $v !== null);

            $response = Http::timeout($this->timeout)
                ->attach('image', file_get_contents($photo->getRealPath()), $photo->getClientOriginalName())
                ->post("{$this->baseUrl}/api/v1/estimate", $params);

            $data = $response->json();

            // ── 422: el ML no detectó bovino — propagar el mensaje exacto ────
            // El estimator retorna 422 cuando bovine_detected = false
            if ($response->status() === 422) {
                $message = $data['error']
                    ?? $data['warning']
                    ?? 'No se detectó ningún bovino en la imagen.';

                throw new \RuntimeException($message);
            }

            // ── Cualquier otro error del servidor (500, 503, etc.) ────────────
            if ($response->failed()) {
                Log::error('[MlService] Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('El servicio de estimación no está disponible.');
            }

            // ── Respuesta exitosa pero sin bovino detectado (por si acaso) ────
            // Algunos casos pueden llegar como 200 con bovine_detected = false
            if (isset($data['bovine_detected']) && $data['bovine_detected'] === false) {
                $message = $data['error']
                    ?? $data['warning']
                    ?? 'No se detectó ningún bovino en la imagen.';

                throw new \RuntimeException($message);
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[MlService] Connection error', ['error' => $e->getMessage()]);
            throw new \RuntimeException('No se pudo conectar al servicio de estimación. Verifique que el servicio ML esté activo.');
        }
    }

    public function sendFeedback(string $animalId, float $estimated, float $real, ?string $notes = null): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/feedback", [
                    'animal_id'           => $animalId,
                    'estimated_weight_kg' => $estimated,
                    'real_weight_kg'      => $real,
                    'notes'               => $notes,
                ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::warning('[MlService] Feedback failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}