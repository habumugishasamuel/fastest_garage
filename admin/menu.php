<?php
$pageTitle = "Manage Menu";

require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add' || $action == 'edit') {
        $title = $_POST['title'] ?? '';
        $url = $_POST['url'] ?? '';
        $parent_id = $_POST['parent_id'] ? (int)$_POST['parent_id'] : null;
        $position = (int)($_POST['position'] ?? 0);
        
        if (empty($title) || empty($url)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($action == 'add') {
                $query = "INSERT INTO menu_items (title, url, parent_id, position) VALUES (:title, :url, :parent_id, :position)";
            } else {
                $id = (int)$_POST['id'];
                $query = "UPDATE menu_items SET title = :title, url = :url, parent_id = :parent_id, position = :position WHERE id = :id";
            }
            
            try {
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':url', $url);
                $stmt->bindParam(':parent_id', $parent_id);
                $stmt->bindParam(':position', $position);
                
                if ($action == 'edit') {
                    $stmt->bindParam(':id', $id);
                }
                
                $stmt->execute();
                $message = 'Menu item ' . ($action == 'add' ? 'added' : 'updated') . ' successfully';
            } catch (PDOException $e) {
                $error = 'Error ' . ($action == 'add' ? 'adding' : 'updating') . ' menu item: ' . $e->getMessage();
            }
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];
        
        try {
            // First update any child items to have no parent
            $stmt = $conn->prepare("UPDATE menu_items SET parent_id = NULL WHERE parent_id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Then delete the menu item
            $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = 'Menu item deleted successfully';
        } catch (PDOException $e) {
            $error = 'Error deleting menu item: ' . $e->getMessage();
        }
    }
}

// Get menu item for editing
$edit_item = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_item = $stmt->fetch();
}

// Get all menu items
$menu_items = [];
try {
    $stmt = $conn->query("SELECT * FROM menu_items ORDER BY parent_id, position, title");
    $menu_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching menu items: ' . $e->getMessage();
}

// Function to build nested menu array
function buildMenuTree($items, $parentId = null) {
    $branch = [];
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $children = buildMenuTree($items, $item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $branch[] = $item;
        }
    }
    return $branch;
}

// Function to generate options for select
function generateMenuOptions($items, $selected = null, $parent_id = null, $level = 0) {
    $html = '';
    foreach ($items as $item) {
        if ($item['parent_id'] == $parent_id) {
            $html .= sprintf(
                '<option value="%d" %s>%s%s</option>',
                $item['id'],
                ($selected == $item['id'] ? 'selected' : ''),
                str_repeat('— ', $level),
                htmlspecialchars($item['title'])
            );
            $html .= generateMenuOptions($items, $selected, $item['id'], $level + 1);
        }
    }
    return $html;
}

require_once 'layout.php';
?>

<!-- Add/Edit Menu Item Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <?php echo $edit_item ? 'Edit Menu Item' : 'Add New Menu Item'; ?>
        </h6>
        <?php if ($edit_item): ?>
            <a href="menu.php" class="btn btn-sm btn-secondary">
                <i class="bi bi-plus-circle"></i> Add New Item
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add'; ?>">
            <?php if ($edit_item): ?>
                <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo $edit_item ? htmlspecialchars($edit_item['title']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="url" class="form-label">URL *</label>
                        <input type="text" class="form-control" id="url" name="url" 
                               value="<?php echo $edit_item ? htmlspecialchars($edit_item['url']) : ''; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Item</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">None</option>
                            <?php 
                            if ($edit_item) {
                                echo generateMenuOptions($menu_items, $edit_item['parent_id']);
                            } else {
                                echo generateMenuOptions($menu_items);
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <input type="number" class="form-control" id="position" name="position" 
                               value="<?php echo $edit_item ? $edit_item['position'] : '0'; ?>" min="0">
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    <?php echo $edit_item ? 'Update Menu Item' : 'Add Menu Item'; ?>
                </button>
                
                <?php if ($edit_item): ?>
                    <a href="menu.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Menu Items List -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Menu Structure</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Position</th>
                        <th>Parent</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $item): ?>
                        <tr>
                            <td>
                                <?php 
                                if ($item['parent_id']) {
                                    echo '— ';
                                }
                                echo htmlspecialchars($item['title']); 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['url']); ?></td>
                            <td><?php echo $item['position']; ?></td>
                            <td>
                                <?php
                                if ($item['parent_id']) {
                                    foreach ($menu_items as $parent) {
                                        if ($parent['id'] == $item['parent_id']) {
                                            echo htmlspecialchars($parent['title']);
                                            break;
                                        }
                                    }
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="menu.php?edit=<?php echo $item['id']; ?>" 
                                       class="btn btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="if(confirm('Are you sure you want to delete this menu item?')) {
                                                document.getElementById('delete-form-<?php echo $item['id']; ?>').submit();
                                            }"
                                            data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-form-<?php echo $item['id']; ?>" method="POST" action="" style="display: none;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout_end.php'; ?> 