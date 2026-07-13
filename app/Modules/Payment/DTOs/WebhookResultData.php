<?php

namespace App\Modules\Payment\DTOs;

use App\Enums\TransactionStatus;
use Carbon\CarbonImmutable;

readonly class WebhookResultData
{
    public function __construct(
        public ?string $externalTransactionId,
        public ?TransactionStatus $status,
        public ?CarbonImmutable $paidAt,
        public ?string $eventType,
        public array $rawPayload,
        public bool $knownEvent = true,
    ) {
    }
}