<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/tenant_activation_helper.php';

function hh_app_base_url() {
    $configured = defined('SITE_URL') ? trim((string)SITE_URL) : '';
    if ($configured !== '' && stripos($configured, 'your-domain') === false) {
        return rtrim($configured, '/');
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return rtrim($configured, '/');
    }

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $scheme = $is_https ? 'https' : 'http';
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script_dir === '/' || $script_dir === '\\' || $script_dir === '.') {
        $script_dir = '';
    }

    return $scheme . '://' . $host . $script_dir;
}

function hh_app_url($path, $params = []) {
    $base = hh_app_base_url();
    $url = rtrim($base, '/') . '/' . ltrim($path, '/');

    if (!empty($params)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
    }

    return $url;
}

function pesapal_ensure_configured() {
    $key = defined('PESAPAL_CONSUMER_KEY') ? trim((string)PESAPAL_CONSUMER_KEY) : '';
    $secret = defined('PESAPAL_CONSUMER_SECRET') ? trim((string)PESAPAL_CONSUMER_SECRET) : '';

    if ($key === '' || $secret === ''
        || $key === 'PASTE_YOUR_PESAPAL_CONSUMER_KEY_HERE'
        || $secret === 'PASTE_YOUR_PESAPAL_CONSUMER_SECRET_HERE') {
        throw new Exception('Pesapal live keys are missing. Add PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET in config.php.');
    }

    if (!function_exists('curl_init')) {
        throw new Exception('PHP cURL is required for Pesapal payments.');
    }
}

function pesapal_request($method, $path, $payload = null, $token = null) {
    pesapal_ensure_configured();

    $base = defined('PESAPAL_BASE_URL') ? rtrim((string)PESAPAL_BASE_URL, '/') : 'https://pay.pesapal.com/v3/api';
    $url = preg_match('/^https?:\/\//i', $path) ? $path : $base . '/' . ltrim($path, '/');

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload ?: []));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    }

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        throw new Exception('Pesapal request failed: ' . $curl_error);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new Exception('Pesapal returned an invalid response.');
    }

    $api_error = $decoded['error'] ?? null;
    if (is_array($api_error) && (!empty($api_error['message']) || !empty($api_error['code']))) {
        throw new Exception('Pesapal error: ' . ($api_error['message'] ?? $api_error['code']));
    }

    if ($http_code >= 400) {
        throw new Exception('Pesapal HTTP error ' . $http_code . ': ' . ($decoded['message'] ?? 'Request failed'));
    }

    return $decoded;
}

function pesapal_get_token() {
    $response = pesapal_request('POST', 'Auth/RequestToken', [
        'consumer_key' => PESAPAL_CONSUMER_KEY,
        'consumer_secret' => PESAPAL_CONSUMER_SECRET,
    ]);

    if (empty($response['token'])) {
        throw new Exception('Pesapal did not return an access token.');
    }

    return $response['token'];
}

function pesapal_get_notification_id($token) {
    $configured = defined('PESAPAL_IPN_ID') ? trim((string)PESAPAL_IPN_ID) : '';
    if ($configured !== '') {
        return $configured;
    }

    $ipn_url = hh_app_url('pesapal_ipn.php');
    if (stripos($ipn_url, 'localhost') !== false || stripos($ipn_url, '127.0.0.1') !== false) {
        throw new Exception('Pesapal IPN requires a public URL. Set SITE_URL in config.php after deploying.');
    }

    $registered_ipns = pesapal_request('GET', 'URLSetup/GetIpnList', null, $token);
    if (is_array($registered_ipns)) {
        foreach ($registered_ipns as $ipn) {
            if (!is_array($ipn) || empty($ipn['ipn_id']) || empty($ipn['url'])) {
                continue;
            }

            if (rtrim((string)$ipn['url'], '/') === rtrim($ipn_url, '/')) {
                return $ipn['ipn_id'];
            }
        }
    }

    $response = pesapal_request('POST', 'URLSetup/RegisterIPN', [
        'url' => $ipn_url,
        'ipn_notification_type' => 'GET',
    ], $token);

    if (empty($response['ipn_id'])) {
        throw new Exception('Pesapal did not return an IPN ID.');
    }

    return $response['ipn_id'];
}

function pesapal_submit_order($token, $payload) {
    $response = pesapal_request('POST', 'Transactions/SubmitOrderRequest', $payload, $token);

    if (empty($response['redirect_url']) || empty($response['order_tracking_id'])) {
        throw new Exception('Pesapal did not return a checkout redirect URL.');
    }

    return $response;
}

function pesapal_get_transaction_status($token, $order_tracking_id) {
    $path = 'Transactions/GetTransactionStatus?orderTrackingId=' . urlencode($order_tracking_id);
    return pesapal_request('GET', $path, null, $token);
}

function pesapal_local_status($status_description, $status_code = null) {
    $description = strtoupper(trim((string)$status_description));
    $code = is_numeric($status_code) ? intval($status_code) : null;

    if ($code === 1 || $description === 'COMPLETED') {
        return 'paid';
    }

    if (in_array($code, [0, 2, 3], true) || in_array($description, ['INVALID', 'FAILED', 'REVERSED'], true)) {
        return 'failed';
    }

    return 'pending';
}

function hh_merge_payment_response($conn, $payment_id, $merge, $status = null) {
    $payment_id = intval($payment_id);

    $stmt = $conn->prepare("SELECT payment_response FROM payments WHERE id = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $existing = hh_decode_payment_response($current['payment_response'] ?? '');
    $payload = array_replace_recursive($existing, $merge);
    $json = json_encode($payload);

    if ($status !== null) {
        $upd = $conn->prepare("UPDATE payments SET status = ?, payment_response = ?, updated_at = NOW() WHERE id = ?");
        if (!$upd) {
            return false;
        }
        $upd->bind_param("ssi", $status, $json, $payment_id);
    } else {
        $upd = $conn->prepare("UPDATE payments SET payment_response = ?, updated_at = NOW() WHERE id = ?");
        if (!$upd) {
            return false;
        }
        $upd->bind_param("si", $json, $payment_id);
    }

    return $upd->execute();
}

function pesapal_payment_by_reference($conn, $merchant_reference) {
    $stmt = $conn->prepare("SELECT * FROM payments WHERE transaction_ref = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $merchant_reference);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();

    return $payment ?: null;
}

function pesapal_payment_by_id($conn, $payment_id) {
    $payment_id = intval($payment_id);
    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();

    return $payment ?: null;
}

function pesapal_apply_status_to_payment($conn, $payment_id, $order_tracking_id, $status_response) {
    $payment = pesapal_payment_by_id($conn, $payment_id);
    if (!$payment) {
        return 'failed';
    }

    $was_paid = strtolower((string)$payment['status']) === 'paid';
    $local_status = pesapal_local_status(
        $status_response['payment_status_description'] ?? '',
        $status_response['status_code'] ?? null
    );

    hh_merge_payment_response($conn, $payment_id, [
        'provider' => 'pesapal',
        'pesapal_order_tracking_id' => $order_tracking_id,
        'pesapal_status' => $status_response,
        'verified_at' => date('c'),
    ], $local_status);

    if ($local_status === 'paid' && !$was_paid) {
        require_once __DIR__ . '/tenant_activation_helper.php';
        require_once __DIR__ . '/payment_receipt_helper.php';

        hh_activate_tenant_for_paid_payment($conn, $payment_id);
        send_payment_receipt_email($conn, $payment_id);
    }

    return $local_status;
}

function pesapal_notification_input() {
    $input = array_merge($_GET, $_POST);
    if (!empty($input)) {
        return $input;
    }

    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
}

?>
