<?php

namespace App\Mail;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class MicrosoftGraphTransport extends AbstractTransport
{
    public function __construct(
        protected string $tenantId,
        protected string $clientId,
        protected string $clientSecret,
        protected string $fromAddress,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $token = $this->getAccessToken();

        /*
         * Envia o e-mail como MIME.
         *
         * Isso preserva HTML, texto alternativo, headers e anexos criados
         * pelo próprio Laravel/Symfony Mailer.
         */
        $mime = $message->getOriginalMessage()->toString();

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'text/plain',
            ])
            ->send(
                'POST',
                sprintf(
                    'https://graph.microsoft.com/v1.0/users/%s/sendMail',
                    rawurlencode($this->fromAddress)
                ),
                [
                    'body' => base64_encode($mime),
                ]
            );

        if (! $response->successful()) {
            throw new TransportException(
                'Microsoft Graph sendMail failed: HTTP '
                . $response->status()
                . ' - '
                . $response->body()
            );
        }
    }

    protected function getAccessToken(): string
    {
        return Cache::remember('ms_graph_mail_token', now()->addMinutes(50), function () {
            $response = Http::asForm()->post(
                "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",
                [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ]
            );

            if (! $response->successful()) {
                throw new TransportException(
                    'Microsoft Graph token request failed: HTTP '
                    . $response->status()
                    . ' - '
                    . $response->body()
                );
            }

            return $response->json('access_token');
        });
    }

    public function __toString(): string
    {
        return 'microsoft-graph';
    }
}