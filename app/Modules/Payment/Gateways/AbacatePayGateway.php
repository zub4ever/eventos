<?php

namespace App\Modules\Payment\Gateways;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Modules\Payment\Contracts\IPaymentGateway;
use App\Modules\Payment\DTOs\GatewayTransactionRequestData;
use App\Modules\Payment\DTOs\GatewayTransactionResultData;
use App\Modules\Payment\DTOs\WebhookResultData;
use App\Modules\Payment\Exceptions\PaymentGatewayException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AbacatePayGateway implements IPaymentGateway
{
    public function createTransaction(GatewayTransactionRequestData $data): GatewayTransactionResultData
    {
        $config = config('services.abacatepay');

        try {
            $response = $this->request($config, $data->transaction->id)
                ->post($config['create_charge_path'], [
                    'reference_id' => $data->transaction->id,
                    'booking_id' => $data->booking->id,
                    'amount' => (int) round($data->amount * 100),
                    'payment_method' => 'pix',
                    'expires_at' => $data->expiresAt->toIso8601String(),
                ])
                ->throw();
        } catch (RequestException $exception) {
            Log::warning('AbacatePay request failed', [
                'provider' => 'abacatepay',
                'status' => $exception->response?->status(),
                'body' => $exception->response?->json(),
            ]);

            throw PaymentGatewayException::requestFailed('abacatepay', $exception);
        } catch (Throwable $exception) {
            Log::warning('AbacatePay unexpected failure', [
                'provider' => 'abacatepay',
                'message' => $exception->getMessage(),
            ]);

            throw PaymentGatewayException::requestFailed('abacatepay', $exception);
        }

        $payload = $response->json();
        $map = $config['response_map'];

        return new GatewayTransactionResultData(
            externalId: data_get($payload, $map['external_id']),
            status: $this->mapStatus(data_get($payload, $map['status'])),
            pixQrCode: data_get($payload, $map['pix_qr_code']),
            pixCopyPaste: data_get($payload, $map['pix_copy_paste']),
            boletoUrl: null,
            expiresAt: $this->parseDate(data_get($payload, $map['expires_at'])),
            payload: $payload,
        );
    }

    public function validateWebhook(Request $request): bool
    {
        $config = config('services.abacatepay');
        $expected = (string) ($config['webhook_secret'] ?? '');

        if ($expected === '') {
            return false;
        }

        $headerValue = (string) $request->header($config['webhook_signature_header']);

        return $headerValue !== '' && hash_equals($expected, $headerValue);
    }

    public function processWebhook(array $payload): WebhookResultData
    {
        $config = config('services.abacatepay');
        $map = $config['webhook_map'];
        $eventType = data_get($payload, $map['event_type']);
        $knownEvents = $config['known_webhook_events'];

        return new WebhookResultData(
            externalTransactionId: data_get($payload, $map['external_id']),
            status: $this->mapStatus(data_get($payload, $map['status'])),
            paidAt: $this->parseDate(data_get($payload, $map['paid_at'])),
            eventType: is_string($eventType) ? $eventType : null,
            rawPayload: $payload,
            knownEvent: in_array($eventType, $knownEvents, true),
        );
    }

    public function refundTransaction(Transaction $transaction): array
    {
        return ['refunded' => false, 'transaction_id' => $transaction->id];
    }

    private function request(array $config, string $idempotencyKey): PendingRequest
    {
        $request = Http::acceptJson()
            ->baseUrl($config['base_url'])
            ->withToken($config['api_key'])
            ->timeout($config['timeout'])
            ->retry($config['retry_times'], $config['retry_sleep']);

        if (! empty($config['idempotency_header'])) {
            $request = $request->withHeaders([
                $config['idempotency_header'] => $idempotencyKey,
            ]);
        }

        return $request;
    }

    private function mapStatus(?string $status): TransactionStatus
    {
        return match (strtolower((string) $status)) {
            'paid', 'confirmed' => TransactionStatus::PAID,
            'failed', 'error' => TransactionStatus::FAILED,
            'expired' => TransactionStatus::EXPIRED,
            default => TransactionStatus::PENDING,
        };
    }

    private function parseDate(?string $value): ?CarbonImmutable
    {
        return $value ? CarbonImmutable::parse($value) : null;
    }
}