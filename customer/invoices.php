<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get all invoices for the customer
$query = "SELECT i.*, j.description as job_description, v.make, v.model, v.license_plate 
          FROM invoices i 
          JOIN jobs j ON i.job_id = j.id 
          JOIN vehicles v ON j.vehicle_id = v.id 
          WHERE j.customer_id = :customer_id 
          ORDER BY i.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <!-- Invoices List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">My Invoices</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Vehicle</th>
                            <th>Job Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo $invoice['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($invoice['make'] . ' ' . $invoice['model']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($invoice['license_plate']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($invoice['job_description']); ?></td>
                                <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $invoice['status'] == 'paid' ? 'success' : 
                                            ($invoice['status'] == 'overdue' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($invoice['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></td>
                                <td>
                                    <a href="invoice_details.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($invoice['status'] == 'pending'): ?>
                                        <a href="pay_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-success btn-sm">
                                            <i class="bi bi-credit-card"></i> Pay
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 