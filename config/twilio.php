<?php

return [
    'sid'            => env('TWILIO_SID', ''),
    'auth_token'     => env('TWILIO_AUTH_TOKEN', ''),
    'phone_number'   => env('TWILIO_PHONE_NUMBER', ''),
    'whatsapp_number' => env('TWILIO_WHATSAPP_NUMBER', ''),
    'webhook_url'    => env('TWILIO_WEBHOOK_URL', ''),
];
