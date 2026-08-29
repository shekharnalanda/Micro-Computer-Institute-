<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CentralSyncService
{
    public function enquiry(array $payload): bool
    {
        return $this->send('/api/v1/enquiries', $payload, 'enquiry');
    }

    public function admission(array $payload): bool
    {
        return $this->send('/api/v1/admissions', $payload, 'admission');
    }

    private function send(string $path, array $payload, string $type): bool
    {
        if (! config('services.mci_central.enabled')) {
            return false;
        }

        $baseUrl = rtrim((string) config('services.mci_central.url'), '/');
        $token = (string) config('services.mci_central.token');

        if ($baseUrl === '' || $token === '') {
            Log::warning('MCI central sync skipped: integration configuration missing', ['type' => $type]);
            return false;
        }

        try {
            $response = $this->client($token)->post($baseUrl.$path, $payload);

            if ($response->successful()) {
                Log::info('MCI central sync successful', [
                    'type' => $type,
                    'source_reference_id' => $payload['source_reference_id'] ?? null,
                    'central_reference_id' => $response->json('central_reference_id'),
                    'duplicate' => (bool) $response->json('duplicate', false),
                ]);
                return true;
            }

            Log::warning('MCI central sync failed', [
                'type' => $type,
                'source_reference_id' => $payload['source_reference_id'] ?? null,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('MCI central sync exception', [
                'type' => $type,
                'source_reference_id' => $payload['source_reference_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function client(string $token): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders(['X-MCI-Token' => $token])
            ->connectTimeout(5)
            ->timeout(12)
            ->retry(2, 300, throw: false);
    }
}
