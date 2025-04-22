<?php
require_once '../includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get staff's assigned jobs
$query = "SELECT j.*, u.name as customer_name, v.make, v.model 
          FROM jobs j 
          JOIN users u ON j.customer_id = u.id 
          JOIN vehicles v ON j.vehicle_id = v.id 
          WHERE j.staff_id = :staff_id 
          ORDER BY j.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':staff_id', $_SESSION['user_id']);
$stmt->execute();
$assigned_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get today's appointments
$today = date('Y-m-d');
$query = "SELECT a.*, u.name as customer_name, v.make, v.model, s.name as service_name 
          FROM appointments a 
          JOIN users u ON a.customer_id = u.id 
          JOIN vehicles v ON a.vehicle_id = v.id 
          JOIN services s ON a.service_id = s.id 
          WHERE DATE(a.appointment_date) = :today 
          AND a.status = 'scheduled' 
          ORDER BY a.appointment_date ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':today', $today);
$stmt->execute();
$today_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <!-- Assigned Jobs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">My Assigned Jobs</h6>
            <a href="jobs.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> View All Jobs
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assigned_jobs as $job): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($job['make'] . ' ' . $job['model']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $job['status'] == 'completed' ? 'success' : 
                                            ($job['status'] == 'in_progress' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                <td>
                                    <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Today's Appointments -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Today's Appointments</h6>
            <a href="appointments.php" class="btn btn-primary btn-sm">
                <i class="bi bi-calendar"></i> View All Appointments
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($today_appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['make'] . ' ' . $appointment['model']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $appointment['status'] == 'completed' ? 'success' : 
                                            ($appointment['status'] == 'cancelled' ? 'danger' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="jobs.php?action=new" class="btn btn-primary btn-block">
                                <i class="bi bi-plus-circle"></i> New Job
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="appointments.php?action=new" class="btn btn-success btn-block">
                                <i class="bi bi-calendar-plus"></i> New Appointment
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="jobs.php?status=in_progress" class="btn btn-warning btn-block">
                                <i class="bi bi-wrench"></i> Active Jobs
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="appointments.php?view=today" class="btn btn-info btn-block">
                                <i class="bi bi-calendar-check"></i> Today's Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 