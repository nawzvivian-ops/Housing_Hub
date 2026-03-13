<?php
// config.php

// Flutterwave API Credentials (Get from https://dashboard.flutterwave.com/settings/apis)
// For testing, you can use these sandbox keys temporarily:
define('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-SANDBOXDEMOKEY-X');
define('FLUTTERWAVE_SECRET_KEY', 'FLWSECK_TEST-SANDBOXDEMOKEY-X');
define('FLUTTERWAVE_ENCRYPTION_KEY', 'FLWSECK_TEST-SANDBOXDEMOKEY-X');

// Africa's Talking SMS (optional for now)
define('AFRICASTALKING_USERNAME', 'sandbox');
define('AFRICASTALKING_API_KEY', 'your_api_key_here');

// Site Configuration
define('SITE_URL', 'http://localhost/housinghub');
define('SITE_NAME', 'HousingHub');
define('SUPPORT_EMAIL', 'support@housinghub.com');
define('SUPPORT_PHONE', '+256700000000');

// Currency
define('CURRENCY', 'UGX');
define('CURRENCY_SYMBOL', 'UGX');

// Environment
define('ENVIRONMENT', 'sandbox');
?>