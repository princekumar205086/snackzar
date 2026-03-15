<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class InfobipSmsService
{
    public function sendOtp(string $phone, string $otp): void
    {
        $message = "{$otp} is your Snackzar OTP. It is valid for " . config('snackzar.otp.expiry_minutes', 10) . ' minutes.';

        $this->sendMessage(
            $phone,
            $message,
            null,
            config('snackzar.sms.otp_template_id'),
        );
    }

    public function sendMessage(string $recipient, string $message, ?string $sender = null, ?string $templateId = null): void
    {
        $baseUrl = $this->resolveBaseUrl();
        $apiKey = (string) config('services.infobip.api_key');

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('Infobip SMS is not configured.');
        }

        $payload = [
            'messages' => [[
                'destinations' => [[
                    'to' => $this->normalizeRecipient($recipient),
                ]],
                'sender' => $sender ?: config('services.infobip.sender') ?: 'Snackzar',
                'content' => [
                    'text' => $message,
                ],
            ]],
        ];

        if ($indiaDlt = $this->buildIndiaDltPayload($templateId)) {
            $payload['messages'][0]['regional'] = [
                'indiaDlt' => $indiaDlt,
            ];
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'App ' . $apiKey,
            ])
            ->post($baseUrl . '/sms/3/messages', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Infobip SMS request failed: ' . $response->body());
        }
    }

    private function resolveBaseUrl(): string
    {
        $baseUrl = trim((string) config('services.infobip.base_url'));
        if ($baseUrl === '') {
            return '';
        }

        if (! Str::startsWith($baseUrl, ['http://', 'https://'])) {
            $baseUrl = 'https://' . $baseUrl;
        }

        return rtrim($baseUrl, '/');
    }

    private function normalizeRecipient(string $recipient): string
    {
        $digits = preg_replace('/\D+/', '', $recipient) ?: '';

        if (strlen($digits) === 10) {
            $digits = config('snackzar.sms.default_country_code', '91') . $digits;
        }

        return $digits;
    }

    private function buildIndiaDltPayload(?string $templateId): ?array
    {
        $principalEntityId = config('snackzar.sms.india_dlt_principal_entity_id');
        if (! $principalEntityId) {
            return null;
        }

        $payload = [
            'principalEntityId' => $principalEntityId,
        ];

        if ($templateId) {
            $payload['contentTemplateId'] = $templateId;
        }

        return $payload;
    }
}