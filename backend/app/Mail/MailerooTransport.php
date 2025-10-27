<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class MailerooTransport extends AbstractTransport
{
    protected $apiKey;
    protected $apiUrl = 'https://smtp.maileroo.com/api/v2/emails';

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        \Log::info('🚀 MailerooTransport::doSend - Iniciando envío de email');

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0];
        $to = $email->getTo()[0];

        $payload = [
            'from' => [
                'address' => $from->getAddress(),
                'display_name' => $from->getName() ?? config('mail.from.name'),
            ],
            'to' => [
                'address' => $to->getAddress(),
                'display_name' => $to->getName() ?? '',
            ],
            'subject' => $email->getSubject(),
        ];

        // Add HTML body if exists
        if ($email->getHtmlBody()) {
            $payload['html'] = $email->getHtmlBody();
        }

        // Add plain text body if exists
        if ($email->getTextBody()) {
            $payload['plain'] = $email->getTextBody();
        }

        \Log::info('📤 Enviando a Maileroo API', [
            'to' => $payload['to']['address'],
            'from' => $payload['from']['address'],
            'subject' => $payload['subject'],
            'api_url' => $this->apiUrl,
            'has_api_key' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey ?? '')
        ]);

        // Send request to Maileroo API
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, $payload);

        \Log::info('📨 Respuesta de Maileroo API', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->body()
        ]);

        if (!$response->successful()) {
            \Log::error('❌ Maileroo API falló', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Maileroo API Error: ' . $response->body());
        }

        \Log::info('✅ Email enviado exitosamente por Maileroo');
    }

    public function __toString(): string
    {
        return 'maileroo';
    }
}
