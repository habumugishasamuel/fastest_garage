<?php
$pageTitle = "Settings";

require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Create settings table if it doesn't exist
try {
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);

    // Insert default settings if they don't exist
    $default_settings = [
        ['site_name', 'Simple CMS', 'The name of your website'],
        ['site_description', 'A simple content management system', 'A brief description of your website'],
        ['contact_email', 'admin@example.com', 'Primary contact email address'],
        ['items_per_page', '10', 'Number of items to display per page'],
        ['maintenance_mode', '0', 'Enable maintenance mode (0 = off, 1 = on)']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, setting_description) VALUES (?, ?, ?)");
    foreach ($default_settings as $setting) {
        $stmt->execute($setting);
    }
} catch (PDOException $e) {
    $error = "Error setting up settings table: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        $message = "Settings updated successfully";
    } catch (PDOException $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// Get all settings
$stmt = $conn->query("SELECT * FROM settings ORDER BY setting_key");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'layout.php';
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Site Settings</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <?php foreach ($settings as $setting): ?>
                <div class="mb-3">
                    <label for="<?php echo htmlspecialchars($setting['setting_key']); ?>" class="form-label">
                        <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                    </label>
                    <?php if ($setting['setting_key'] == 'maintenance_mode'): ?>
                        <select class="form-select" id="<?php echo htmlspecialchars($setting['setting_key']); ?>" 
                                name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]">
                            <option value="0" <?php echo $setting['setting_value'] == '0' ? 'selected' : ''; ?>>Off</option>
                            <option value="1" <?php echo $setting['setting_value'] == '1' ? 'selected' : ''; ?>>On</option>
                        </select>
                    <?php elseif ($setting['setting_key'] == 'items_per_page'): ?>
                        <input type="number" class="form-control" id="<?php echo htmlspecialchars($setting['setting_key']); ?>" 
                               name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]"
                               value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                               min="1" max="100">
                    <?php else: ?>
                        <input type="text" class="form-control" id="<?php echo htmlspecialchars($setting['setting_key']); ?>" 
                               name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]"
                               value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                    <?php endif; ?>
                    <div class="form-text text-muted">
                        <?php echo htmlspecialchars($setting['setting_description']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Settings
            </button>
        </form>
    </div>
</div>

<?php require_once 'layout_end.php'; ?> 