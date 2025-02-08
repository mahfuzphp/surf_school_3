<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

// Check login and user type
checkLogin();

if ($_SESSION['user_type'] !== 'instructor') {
    $_SESSION['error_message'] = "Access denied. Instructor privileges required.";
    header("Location: /login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $level = $_POST['level'];
        $duration = (int)$_POST['duration'];
        $max_students = (int)$_POST['max_students'];
        
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
        if (empty($level)) {
            $errors[] = "Level is required";
        }
        if ($duration <= 0) {
            $errors[] = "Duration must be greater than 0";
        }
        if ($max_students <= 0) {
            $errors[] = "Maximum students must be greater than 0";
        }

        // Handle lesson image upload
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
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['lesson_image']['name'], PATHINFO_EXTENSION);
                $lesson_image = uniqid() . '.' . $file_extension;

                if (!move_uploaded_file($_FILES['lesson_image']['tmp_name'], $upload_dir . $lesson_image)) {
                    $errors[] = "Failed to upload image";
                }
            }
        }

        if (empty($errors)) {
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
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $title,
                $description,
                $price,
                $_SESSION['user_id'],
                $level,
                $duration,
                $max_students,
                $lesson_image
            ]);

            $_SESSION['success_message'] = "Lesson created successfully!";
            header("Location: /instructor/my-lessons.php");
            exit();
        } else {
            $_SESSION['error_message'] = $errors;
            // Delete uploaded image if there were errors
            if ($lesson_image && file_exists("../uploads/lesson_images/" . $lesson_image)) {
                unlink("../uploads/lesson_images/" . $lesson_image);
            }
        }
    } catch (PDOException $e) {
        error_log("Error creating lesson: " . $e->getMessage());
        $_SESSION['error_message'] = "Error creating lesson. Please try again.";
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add New Lesson</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            if (is_array($_SESSION['error_message'])) {
                                echo '<ul class="mb-0">';
                                foreach ($_SESSION['error_message'] as $error) {
                                    echo '<li>' . htmlspecialchars($error) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo htmlspecialchars($_SESSION['error_message']);
                            }
                            unset($_SESSION['error_message']);
                            ?>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price ($)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                    value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">Level</label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="">Select Level</option>
                                    <option value="Beginner" <?php echo (isset($_POST['level']) && $_POST['level'] === 'Beginner') ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="Intermediate" <?php echo (isset($_POST['level']) && $_POST['level'] === 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="Advanced" <?php echo (isset($_POST['level']) && $_POST['level'] === 'Advanced') ? 'selected' : ''; ?>>Advanced</option>
                                    <option value="All Levels" <?php echo (isset($_POST['level']) && $_POST['level'] === 'All Levels') ? 'selected' : ''; ?>>All Levels</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" min="30" step="30"
                                    value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : '60'; ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_students" class="form-label">Maximum Students</label>
                                <input type="number" class="form-control" id="max_students" name="max_students" min="1"
                                    value="<?php echo isset($_POST['max_students']) ? htmlspecialchars($_POST['max_students']) : '5'; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="lesson_image" class="form-label">Lesson Image</label>
                            <input type="file" class="form-control" id="lesson_image" name="lesson_image" accept="image/*">
                            <small class="text-muted">Max file size: 5MB. Allowed types: JPG, PNG, WEBP</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Lesson</button>
                            <a href="/instructor/my-lessons.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 