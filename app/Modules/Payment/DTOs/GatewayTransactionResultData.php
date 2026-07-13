<?php

namespace App\Modules\Payment\DTOs;

use App\Enums\TransactionStatus;
use Carbon\CarbonImmutable;

readonly class GatewayTransactionResultData
{
    public function __construct(
        public ?string $externalId,
        public TransactionStatus $status,
        public ?string $pixQrCode,
        public ?string $pixCopyPaste,
        public ?string $boletoUrl,
        public ?CarbonImmutable $expiresAt,
        public array $payload,
    ) {
    }
}