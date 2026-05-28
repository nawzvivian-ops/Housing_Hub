<?php

function hh_decode_payment_response($raw) {
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function hh_payment_action_from_response($payment) {
    $response = hh_decode_payment_response($payment['payment_response'] ?? '');
    $action = $response['action'] ?? 'rent';

    return in_array($action, ['rent', 'buy', 'lease'], true) ? $action : 'rent';
}

function hh_activate_tenant_for_paid_payment($conn, $payment_id) {
    $payment_id = intval($payment_id);
    if ($payment_id <= 0) {
        return false;
    }

    $stmt = $conn->prepare("
        SELECT p.*, u.fullname, u.email, u.phone, u.role
        FROM payments p
        JOIN users u ON p.tenant_id = u.id
        WHERE p.id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();

    if (!$payment || strtolower((string)$payment['status']) !== 'paid') {
        return false;
    }

    $tenant_user_id = intval($payment['tenant_id']);
    $property_id = intval($payment['property_id']);
    $fullname = (string)($payment['fullname'] ?? '');
    $email = (string)($payment['email'] ?? '');
    $phone = (string)($payment['phone'] ?? '');
    $action = hh_payment_action_from_response($payment);
    $lease_start = date('Y-m-d');

    $chk = $conn->prepare("SELECT id FROM tenants WHERE user_id = ? LIMIT 1");
    if (!$chk) {
        return false;
    }

    $chk->bind_param("i", $tenant_user_id);
    $chk->execute();
    $existing_tenant = $chk->get_result()->fetch_assoc();

    if (!$existing_tenant) {
        if ($action === 'buy') {
            $ins = $conn->prepare("
                INSERT INTO tenants
                    (fullname, email, phone, property_id, user_id,
                     lease_start, lease_end, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NULL, 'Active', NOW())
            ");
            if (!$ins) {
                return false;
            }

            $ins->bind_param(
                "sssiis",
                $fullname,
                $email,
                $phone,
                $property_id,
                $tenant_user_id,
                $lease_start
            );
        } else {
            $lease_end = ($action === 'lease')
                ? date('Y-m-d', strtotime('+1 year'))
                : date('Y-m-d', strtotime('+1 month'));

            $ins = $conn->prepare("
                INSERT INTO tenants
                    (fullname, email, phone, property_id, user_id,
                     lease_start, lease_end, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
            ");
            if (!$ins) {
                return false;
            }

            $ins->bind_param(
                "sssiiss",
                $fullname,
                $email,
                $phone,
                $property_id,
                $tenant_user_id,
                $lease_start,
                $lease_end
            );
        }

        if (!$ins->execute()) {
            return false;
        }
    } else {
        $upd = $conn->prepare("UPDATE tenants SET property_id = ?, status = 'Active' WHERE user_id = ?");
        if (!$upd) {
            return false;
        }

        $upd->bind_param("ii", $property_id, $tenant_user_id);
        if (!$upd->execute()) {
            return false;
        }
    }

    $role = $conn->prepare("
        UPDATE users
        SET role = 'tenant'
        WHERE id = ?
          AND role NOT IN ('admin','staff','broker','propertyowner')
    ");
    if ($role) {
        $role->bind_param("i", $tenant_user_id);
        $role->execute();
    }

    return true;
}

?>
