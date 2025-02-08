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

// Get lesson ID from URL
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify lesson exists and belongs to instructor
$stmt = $pdo->prepare("
    SELECT * FROM lessons 
    WHERE id = ? AND instructor_id = ?
");
$stmt->execute([$lesson_id, $_SESSION['user_id']]);
$lesson = $stmt->fetch();

if (!$lesson) {
    $_SESSION['error_message'] = "Lesson not found or access denied.";
    header("Location: /instructor/my-lessons.php");
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
        $lesson_image = $lesson['lesson_image']; // Keep existing image by default
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
                $new_image = uniqid() . '.' . $file_extension;

                if (move_uploaded_file($_FILES['lesson_image']['tmp_name'], $upload_dir . $new_image)) {
                    // Delete old image if exists
                    if ($lesson['lesson_image'] && file_exists($upload_dir . $lesson['lesson_image'])) {
                        unlink($upload_dir . $lesson['lesson_image']);
                    }
                    $lesson_image = $new_image;
                } else {
                    $errors[] = "Failed to upload image";
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE lessons SET 
                    title = ?,
                    description = ?,
                    price = ?,
                    level = ?,
                    duration = ?,
                    max_students = ?,
                    lesson_image = ?
                WHERE id = ? AND instructor_id = ?
            ");

            $stmt->execute([
                $title,
                $description,
                $price,
                $level,
                $duration,
                $max_students,
                $lesson_image,
                $lesson_id,
                $_SESSION['user_id']
            ]);

            $_SESSION['success_message'] = "Lesson updated successfully!";
            header("Location: /instructor/my-lessons.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error updating lesson: " . $e->getMessage());
        $errors[] = "Error updating lesson. Please try again.";
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
                    <h4 class="mb-0">Edit Lesson</h4>
                </div>
                <div class="card-body p-4">
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
                                value="<?php echo htmlspecialchars($lesson['title']); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php 
                                echo htmlspecialchars($lesson['description']); 
                            ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price ($)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                    value="<?php echo htmlspecialchars($lesson['price']); ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">Level</label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="">Select Level</option>
                                    <?php
                                    $levels = ['Beginner', 'Intermediate', 'Advanced', 'All Levels'];
                                    foreach ($levels as $lvl):
                                    ?>
                                        <option value="<?php echo $lvl; ?>" <?php echo $lesson['level'] === $lvl ? 'selected' : ''; ?>>
                                            <?php echo $lvl; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" min="30" step="30"
                                    value="<?php echo htmlspecialchars($lesson['duration']); ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_students" class="form-label">Maximum Students</label>
                                <input type="number" class="form-control" id="max_students" name="max_students" min="1"
                                    value="<?php echo htmlspecialchars($lesson['max_students']); ?>"
                                    required>
                            </div>
                        </div>

                        <?php if ($lesson['lesson_image']): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div>
                                    <img src="/uploads/lesson_images/<?php echo htmlspecialchars($lesson['lesson_image']); ?>"
                                        alt="Lesson Image" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="lesson_image" class="form-label">Change Image</label>
                            <input type="file" class="form-control" id="lesson_image" name="lesson_image" accept="image/*">
                            <small class="text-muted">Max file size: 5MB. Allowed types: JPG, PNG, WEBP</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Lesson</button>
                            <a href="/instructor/my-lessons.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 