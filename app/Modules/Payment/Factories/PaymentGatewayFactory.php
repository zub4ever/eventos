<?php

namespace App\Modules\Payment\Factories;

use App\Modules\Payment\Contracts\IPaymentGateway;
use App\Modules\Payment\Exceptions\InvalidPaymentProviderException;
use App\Modules\Payment\Gateways\AbacatePayGateway;
use App\Modules\Payment\Gateways\AsaasGateway;

class PaymentGatewayFactory
{
    public function __construct(
        private readonly AbacatePayGateway $abacatePayGateway,
        private readonly AsaasGateway $asaasGateway,
    ) {
    }

    public function make(?string $provider = null): IPaymentGateway
    {
        return match ($provider ?? config('services.payment_provider')) {
            'abacatepay' => $this->abacatePayGateway,
            'asaas' => $this->asaasGateway,
            default => throw InvalidPaymentProviderException::unsupported((string) ($provider ?? config('services.payment_provider'))),
        };
    }

    public function providerName(?string $provider = null): string
    {
        return (string) ($provider ?? config('services.payment_provider'));
    }
}