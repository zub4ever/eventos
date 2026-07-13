<?php

namespace App\Modules\Payment\Contracts;

use App\Models\Transaction;
use App\Modules\Payment\DTOs\GatewayTransactionRequestData;
use App\Modules\Payment\DTOs\GatewayTransactionResultData;
use App\Modules\Payment\DTOs\WebhookResultData;
use Illuminate\Http\Request;

interface IPaymentGateway
{
    public function createTransaction(GatewayTransactionRequestData $data): GatewayTransactionResultData;

    public function validateWebhook(Request $request): bool;

    public function processWebhook(array $payload): WebhookResultData;

    public function refundTransaction(Transaction $transaction): array;
}