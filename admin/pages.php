<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO pages (title, slug, content, meta_description, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['slug'],
                    $_POST['content'],
                    $_POST['meta_description'],
                    $_POST['status'],
                    $_SESSION['user_id']
                ]);
                break;

            case 'edit':
                $stmt = $conn->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, meta_description = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['slug'],
                    $_POST['content'],
                    $_POST['meta_description'],
                    $_POST['status'],
                    $_POST['id']
                ]);
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM pages WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
        }
        header("Location: pages.php");
        exit();
    }
}

// Get all pages
$stmt = $conn->query("SELECT * FROM pages ORDER BY created_at DESC");
$pages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages - Simple CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Simple CMS</a>
            <div class="navbar-nav">
                <a class="nav-link" href="../index.php">Back to Site</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h2>Manage Pages</h2>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPageModal">
                    Add New Page
                </button>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page): ?>
                                <tr>
                                    <td><?php echo $page['title']; ?></td>
                                    <td><?php echo $page['slug']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $page['status'] === 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($page['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($page['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-page" 
                                                data-id="<?php echo $page['id']; ?>"
                                                data-title="<?php echo $page['title']; ?>"
                                                data-slug="<?php echo $page['slug']; ?>"
                                                data-content="<?php echo htmlspecialchars($page['content']); ?>"
                                                data-meta="<?php echo $page['meta_description']; ?>"
                                                data-status="<?php echo $page['status']; ?>">
                                            Edit
                                        </button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $page['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Page Modal -->
    <div class="modal fade" id="addPageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Page</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta Description</label>
                            <input type="text" class="form-control" name="meta_description">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Page</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Page Modal -->
    <div class="modal fade" id="editPageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Page</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="edit-title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" id="edit-slug" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" id="edit-content" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta Description</label>
                            <input type="text" class="form-control" name="meta_description" id="edit-meta">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit-status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.edit-page').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit-id').value = this.dataset.id;
                document.getElementById('edit-title').value = this.dataset.title;
                document.getElementById('edit-slug').value = this.dataset.slug;
                document.getElementById('edit-content').value = this.dataset.content;
                document.getElementById('edit-meta').value = this.dataset.meta;
                document.getElementById('edit-status').value = this.dataset.status;
                
                new bootstrap.Modal(document.getElementById('editPageModal')).show();
            });
        });
    </script>
</body>
</html> 