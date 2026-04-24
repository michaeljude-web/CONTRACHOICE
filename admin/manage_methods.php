<?php
require_once '../includes/db_connection.php';
require_once '../includes/admin/auth.php';
requireAdminLogin();

$page_title = 'Manage Contraceptive Methods';
$active_page = 'manage_methods';

$message = '';
$error = '';

$upload_dir = '../uploads/contraceptive_methods/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function handleImageUpload($file, $upload_dir) {
    if (empty($file['name']) || empty($file['tmp_name'])) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('method_', true) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
        return $filename;
    }
    return null;
}

function deleteImage($filename, $upload_dir) {
    if ($filename && file_exists($upload_dir . $filename)) {
        unlink($upload_dir . $filename);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $name = trim($_POST['name']);
            $category = trim($_POST['category']);
            $delivery = trim($_POST['delivery']);
            $effectiveness = floatval($_POST['effectiveness']);
            $cost_level = trim($_POST['cost_level']);
            $is_hormone_free = isset($_POST['is_hormone_free']) ? 1 : 0;
            $suitable_smoker = isset($_POST['suitable_smoker']) ? 1 : 0;
            $suitable_breastfeeding = isset($_POST['suitable_breastfeeding']) ? 1 : 0;
            $contraindications = trim($_POST['contraindications']);
            $description = trim($_POST['description']);
            $side_effects = trim($_POST['side_effects']);
            $how_used = trim($_POST['how_used']);
            $best_for = trim($_POST['best_for']);

            $image_path = null;
            if (!empty($_FILES['method_image']['name'])) {
                $result = handleImageUpload($_FILES['method_image'], $upload_dir);
                if ($result !== null) {
                    $image_path = $result;
                }
            }

            if (!$error) {
                $stmt = $conn->prepare("INSERT INTO contraceptive_methods 
                    (name, category, delivery, effectiveness, cost_level, is_hormone_free, suitable_smoker, suitable_breastfeeding, contraindications, description, side_effects, how_used, best_for, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssdsiiissssss", $name, $category, $delivery, $effectiveness, $cost_level, $is_hormone_free, $suitable_smoker, $suitable_breastfeeding, $contraindications, $description, $side_effects, $how_used, $best_for, $image_path);
                if ($stmt->execute()) {
                    $message = "Method added successfully.";
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        elseif ($action === 'edit') {
            $id = intval($_POST['method_id']);
            $name = trim($_POST['name']);
            $category = trim($_POST['category']);
            $delivery = trim($_POST['delivery']);
            $effectiveness = floatval($_POST['effectiveness']);
            $cost_level = trim($_POST['cost_level']);
            $is_hormone_free = isset($_POST['is_hormone_free']) ? 1 : 0;
            $suitable_smoker = isset($_POST['suitable_smoker']) ? 1 : 0;
            $suitable_breastfeeding = isset($_POST['suitable_breastfeeding']) ? 1 : 0;
            $contraindications = trim($_POST['contraindications']);
            $description = trim($_POST['description']);
            $side_effects = trim($_POST['side_effects']);
            $how_used = trim($_POST['how_used']);
            $best_for = trim($_POST['best_for']);

            $existing = $conn->query("SELECT image_path FROM contraceptive_methods WHERE method_id = $id")->fetch_assoc();
            $image_path = $existing['image_path'];

            if (!empty($_FILES['method_image']['name'])) {
                $result = handleImageUpload($_FILES['method_image'], $upload_dir);
                if ($result !== null) {
                    deleteImage($existing['image_path'], $upload_dir);
                    $image_path = $result;
                }
            }

            if (!$error) {
                $stmt = $conn->prepare("UPDATE contraceptive_methods SET 
                    name=?, category=?, delivery=?, effectiveness=?, cost_level=?, is_hormone_free=?, suitable_smoker=?, suitable_breastfeeding=?, contraindications=?, description=?, side_effects=?, how_used=?, best_for=?, image_path=?
                    WHERE method_id=?");
                $stmt->bind_param("sssdsiiissssssi", $name, $category, $delivery, $effectiveness, $cost_level, $is_hormone_free, $suitable_smoker, $suitable_breastfeeding, $contraindications, $description, $side_effects, $how_used, $best_for, $image_path, $id);
                if ($stmt->execute()) {
                    $message = "Method updated successfully.";
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        elseif ($action === 'delete') {
            $id = intval($_POST['method_id']);
            $existing = $conn->query("SELECT image_path FROM contraceptive_methods WHERE method_id = $id")->fetch_assoc();
            $stmt = $conn->prepare("DELETE FROM contraceptive_methods WHERE method_id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                deleteImage($existing['image_path'], $upload_dir);
                $message = "Method deleted successfully.";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$methods = $conn->query("SELECT * FROM contraceptive_methods ORDER BY method_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Contraceptive Methods</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-5/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-7/css/all.min.css">
    <style>
        :root {
            --bg-dirty: #f8f6f0;
            --surface: #ffffff;
            --border-soft: #e8e4dc;
            --text-primary: #2c2b28;
            --text-secondary: #6b6b67;
            --blue-600: #185FA5;
            --blue-800: #0C447C;
            --red-600: #b91c1c;
        }
        body { 
            background: var(--bg-dirty); 
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
        }
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 24px 28px;
        }
        .topbar {
            background: var(--surface);
            border-radius: 20px;
            padding: 16px 24px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-soft);
        }
        .topbar-left h4 {
            margin: 0;
            font-weight: 500;
        }
        .topbar-left p {
            margin: 0;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 500;
            margin: 0;
        }
        .btn-add {
            background: var(--blue-600);
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            color: white;
            font-weight: 500;
        }
        .card-method {
            background: var(--surface);
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            padding: 16px 24px;
            background: var(--surface);
            border-bottom: 1px solid var(--border-soft);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .card-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .method-thumb {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid var(--border-soft);
            flex-shrink: 0;
        }
        .method-thumb-placeholder {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: #f0ede7;
            border: 1px solid var(--border-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bbb;
            font-size: 20px;
            flex-shrink: 0;
        }
        .card-header h3 { margin: 0; font-size: 18px; font-weight: 500; }
        .btn-edit, .btn-delete {
            background: none;
            border: none;
            margin-left: 10px;
            cursor: pointer;
        }
        .btn-edit { color: var(--blue-600); }
        .btn-delete { color: var(--red-600); }
        .card-body {
            padding: 20px 24px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px,1fr));
            gap: 12px;
        }
        .info-row { font-size: 13px; }
        .info-label { font-weight: 500; color: var(--text-secondary); width: 130px; display: inline-block; }
        .modal-custom .modal-content { border-radius: 20px; }
        .alert-custom { padding: 12px 18px; border-radius: 14px; margin-bottom: 20px; }
        .img-upload-preview {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid var(--border-soft);
            display: none;
            margin-top: 8px;
        }
        .img-upload-placeholder {
            width: 90px;
            height: 90px;
            border-radius: 12px;
            background: #f0ede7;
            border: 2px dashed var(--border-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bbb;
            font-size: 28px;
            margin-top: 8px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../includes/admin/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h4>Manage Contraceptive Methods</h4>
            </div>
        </div>

        <div class="admin-header">
            <h1><i class="fas fa-tablets me-2"></i> Contraceptive Methods</h1>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#methodModal" onclick="resetForm()"><i class="fas fa-plus me-1"></i> Add New Method</button>
        </div>

        <?php if ($message): ?>
            <div class="alert-custom" style="background:#eaf3de; border:1px solid #c8e0b0;"><i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($message) ?></div>
        <?php elseif ($error): ?>
            <div class="alert-custom" style="background:#fcebeb; border:1px solid #f0c0c0;"><i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($methods->num_rows === 0): ?>
            <div class="text-center p-5 bg-white rounded-3">No methods yet. Click "Add New Method" to start.</div>
        <?php else: ?>
            <?php while($row = $methods->fetch_assoc()): ?>
            <div class="card-method">
                <div class="card-header">
                    <div class="card-header-left">
                        <?php if (!empty($row['image_path'])): ?>
                            <img src="../uploads/contraceptive_methods/<?= htmlspecialchars($row['image_path']) ?>" class="method-thumb" alt="<?= htmlspecialchars($row['name']) ?>">
                        <?php else: ?>
                            <div class="method-thumb-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                    </div>
                    <div>
                        <button class="btn-edit" onclick="editMethod(<?= htmlspecialchars(json_encode($row)) ?>)"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn-delete" onclick="confirmDelete(<?= $row['method_id'] ?>, '<?= addslashes($row['name']) ?>')"><i class="fas fa-trash-alt"></i> Delete</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="info-row"><span class="info-label">Category:</span> <?= htmlspecialchars($row['category']) ?></div>
                    <div class="info-row"><span class="info-label">Delivery:</span> <?= htmlspecialchars($row['delivery']) ?></div>
                    <div class="info-row"><span class="info-label">Effectiveness:</span> <?= $row['effectiveness'] ?>%</div>
                    <div class="info-row"><span class="info-label">Cost level:</span> <?= ucfirst($row['cost_level']) ?></div>
                    <div class="info-row"><span class="info-label">Hormone-free:</span> <?= $row['is_hormone_free'] ? 'Yes' : 'No' ?></div>
                    <div class="info-row"><span class="info-label">Suitable for smokers:</span> <?= $row['suitable_smoker'] ? 'Yes' : 'No' ?></div>
                    <div class="info-row"><span class="info-label">Suitable breastfeeding:</span> <?= $row['suitable_breastfeeding'] ? 'Yes' : 'No' ?></div>
                    <div class="info-row"><span class="info-label">Contraindications:</span> <?= htmlspecialchars($row['contraindications'] ?: 'None') ?></div>
                    <div class="info-row"><span class="info-label">Description:</span> <?= nl2br(htmlspecialchars(substr($row['description'],0,100))) ?>...</div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade modal-custom" id="methodModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="max-height:92vh; display:flex; flex-direction:column;">
            <div class="modal-header" style="flex-shrink:0;">
                <h5 class="modal-title" id="modalTitle">Add New Contraceptive Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="methodForm" enctype="multipart/form-data" style="display:flex; flex-direction:column; flex:1; min-height:0; overflow:hidden;">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="method_id" id="method_id">
                <div class="modal-body" style="overflow-y:auto; flex:1;">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Method Photo</label>
                            <div>
                                <div class="img-upload-placeholder" id="imgPlaceholder" onclick="document.getElementById('method_image').click()">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <img id="imgPreview" class="img-upload-preview" alt="Preview">
                            </div>
                            <input type="file" name="method_image" id="method_image" accept="image/*" class="d-none">
                            <div class="form-text">Any image format (JPG, PNG, GIF, WEBP, BMP...). Leave blank to keep existing photo.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="hormonal">Hormonal</option>
                                <option value="barrier">Barrier</option>
                                <option value="long_term">Long-acting</option>
                                <option value="natural">Natural</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Delivery *</label>
                            <input type="text" name="delivery" id="delivery" class="form-control" placeholder="e.g., daily_pill, injection, implant" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effectiveness (%) *</label>
                            <input type="number" step="0.1" name="effectiveness" id="effectiveness" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost level *</label>
                            <select name="cost_level" id="cost_level" class="form-select">
                                <option value="low">Low (₱)</option>
                                <option value="medium">Medium (₱₱)</option>
                                <option value="high">High (₱₱₱)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_hormone_free" id="is_hormone_free" class="form-check-input" value="1">
                                <label class="form-check-label">Hormone-free</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="suitable_smoker" id="suitable_smoker" class="form-check-input" value="1">
                                <label class="form-check-label">Suitable for smokers</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="suitable_breastfeeding" id="suitable_breastfeeding" class="form-check-input" value="1">
                                <label class="form-check-label">Suitable while breastfeeding</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Contraindications (comma separated)</label>
                            <input type="text" name="contraindications" id="contraindications" class="form-control" placeholder="e.g., hypertension, migraine, blood_clots">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description *</label>
                            <textarea name="description" id="description" rows="2" class="form-control" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Side effects</label>
                            <textarea name="side_effects" id="side_effects" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">How it's used</label>
                            <textarea name="how_used" id="how_used" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Best for</label>
                            <textarea name="best_for" id="best_for" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="flex-shrink:0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteName"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="method_id" id="deleteId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/vendor/bootstrap-5/js/bootstrap.bundle.min.js"></script>
<script>
    const imgInput = document.getElementById('method_image');
    const imgPreview = document.getElementById('imgPreview');
    const imgPlaceholder = document.getElementById('imgPlaceholder');

    imgInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
                imgPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    function resetForm() {
        document.getElementById('formAction').value = 'add';
        document.getElementById('modalTitle').innerText = 'Add New Contraceptive Method';
        document.getElementById('methodForm').reset();
        document.getElementById('method_id').value = '';
        imgPreview.style.display = 'none';
        imgPreview.src = '';
        imgPlaceholder.style.display = 'flex';
    }

    function editMethod(method) {
        document.getElementById('formAction').value = 'edit';
        document.getElementById('modalTitle').innerText = 'Edit Method';
        document.getElementById('method_id').value = method.method_id;
        document.getElementById('name').value = method.name;
        document.getElementById('category').value = method.category;
        document.getElementById('delivery').value = method.delivery;
        document.getElementById('effectiveness').value = method.effectiveness;
        document.getElementById('cost_level').value = method.cost_level;
        document.getElementById('is_hormone_free').checked = method.is_hormone_free == 1;
        document.getElementById('suitable_smoker').checked = method.suitable_smoker == 1;
        document.getElementById('suitable_breastfeeding').checked = method.suitable_breastfeeding == 1;
        document.getElementById('contraindications').value = method.contraindications || '';
        document.getElementById('description').value = method.description || '';
        document.getElementById('side_effects').value = method.side_effects || '';
        document.getElementById('how_used').value = method.how_used || '';
        document.getElementById('best_for').value = method.best_for || '';

        if (method.image_path) {
            imgPreview.src = '../uploads/contraceptive_methods/' + method.image_path;
            imgPreview.style.display = 'block';
            imgPlaceholder.style.display = 'none';
        } else {
            imgPreview.style.display = 'none';
            imgPreview.src = '';
            imgPlaceholder.style.display = 'flex';
        }

        new bootstrap.Modal(document.getElementById('methodModal')).show();
    }

    function confirmDelete(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('deleteId').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
</body>
</html>