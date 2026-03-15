<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
// Must be logged in as tenant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}
 
$user_id = (int)$_SESSION['user_id'];
 
// ── Fetch tenant + property data ──
$stmt = mysqli_prepare($conn,
    "SELECT tn.id AS tenant_db_id, tn.fullname, tn.email, tn.phone,
            tn.lease_start, tn.lease_end,
            p.property_name, p.address, p.rent_amount, p.landlord_name
     FROM tenants tn
     LEFT JOIN properties p ON tn.property_id = p.id
     WHERE tn.user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$d = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
 
if (!$d) { die("No tenancy record found."); }
 
$tenant_db_id  = (int)$d['tenant_db_id'];
$tenant_name   = $d['fullname']      ?? 'Tenant';
$tenant_email  = $d['email']         ?? '';
$tenant_phone  = $d['phone']         ?? '';
$property      = $d['property_name'] ?? 'N/A';
$address       = $d['address']       ?? 'N/A';
$rent          = number_format($d['rent_amount'] ?? 0);
$landlord      = $d['landlord_name'] ?? 'HousingHub Manager';
$lease_start   = $d['lease_start']   ? date('d M Y', strtotime($d['lease_start'])) : 'N/A';
$lease_end     = $d['lease_end']     ? date('d M Y', strtotime($d['lease_end']))   : 'N/A';
$generated     = date('d M Y, H:i');
$ref           = 'HH-' . date('Y') . '-TEN-' . $user_id;
$filename      = 'Lease_' . preg_replace('/[^a-zA-Z0-9]/', '_', $tenant_name) . '_' . date('Ymd') . '.html';
 
// ── Build the lease HTML ──
$html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Lease Agreement - HousingHub</title>
<style>
  *{box-sizing:border-box}
  body{font-family:Georgia,serif;max-width:750px;margin:40px auto;padding:0 30px;color:#1a1a1a;line-height:1.7}
  .header{text-align:center;border-bottom:3px solid #c8a43c;padding-bottom:20px;margin-bottom:28px}
  .brand{font-size:28px;font-weight:bold;color:#c8a43c;letter-spacing:3px}
  .sub{font-size:14px;color:#666;margin-top:4px}
  .ref{font-size:11px;color:#aaa;margin-top:6px}
  .parties{display:grid;grid-template-columns:1fr 1fr;gap:0;margin:22px 0;border:1px solid #ddd;border-radius:6px;overflow:hidden}
  .party{padding:16px;background:#fafafa}
  .party:first-child{border-right:1px solid #ddd}
  .party-role{font-size:10px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#aaa;margin-bottom:5px}
  .party-name{font-size:16px;font-weight:bold;color:#1a1a1a}
  .party-detail{font-size:12px;color:#777;margin-top:3px}
  .sec{margin-bottom:18px}
  .sec h3{font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#c8a43c;margin-bottom:7px;padding-bottom:4px;border-bottom:1px solid #f5e8c0}
  .sec p{font-size:13px;color:#333}
  .sigs{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px;padding-top:20px;border-top:2px solid #c8a43c}
  .sig-line{border-bottom:1px solid #bbb;height:52px;margin-bottom:8px}
  .sig-name{font-size:12px;color:#666;text-align:center}
  .footer{text-align:center;font-size:11px;color:#bbb;margin-top:36px;padding-top:14px;border-top:1px solid #eee}
  @media print{
    body{margin:0;padding:20px}
    .no-print{display:none}
  }
</style>
</head>
<body>
 
<div class="no-print" style="background:#fff8e1;border:1px solid #ffd54f;border-radius:6px;padding:12px 18px;margin-bottom:24px;font-family:Arial,sans-serif;font-size:13px;color:#5d4037">
  💡 <strong>To save as PDF:</strong> Press <strong>Ctrl+P</strong> (Windows) or <strong>Cmd+P</strong> (Mac) → Change destination to <strong>"Save as PDF"</strong> → Click Save. On mobile, use your browser menu → Print → Save as PDF.
</div>
 
<div class="header">
  <div class="brand">HOUSING HUB</div>
  <div class="sub">Residential Tenancy Agreement</div>
  <div class="ref">Ref: ' . htmlspecialchars($ref) . ' &nbsp;|&nbsp; Generated: ' . htmlspecialchars($generated) . '</div>
</div>
 
<div class="parties">
  <div class="party">
    <div class="party-role">Landlord</div>
    <div class="party-name">' . htmlspecialchars($landlord) . '</div>
    <div class="party-detail">' . htmlspecialchars($address) . '</div>
  </div>
  <div class="party">
    <div class="party-role">Tenant</div>
    <div class="party-name">' . htmlspecialchars($tenant_name) . '</div>
    <div class="party-detail">' . htmlspecialchars($tenant_email) . ' &nbsp;|&nbsp; ' . htmlspecialchars($tenant_phone) . '</div>
  </div>
</div>
 
<div class="sec"><h3>1. Premises</h3><p>The Landlord agrees to let <strong>' . htmlspecialchars($property) . '</strong>, ' . htmlspecialchars($address) . ', Uganda to the Tenant for residential use only.</p></div>
<div class="sec"><h3>2. Term</h3><p>The tenancy commences on <strong>' . htmlspecialchars($lease_start) . '</strong> and continues until <strong>' . htmlspecialchars($lease_end) . '</strong>, after which it converts to a month-to-month tenancy unless renewed in writing by both parties.</p></div>
<div class="sec"><h3>3. Rent</h3><p>The Tenant agrees to pay <strong>UGX ' . htmlspecialchars($rent) . '</strong> per month, due on or before the 1st of each calendar month, via the HousingHub payment portal. Late payments may attract a penalty as determined by management.</p></div>
<div class="sec"><h3>4. Utilities &amp; Maintenance</h3><p>Electricity and water are billed separately and payable by the Tenant. Structural repairs and major maintenance are the Landlord\'s responsibility and shall be reported via the HousingHub maintenance portal.</p></div>
<div class="sec"><h3>5. Tenant Obligations</h3><p>The Tenant shall keep the property clean, report any damage promptly, not sublet the premises without written consent, and comply with all building rules and regulations.</p></div>
<div class="sec"><h3>6. Termination</h3><p>Either party may terminate this agreement with 30 days written notice via the HousingHub portal. Early termination by the Tenant without cause shall forfeit the security deposit.</p></div>
<div class="sec"><h3>7. Governing Law</h3><p>This agreement is governed by the laws of the Republic of Uganda. Any disputes shall be resolved in accordance with applicable Ugandan tenancy legislation.</p></div>
 
<div class="sigs">
  <div>
    <div class="sig-line"></div>
    <div class="sig-name"><strong>' . htmlspecialchars($landlord) . '</strong><br>Landlord &nbsp;|&nbsp; Date: ___________</div>
  </div>
  <div>
    <div class="sig-line"></div>
    <div class="sig-name"><strong>' . htmlspecialchars($tenant_name) . '</strong><br>Tenant &nbsp;|&nbsp; Date: ___________</div>
  </div>
</div>
 
<div class="footer">
  HousingHub &copy; ' . date('Y') . ' &nbsp;|&nbsp; All Rights Reserved &nbsp;|&nbsp; support@housinghuborg.ug
</div>
</body>
</html>';
 
// ── Save file to server (in a documents folder) ──
$docs_dir = __DIR__ . '/tenant_docs/';
if (!is_dir($docs_dir)) {
    mkdir($docs_dir, 0755, true);
}
 
$file_path     = $docs_dir . $filename;
$db_file_path  = 'tenant_docs/' . $filename; // relative path stored in DB
 
file_put_contents($file_path, $html);
 
// ── Save record to tenant_documents table ──
// Only insert if this exact lease file hasn't been saved before for this tenant today
$doc_check = mysqli_query($conn,
    "SELECT id FROM tenant_documents
     WHERE tenant_id = '$tenant_db_id'
     AND document_name = 'Lease Agreement'
     AND DATE(uploaded_at) = CURDATE()
     LIMIT 1");
 
if (!$doc_check || mysqli_num_rows($doc_check) == 0) {
    $ins = mysqli_prepare($conn,
        "INSERT INTO tenant_documents (tenant_id, document_name, file_path, uploaded_at)
         VALUES (?, 'Lease Agreement', ?, NOW())");
    if ($ins) {
        mysqli_stmt_bind_param($ins, "is", $tenant_db_id, $db_file_path);
        mysqli_stmt_execute($ins);
    }
}
 
// ── Send file to tenant's device as download ──
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($html));
header('Cache-Control: no-cache, must-revalidate');
echo $html;
exit();
?>