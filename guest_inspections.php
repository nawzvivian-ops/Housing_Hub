<?php
include "db_connect.php";

$property_id = $_POST['property_id'] ?? 0;

// Fetch completed inspections only
$inspectionsQ = mysqli_query($conn, "
    SELECT i.*, t.fullname AS tenant_name 
    FROM inspections i
    LEFT JOIN tenants t ON i.tenant_id = t.id
    WHERE i.property_id='$property_id' AND i.status='Completed'
    ORDER BY i.inspection_date DESC
");
?>

<h2>Property Inspections</h2>
<table border="1" cellpadding="5">
<tr>
    <th>#</th>
    <th>Tenant</th>
    <th>Inspector</th>
    <th>Date</th>
    <th>Condition</th>
    <th>Notes</th>
</tr>
<?php $i=1; while($ins=mysqli_fetch_assoc($inspectionsQ)): ?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($ins['tenant_name'] ?? '-') ?></td>
    <td><?= htmlspecialchars($ins['inspector_name']) ?></td>
    <td><?= htmlspecialchars($ins['inspection_date']) ?></td>
    <td><?= htmlspecialchars($ins['condition']) ?></td>
    <td><?= htmlspecialchars($ins['notes']) ?></td>
</tr>
<?php endwhile; ?>
</table>