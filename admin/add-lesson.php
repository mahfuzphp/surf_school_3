<?php
// Include required files before any output
require_once '../includes/functions.php'; // This will now start the session
require_once '../config/database.php';

// Check login and admin status
checkLogin();

// Check if user is admin
if ($_SESSION['user_type'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: /login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $instructor_id = (int)$_POST['instructor_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $errors = [];

    // Validate inputs
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    if ($instructor_id <= 0) {
        $errors[] = "Please select an instructor";
    }

    // Handle image upload
    $lesson_image = null;
    if (!empty($_FILES['lesson_image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['lesson_image']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG and WEBP are allowed";
        } elseif ($_FILES['lesson_image']['size'] > $max_size) {
            $errors[] = "File size too large. Maximum size is 5MB";
        } else {
            $upload_dir = "../uploads/lesson_images/";
            $file_extension = pathinfo($_FILES['lesson_image']['name'], PATHINFO_EXTENSION);
            $lesson_image = uniqid() . '.' . $file_extension;

            if (!move_uploaded_file($_FILES['lesson_image']['tmp_name'], $upload_dir . $lesson_image)) {
                $errors[] = "Failed to upload image";
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO lessons (
                    title, 
                    description, 
                    price, 
                    instructor_id, 
                    level,
                    duration,
                    max_students,
                    lesson_image,
                    is_active,
                    created_at
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $title,
                $description,
                $price,
                $instructor_id,
                $level,
                $duration,
                $max_students,
                $lesson_image,
                $is_active
            ]);

            // Set success message in session
            $_SESSION['success_message'] = "Lesson added successfully!";

            // Redirect to manage-lessons.php
            header("Location: manage-lessons.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();

            // Delete uploaded image if database insert fails
            if ($lesson_image && file_exists("../uploads/lesson_images/" . $lesson_image)) {
                unlink("../uploads/lesson_images/" . $lesson_image);
            }
        }
    }
}

// Get all instructors for the dropdown
$stmt = $pdo->query("SELECT id, username, email FROM users WHERE user_type = 'instructor'");
$instructors = $stmt->fetchAll();
?>

<div class="container mt-5 pt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add New Lesson</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Lesson Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php
                                                                                                                    echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
                                                                                                                    ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price"
                                    value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                    step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="instructor_id" class="form-label">Assign Instructor</label>
                            <select class="form-select" id="instructor_id" name="instructor_id" required>
                                <option value="">Select Instructor</option>
                                <?php foreach ($instructors as $instructor): ?>
                                    <option value="<?php echo $instructor['id']; ?>"
                                        <?php echo (isset($_POST['instructor_id']) && $_POST['instructor_id'] == $instructor['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($instructor['username']); ?>
                                        (<?php echo htmlspecialchars($instructor['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="lesson_image" class="form-label">Lesson Image</label>
                            <input type="file" class="form-control" id="lesson_image" name="lesson_image" accept="image/*">
                            <small class="text-muted">Max file size: 5MB. Allowed types: JPG, PNG, WEBP</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                    <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active Lesson</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Lesson</button>
                            <a href="/admin/manage-lessons.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>