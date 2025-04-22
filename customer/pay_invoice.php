<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

$invoice_id = $_GET['id'] ?? 0;

if (!$invoice_id) {
    header('Location: invoices.php');
    exit;
}

// Get invoice details
$query = "SELECT i.*, j.description as job_description, v.make, v.model, v.license_plate 
          FROM invoices i 
          JOIN jobs j ON i.job_id = j.id 
          JOIN vehicles v ON j.vehicle_id = v.id 
          WHERE i.id = :invoice_id AND j.customer_id = :customer_id AND i.status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bindParam(':invoice_id', $invoice_id);
$stmt->bindParam(':customer_id', $_SESSION['user_id']);
$stmt->execute();
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    header('Location: invoices.php');
    exit;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_number = $_POST['card_number'] ?? '';
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';
    
    if (empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
        $error = 'Please fill in all required fields';
    } else {
        // In a real application, you would integrate with a payment gateway here
        // For this example, we'll just update the invoice status
        
        $query = "UPDATE invoices SET status = 'paid', payment_date = NOW() WHERE id = :invoice_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':invoice_id', $invoice_id);
        
        if ($stmt->execute()) {
            $message = 'Payment successful!';
            // Redirect to invoice details after 2 seconds
            header("refresh:2;url=invoice_details.php?id=" . $invoice_id);
        } else {
            $error = 'Error processing payment';
        }
    }
}
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pay Invoice</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">Invoice Details</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Invoice #</th>
                            <td><?php echo $invoice['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Vehicle</th>
                            <td>
                                <?php echo htmlspecialchars($invoice['make'] . ' ' . $invoice['model']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($invoice['license_plate']); ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th>Job Description</th>
                            <td><?php echo htmlspecialchars($invoice['job_description']); ?></td>
                        </tr>
                        <tr>
                            <th>Amount Due</th>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5 class="mb-3">Payment Information</h5>
                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="card_number">Card Number *</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                   placeholder="1234 5678 9012 3456" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="card_expiry">Expiry Date *</label>
                                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" 
                                           placeholder="MM/YY" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="card_cvv">CVV *</label>
                                    <input type="text" class="form-control" id="card_cvv" name="card_cvv" 
                                           placeholder="123" required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-credit-card"></i> Pay $<?php echo number_format($invoice['amount'], 2); ?>
                        </button>
                        
                        <a href="invoice_details.php?id=<?php echo $invoice_id; ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 