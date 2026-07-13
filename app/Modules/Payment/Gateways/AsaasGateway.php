<?php

namespace App\Modules\Payment\Gateways;

use App\Enums\TransactionPaymentMethod;
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

class AsaasGateway implements IPaymentGateway
{
    public function createTransaction(GatewayTransactionRequestData $data): GatewayTransactionResultData
    {
        $config = config('services.asaas');

        try {
            $response = $this->request($config, $data->transaction->id)
                ->post($config['create_charge_path'], [
                    'externalReference' => $data->transaction->id,
                    'description' => 'Booking '.$data->booking->id,
                    'billingType' => $this->mapPaymentMethod($data->paymentMethod),
                    'value' => $data->amount,
                    'dueDate' => $data->expiresAt->toDateString(),
                ])
                ->throw();
        } catch (RequestException $exception) {
            Log::warning('Asaas request failed', [
                'provider' => 'asaas',
                'status' => $exception->response?->status(),
                'body' => $exception->response?->json(),
            ]);

            throw PaymentGatewayException::requestFailed('asaas', $exception);
        } catch (Throwable $exception) {
            Log::warning('Asaas unexpected failure', [
                'provider' => 'asaas',
                'message' => $exception->getMessage(),
            ]);

            throw PaymentGatewayException::requestFailed('asaas', $exception);
        }

        $payload = $response->json();
        $map = $config['response_map'];

        return new GatewayTransactionResultData(
            externalId: data_get($payload, $map['external_id']),
            status: $this->mapStatus(data_get($payload, $map['status'])),
            pixQrCode: data_get($payload, $map['pix_qr_code']),
            pixCopyPaste: data_get($payload, $map['pix_copy_paste']),
            boletoUrl: data_get($payload, $map['boleto_url']),
            expiresAt: $this->parseDate(data_get($payload, $map['expires_at'])),
            payload: $payload,
        );
    }

    public function validateWebhook(Request $request): bool
    {
        $config = config('services.asaas');
        $expected = (string) ($config['webhook_secret'] ?? '');

        if ($expected === '') {
            return false;
        }

        $headerValue = (string) $request->header($config['webhook_signature_header']);

        return $headerValue !== '' && hash_equals($expected, $headerValue);
    }

    public function processWebhook(array $payload): WebhookResultData
    {
        $config = config('services.asaas');
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

    private function mapPaymentMethod(TransactionPaymentMethod $paymentMethod): string
    {
        return match ($paymentMethod) {
            TransactionPaymentMethod::PIX => 'PIX',
            TransactionPaymentMethod::BOLETO => 'BOLETO',
            TransactionPaymentMethod::CREDIT_CARD => 'CREDIT_CARD',
        };
    }

    private function mapStatus(?string $status): TransactionStatus
    {
        return match (strtolower((string) $status)) {
            'received', 'confirmed', 'paid' => TransactionStatus::PAID,
            'overdue', 'expired' => TransactionStatus::EXPIRED,
            'refunded' => TransactionStatus::REFUNDED,
            'failed', 'error' => TransactionStatus::FAILED,
            default => TransactionStatus::PENDING,
        };
    }

    private function parseDate(?string $value): ?CarbonImmutable
    {
        return $value ? CarbonImmutable::parse($value) : null;
    }
}