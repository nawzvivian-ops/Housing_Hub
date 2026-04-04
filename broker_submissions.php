<h2>🏢 Broker Property Submissions</h2>
<table>
<tr>
  <th>Broker</th>
  <th>Property</th>
  <th>Submitted At</th>
  <th>Actions</th>
</tr>
<?php while($row = mysqli_fetch_assoc($submissions)): ?>
<tr>
  <td><?= htmlspecialchars($row['broker_name']) ?> (<?= htmlspecialchars($row['broker_email']) ?>)</td>
  <td><?= htmlspecialchars($row['property_name']) ?? '—' ?></td>
  <td><?= htmlspecialchars($row['submitted_at']) ?></td>
  <td>
    <a href="?page=broker_submissions&approve=<?= $row['id'] ?>"
       onclick="return confirm('Approve this submission?')"
       style="background:#86efac;">Approve</a>
    <a href="?page=broker_submissions&reject=<?= $row['id'] ?>"
       onclick="return confirm('Reject this submission?')"
       style="background:#fca5a5;">Reject</a>
  </td>
</tr>
<?php endwhile; ?>
</table>