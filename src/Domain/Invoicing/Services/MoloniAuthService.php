<?php

namespace Domain\Invoicing\Services;

use Domain\Invoicing\Exceptions\MoloniAuthenticationException;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Invoicing\Models\MoloniToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoloniAuthService
{
    private const TOKEN_REFRESH_BUFFER_SECONDS = 300;

    public function getValidToken(): string
    {
        $token = MoloniToken::first();

        if (! $token) {
            throw new MoloniAuthenticationException(
                'No Moloni token configured. Please authorize the application in Admin settings.'
            );
        }

        if ($token->needsRefresh()) {
            $token = $this->refreshToken($token);
        }

        return $token->access_token;
    }

    public function exchangeCodeForToken(string $code): MoloniToken
    {
        $config = config('invoicing.providers.moloni');
        $startTime = microtime(true);

        try {
            $response = Http::timeout($config['timeout'])
                ->get($config['base_url'] . 'grant/', [
                    'grant_type' => 'authorization_code',
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'code' => $code,
                    'redirect_uri' => $config['redirect_uri'],
                ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                $error = $response->json('error_description') ?? $response->json('error') ?? 'Unknown error';
                Log::error('Moloni OAuth token exchange failed', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                MoloniSyncLog::logFailure('oauth', $error, $response->json(), $durationMs);

                throw new MoloniAuthenticationException('Failed to exchange authorization code: ' . $error);
            }

            $data = $response->json();

            MoloniToken::truncate();

            $token = MoloniToken::create([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'access_token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                'refresh_token_expires_at' => now()->addDays(14),
                'company_id' => null,
            ]);

            MoloniSyncLog::logSuccess('oauth', [
                'token_expires' => $token->access_token_expires_at->toIso8601String(),
            ], $durationMs);

            Log::info('Moloni OAuth token obtained successfully');

            return $token;

        } catch (MoloniAuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            Log::error('Moloni OAuth token exchange error', ['error' => $e->getMessage()]);
            MoloniSyncLog::logFailure('oauth', $e->getMessage(), null, $durationMs);

            throw new MoloniAuthenticationException('Failed to obtain token: ' . $e->getMessage(), 0, $e);
        }
    }

    public function refreshToken(MoloniToken $token): MoloniToken
    {
        if (! $token->isRefreshTokenValid()) {
            throw new MoloniAuthenticationException(
                'Refresh token expired. Please re-authorize the application in Admin settings.'
            );
        }

        $config = config('invoicing.providers.moloni');
        $startTime = microtime(true);

        try {
            $response = Http::timeout($config['timeout'])
                ->get($config['base_url'] . 'grant/', [
                    'grant_type' => 'refresh_token',
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'refresh_token' => $token->refresh_token,
                ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                $error = $response->json('error_description') ?? $response->json('error') ?? 'Unknown error';
                Log::error('Moloni token refresh failed', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                MoloniSyncLog::logFailure('token_refresh', $error, $response->json(), $durationMs);

                throw new MoloniAuthenticationException('Failed to refresh token: ' . $error);
            }

            $data = $response->json();

            $token->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'access_token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                'refresh_token_expires_at' => now()->addDays(14),
            ]);

            MoloniSyncLog::logSuccess('token_refresh', [
                'token_expires' => $token->fresh()->access_token_expires_at->toIso8601String(),
            ], $durationMs);

            Log::info('Moloni token refreshed successfully');

            return $token->fresh();

        } catch (MoloniAuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            Log::error('Moloni token refresh error', ['error' => $e->getMessage()]);
            MoloniSyncLog::logFailure('token_refresh', $e->getMessage(), null, $durationMs);

            throw new MoloniAuthenticationException('Failed to refresh token: ' . $e->getMessage(), 0, $e);
        }
    }

    public function disconnect(): void
    {
        MoloniToken::truncate();

        MoloniSyncLog::logSuccess('disconnect', ['reason' => 'User initiated disconnect']);

        Log::info('Moloni disconnected by user');
    }

    public function getAuthorizationUrl(): string
    {
        $config = config('invoicing.providers.moloni');

        return $config['base_url'] . 'authorize/?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
        ]);
    }
}
