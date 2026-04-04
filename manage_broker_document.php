<?php
// Fetch verification requests instead of broker_documents
$result = mysqli_query($conn, "SELECT * FROM verification_requests WHERE status='pending'");

// Check for errors
if (!$result) {
    echo "Error: " . mysqli_error($conn);
    exit;
}

while ($row = mysqli_fetch_assoc($result)) {
    // display data as needed
    echo "<div>";
    echo "<h3>" . htmlspecialchars($row['full_name']) . "</h3>";
    echo "<p>ID Type: " . htmlspecialchars($row['id_type']) . "</p>";
    echo "<p>Submitted At: " . htmlspecialchars($row['submitted_at']) . "</p>";
    echo "<a href='" . htmlspecialchars($row['id_doc_path']) . "' target='_blank'>View ID Document</a>";
    echo "</div>";
}
?>