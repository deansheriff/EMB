<?php
declare(strict_types=1);

final class PaystackClient
{
    private const API_BASE = 'https://api.paystack.co';

    public function __construct(private readonly string $secretKey)
    {
        if (trim($secretKey) === '') {
            throw new RuntimeException('Paystack is not fully configured.');
        }
    }

    public static function configured(): bool
    {
        return (string) setting('paystack_enabled', '0') === '1'
            && trim((string) setting('paystack_secret_key')) !== '';
    }

    public static function fromSettings(): self
    {
        return new self((string) setting('paystack_secret_key'));
    }

    public function initializeTransaction(array $payload): array
    {
        return $this->request('POST', '/transaction/initialize', $payload);
    }

    public function verifyTransaction(string $reference): array
    {
        return $this->request('GET', '/transaction/verify/' . rawurlencode($reference));
    }

    public static function validWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) setting('paystack_secret_key');
        if ($secret === '' || $signature === '') {
            return false;
        }
        return hash_equals(hash_hmac('sha512', $payload, $secret), $signature);
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $curl = curl_init(self::API_BASE . $path);
        if ($curl === false) {
            throw new RuntimeException('Unable to start the payment connection.');
        }
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Accept: application/json',
            'Content-Type: application/json',
            'Cache-Control: no-cache',
        ];
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CUSTOMREQUEST => $method,
        ]);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        }

        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($body === false || $error !== '') {
            throw new RuntimeException('The payment provider could not be reached. Please try again.');
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('The payment provider returned an invalid response.');
        }
        if ($status < 200 || $status >= 300 || empty($decoded['status'])) {
            $message = trim((string) ($decoded['message'] ?? 'The payment request was not accepted.'));
            throw new RuntimeException($message);
        }
        return $decoded;
    }
}
