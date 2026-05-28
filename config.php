<?php
// config.php

// Pesapal API 3.0 live credentials.
// Replace these two values with the live keys from your Pesapal merchant account.
define('PESAPAL_CONSUMER_KEY', 'PASTE_YOUR_PESAPAL_CONSUMER_KEY_HERE');
define('PESAPAL_CONSUMER_SECRET', 'PASTE_YOUR_PESAPAL_CONSUMER_SECRET_HERE');

// Live Pesapal API base URL. Do not change this unless Pesapal changes their API.
define('PESAPAL_BASE_URL', 'https://pay.pesapal.com/v3/api');

// Optional: paste a registered Pesapal IPN ID here.
// If left blank, the app will register the IPN URL automatically when checkout starts.
define('PESAPAL_IPN_ID', '');

// Africa's Talking SMS (optional for now)
define('AFRICASTALKING_USERNAME', 'your_africastalking_username');
define('AFRICASTALKING_API_KEY', 'your_api_key_here');

// Site Configuration
// Leave blank to auto-detect from the live request host, or set to your public HTTPS URL.
define('SITE_URL', '');
define('SITE_NAME', 'HousingHub');
define('SUPPORT_EMAIL', 'support@housinghub.com');
define('SUPPORT_PHONE', '+256741035928');

// Currency
define('CURRENCY', 'UGX');
define('CURRENCY_SYMBOL', 'UGX');

// Environment
define('ENVIRONMENT', 'production');
?>
