<?php

namespace App\Enums;

enum TransactionPaymentMethod: string
{
    case PIX = 'pix';
    case CREDIT_CARD = 'credit_card';
    case BOLETO = 'boleto';
}
