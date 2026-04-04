<?php
// Replace your existing code that fetches broker documents with this:

$result = mysqli_query($conn, "SELECT * FROM verification_requests WHERE status='pending'");

// Check for errors
if (!$result) {
    echo "Error: " . mysqli_error($conn);
    exit;
}

// Loop through and display data
while ($row = mysqli_fetch_assoc($result)) {
    // Example: display fields
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['id_type']) . "</td>";
    echo "<td><a href='" . htmlspecialchars($row['id_doc_path']) . "' target='_blank'>View Document</a></td>";
    echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "</tr>";
}
?>