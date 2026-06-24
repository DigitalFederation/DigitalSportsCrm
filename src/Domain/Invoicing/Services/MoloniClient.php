<?php

namespace Domain\Invoicing\Services;

use Domain\Invoicing\Exceptions\MoloniApiException;
use Domain\Invoicing\Exceptions\MoloniAuthenticationException;
use Domain\Invoicing\Models\MoloniSyncLog;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoloniClient
{
    public function __construct(
        private MoloniAuthService $authService,
        private MoloniSettingsService $settingsService
    ) {}

    public function post(string $endpoint, array $data = []): array
    {
        $token = $this->authService->getValidToken();
        $config = config('invoicing.providers.moloni');
        $startTime = microtime(true);

        $companyId = $this->settingsService->getCompanyId();
        if ($companyId) {
            $data['company_id'] = $companyId;
        }

        $url = $config['base_url'] . ltrim($endpoint, '/') . '?' . http_build_query([
            'access_token' => $token,
            'human_errors' => 'true',
        ]);

        Log::debug('Moloni API request', [
            'endpoint' => $endpoint,
            'data_keys' => array_keys($data),
        ]);

        try {
            $response = Http::asForm()
                ->timeout($config['timeout'])
                ->retry(3, 1000, function (\Exception $exception) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->post($url, $data);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                $this->handleError($response, $endpoint, $durationMs);
            }

            $responseData = $response->json();

            Log::debug('Moloni API response', [
                'endpoint' => $endpoint,
                'duration_ms' => $durationMs,
            ]);

            return is_array($responseData) ? $responseData : [];

        } catch (MoloniApiException|MoloniAuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            Log::error('Moloni API error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            throw new MoloniApiException(
                'Moloni API request failed: ' . $e->getMessage(),
                0,
                $endpoint,
                null,
                $e
            );
        }
    }

    private function handleError(Response $response, string $endpoint, int $durationMs): void
    {
        $body = $response->json() ?? [];
        $statusCode = $response->status();

        Log::error('Moloni API error response', [
            'endpoint' => $endpoint,
            'status' => $statusCode,
            'response' => $body,
        ]);

        if ($statusCode === 401) {
            MoloniSyncLog::logFailure('api_call', 'Authentication failed', [
                'endpoint' => $endpoint,
                'status' => $statusCode,
            ], $durationMs);

            throw new MoloniAuthenticationException('Moloni API authentication failed. Token may be invalid.');
        }

        $errorMessage = $this->extractErrorMessage($body, $statusCode);

        MoloniSyncLog::logFailure('api_call', $errorMessage, [
            'endpoint' => $endpoint,
            'status' => $statusCode,
            'response' => $body,
        ], $durationMs);

        throw new MoloniApiException(
            $errorMessage,
            $statusCode,
            $endpoint,
            $body
        );
    }

    private function extractErrorMessage(array $body, int $statusCode): string
    {
        $possibleFields = ['error_message', 'error', 'message', 'msg', 'error_description'];

        foreach ($possibleFields as $field) {
            if (isset($body[$field]) && is_string($body[$field])) {
                return $body[$field];
            }
        }

        if (isset($body[0]) && is_array($body[0])) {
            $errors = [];
            foreach ($body as $error) {
                if (isset($error['description'])) {
                    $errors[] = $error['description'];
                }
            }
            if (! empty($errors)) {
                return implode('; ', $errors);
            }
        }

        return match ($statusCode) {
            400 => 'Bad request - invalid data sent to Moloni',
            403 => 'Access forbidden - insufficient permissions',
            404 => 'Endpoint not found',
            422 => 'Validation failed - check data parameters',
            429 => 'Rate limit exceeded - try again later',
            500 => 'Moloni server error - try again later',
            503 => 'Moloni service unavailable - try again later',
            default => "Moloni API request failed with status {$statusCode}",
        };
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->post('companies/getAll/');

            return isset($response[0]['company_id']);
        } catch (\Exception $e) {
            Log::warning('Moloni connection test failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function getCompanies(): array
    {
        return $this->post('companies/getAll/');
    }
}
