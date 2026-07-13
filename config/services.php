<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'payment_provider' => env('PAYMENT_PROVIDER', 'abacatepay'),

    'abacatepay' => [
        'base_url' => env('ABACATEPAY_API_URL', ''),
        'api_key' => env('ABACATEPAY_API_KEY', ''),
        'webhook_secret' => env('ABACATEPAY_WEBHOOK_SECRET', ''),
        'webhook_signature_header' => env('ABACATEPAY_WEBHOOK_SIGNATURE_HEADER', 'X-AbacatePay-Token'),
        'timeout' => (int) env('ABACATEPAY_TIMEOUT', 10),
        'retry_times' => (int) env('ABACATEPAY_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('ABACATEPAY_RETRY_SLEEP', 200),
        'create_charge_path' => env('ABACATEPAY_CREATE_CHARGE_PATH', '/charges'),
        'idempotency_header' => env('ABACATEPAY_IDEMPOTENCY_HEADER', 'Idempotency-Key'),
        'response_map' => [
            'external_id' => env('ABACATEPAY_RESPONSE_EXTERNAL_ID', 'id'),
            'status' => env('ABACATEPAY_RESPONSE_STATUS', 'status'),
            'pix_qr_code' => env('ABACATEPAY_RESPONSE_PIX_QR_CODE', 'pix.qr_code'),
            'pix_copy_paste' => env('ABACATEPAY_RESPONSE_PIX_COPY_PASTE', 'pix.copy_paste'),
            'expires_at' => env('ABACATEPAY_RESPONSE_EXPIRES_AT', 'expires_at'),
        ],
        'webhook_map' => [
            'external_id' => env('ABACATEPAY_WEBHOOK_EXTERNAL_ID', 'id'),
            'status' => env('ABACATEPAY_WEBHOOK_STATUS', 'status'),
            'paid_at' => env('ABACATEPAY_WEBHOOK_PAID_AT', 'paid_at'),
            'event_type' => env('ABACATEPAY_WEBHOOK_EVENT_TYPE', 'event'),
        ],
        'known_webhook_events' => [
            'charge.paid',
            'charge.failed',
            'charge.expired',
        ],
    ],

    'asaas' => [
        'base_url' => env('ASAAS_API_URL', ''),
        'api_key' => env('ASAAS_API_KEY', ''),
        'webhook_secret' => env('ASAAS_WEBHOOK_TOKEN', ''),
        'webhook_signature_header' => env('ASAAS_WEBHOOK_SIGNATURE_HEADER', 'asaas-access-token'),
        'timeout' => (int) env('ASAAS_TIMEOUT', 10),
        'retry_times' => (int) env('ASAAS_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('ASAAS_RETRY_SLEEP', 200),
        'create_charge_path' => env('ASAAS_CREATE_CHARGE_PATH', '/payments'),
        'idempotency_header' => env('ASAAS_IDEMPOTENCY_HEADER', 'Idempotency-Key'),
        'response_map' => [
            'external_id' => env('ASAAS_RESPONSE_EXTERNAL_ID', 'id'),
            'status' => env('ASAAS_RESPONSE_STATUS', 'status'),
            'pix_qr_code' => env('ASAAS_RESPONSE_PIX_QR_CODE', 'pix.qrCode'),
            'pix_copy_paste' => env('ASAAS_RESPONSE_PIX_COPY_PASTE', 'pix.payload'),
            'boleto_url' => env('ASAAS_RESPONSE_BOLETO_URL', 'bankSlipUrl'),
            'expires_at' => env('ASAAS_RESPONSE_EXPIRES_AT', 'dueDate'),
        ],
        'webhook_map' => [
            'external_id' => env('ASAAS_WEBHOOK_EXTERNAL_ID', 'payment.id'),
            'status' => env('ASAAS_WEBHOOK_STATUS', 'payment.status'),
            'paid_at' => env('ASAAS_WEBHOOK_PAID_AT', 'payment.paymentDate'),
            'event_type' => env('ASAAS_WEBHOOK_EVENT_TYPE', 'event'),
        ],
        'known_webhook_events' => [
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED',
            'PAYMENT_OVERDUE',
            'PAYMENT_DELETED',
        ],
    ],

];
