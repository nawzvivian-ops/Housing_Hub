<?php
require_once __DIR__ . '/send_mail.php';

function hh_pdf_escape($text) {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string)$text);
}

function hh_pdf_text(&$content, $x, $y, $size, $text, $r = 0.08, $g = 0.11, $b = 0.18) {
    $safe = hh_pdf_escape($text);
    $content .= sprintf("BT /F1 %d Tf %.3f %.3f %.3f rg %d %d Td (%s) Tj ET\n", $size, $r, $g, $b, $x, $y, $safe);
}

function hh_money($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

function hh_payment_receipt_pdf($payment) {
    $receiptNo = 'HH-' . str_pad((string)$payment['id'], 6, '0', STR_PAD_LEFT);
    $date = !empty($payment['date']) ? date('d M Y, H:i', strtotime($payment['date'])) : date('d M Y, H:i');
    $issued = date('d M Y, H:i');

    $content = "";
    $content .= "0.045 0.071 0.196 rg 0 720 612 72 re f\n";
    $content .= "0.784 0.643 0.235 rg 0 702 612 6 re f\n";
    hh_pdf_text($content, 54, 752, 24, 'HOUSINGHUB', 1, 1, 1);
    hh_pdf_text($content, 54, 733, 10, 'Your Property, Our Priority', 0.92, 0.82, 0.48);
    hh_pdf_text($content, 410, 748, 13, 'PAYMENT RECEIPT', 1, 1, 1);
    hh_pdf_text($content, 410, 730, 10, $receiptNo, 0.92, 0.82, 0.48);

    $content .= "0.965 0.972 0.984 rg 38 94 536 590 re f\n";
    $content .= "0.784 0.643 0.235 RG 38 94 536 590 re S\n";
    hh_pdf_text($content, 58, 650, 16, 'Payment Confirmed', 0.045, 0.071, 0.196);
    hh_pdf_text($content, 58, 628, 10, 'This receipt confirms that HousingHub has verified the payment below.', 0.28, 0.31, 0.38);

    $content .= "0.045 0.071 0.196 rg 58 555 496 48 re f\n";
    hh_pdf_text($content, 76, 584, 10, 'AMOUNT PAID', 0.92, 0.82, 0.48);
    hh_pdf_text($content, 76, 564, 22, hh_money($payment['amount']), 1, 1, 1);

    $rows = [
        ['Tenant', $payment['fullname'] ?? 'N/A'],
        ['Email', $payment['email'] ?? 'N/A'],
        ['Property', $payment['property_name'] ?? 'N/A'],
        ['Payment Method', ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? 'N/A'))],
        ['Transaction Ref', $payment['transaction_ref'] ?? 'N/A'],
        ['Payment Date', $date],
        ['Status', strtoupper($payment['status'] ?? 'PAID')],
        ['Issued On', $issued],
    ];

    $y = 512;
    foreach ($rows as $row) {
        $content .= "0.88 0.9 0.94 RG 58 " . ($y - 10) . " 496 1 re S\n";
        hh_pdf_text($content, 58, $y, 10, $row[0], 0.38, 0.42, 0.5);
        hh_pdf_text($content, 220, $y, 11, substr((string)$row[1], 0, 52), 0.08, 0.11, 0.18);
        $y -= 36;
    }

    $content .= "0.95 0.90 0.72 rg 58 140 496 70 re f\n";
    hh_pdf_text($content, 76, 182, 11, 'Thank you for using HousingHub.', 0.045, 0.071, 0.196);
    hh_pdf_text($content, 76, 162, 9, 'Keep this receipt for your records. For questions, contact HousingHub Support.', 0.2, 0.23, 0.3);
    hh_pdf_text($content, 58, 68, 8, 'Generated electronically by HousingHub. No signature is required.', 0.45, 0.48, 0.55);

    $objects = [];
    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>";
    $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n$content\nendstream";
    $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $i => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($i + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    return $pdf;
}

function send_payment_receipt_email($conn, $payment_id) {
    $stmt = $conn->prepare("
        SELECT p.*, u.fullname, u.email, pr.property_name, pr.address
        FROM payments p
        LEFT JOIN users u ON p.tenant_id = u.id
        LEFT JOIN properties pr ON p.property_id = pr.id
        WHERE p.id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    if (!$payment || empty($payment['email'])) {
        return false;
    }

    $payment['status'] = $payment['status'] ?: 'paid';
    $receiptNo = 'HH-' . str_pad((string)$payment['id'], 6, '0', STR_PAD_LEFT);
    $pdf = hh_payment_receipt_pdf($payment);
    $name = $payment['fullname'] ?: 'Tenant';
    $property = $payment['property_name'] ?: 'your property';

    $body = "
        <div style='font-family:Arial,sans-serif;color:#111827;line-height:1.6'>
            <h2 style='color:#0b145a;margin-bottom:8px'>Payment Receipt - HousingHub</h2>
            <p>Dear " . htmlspecialchars($name) . ",</p>
            <p>Your payment for <strong>" . htmlspecialchars($property) . "</strong> has been approved and verified.</p>
            <p><strong>Amount:</strong> " . htmlspecialchars(hh_money($payment['amount'])) . "<br>
               <strong>Receipt:</strong> " . htmlspecialchars($receiptNo) . "<br>
               <strong>Reference:</strong> " . htmlspecialchars($payment['transaction_ref'] ?? 'N/A') . "</p>
            <p>Your official PDF receipt is attached to this email.</p>
            <p style='color:#6b7280;font-size:13px'>HousingHub - Your Property, Our Priority</p>
        </div>
    ";

    $sent = send_mail(
        $payment['email'],
        "HousingHub Payment Receipt $receiptNo",
        $body,
        true,
        [[
            'data' => $pdf,
            'name' => "HousingHub-Receipt-$receiptNo.pdf",
            'type' => 'application/pdf',
        ]]
    );

    if ($sent) {
        $recipient = mysqli_real_escape_string($conn, $payment['email']);
        $content = mysqli_real_escape_string($conn, "Receipt $receiptNo emailed for " . hh_money($payment['amount']));
        mysqli_query($conn, "INSERT INTO payment_receipts (payment_id, sent_at, method, recipient_email, receipt_content) VALUES ($payment_id, NOW(), 'email_pdf', '$recipient', '$content')");
    }

    return $sent;
}
?>
