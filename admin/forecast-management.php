<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/get_weather.php';

// Check admin authentication
if (!isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/admin-header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fetch_api'])) {
        // Fetch from weather API
        include '../includes/fetch_surf_data.php';
        $message = "Weather data fetched and stored successfully.";
    } elseif (isset($_FILES['csv_file'])) {
        // Handle CSV upload
        $file = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($file, "r")) !== FALSE) {
            $header = fgetcsv($handle); // Skip header row

            while (($data = fgetcsv($handle)) !== FALSE) {
                $location_name = $data[0] ?: 'Bondi Beach';
                // Convert date format from m/d/Y to Y-m-d H:i:s
                $date_obj = DateTime::createFromFormat('n/j/Y G:i', $data[1]);
                if (!$date_obj) {
                    // Try alternate format if first attempt fails
                    $date_obj = DateTime::createFromFormat('m/d/Y H:i', $data[1]);
                }

                if ($date_obj) {
                    $date_time = $date_obj->format('Y-m-d H:i:s');
                } else {
                    continue; // Skip this row if date parsing fails
                }

                $wave_height = floatval($data[2]);
                $wind_speed = intval($data[3]);

                // Calculate conditions
                $conditions = calculateSurfConditions($wave_height, $wind_speed);


                // Delete existing data for this date before inserting
                $delete_stmt = $pdo->prepare("
                    DELETE FROM surf_conditions 
                    WHERE DATE(date_time) = DATE(:date_time)
                    AND location_name = :location_name
                ");

                $delete_stmt->execute([
                    'date_time' => $date_time,
                    'location_name' => $location_name
                ]);


                // Insert into database
                $stmt = $pdo->prepare("
                    INSERT INTO surf_conditions (
                        location_name,
                        date_time,
                        wave_height,
                        wind_speed,
                        beginner_status,
                        intermediate_status,
                        advanced_status,
                        condition_message
                    ) VALUES (
                        'Bondi Beach',
                        :date_time,
                        :wave_height,
                        :wind_speed,
                        :beginner_status,
                        :intermediate_status,
                        :advanced_status,
                        :condition_message
                    )
                ");

                $stmt->execute([
                    'date_time' => $date_time,
                    'wave_height' => $wave_height,
                    'wind_speed' => $wind_speed,
                    'beginner_status' => $conditions['beginner_status'],
                    'intermediate_status' => $conditions['intermediate_status'],
                    'advanced_status' => $conditions['advanced_status'],
                    'condition_message' => $conditions['condition_message']
                ]);
            }
            fclose($handle);
            $message = "CSV data imported successfully.";
        }
    }
}
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white p-4 rounded-4 shadow-sm">
                <div>
                    <h1 class="h2 mb-2 text-primary fw-bold">Forecast Management</h1>
                    <p class="lead text-muted mb-0">Manage surf conditions and forecasts for the next 14 days</p>
                </div>
                <?php if (isset($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Update Data Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 action-card">
                <div class="card-header bg-primary text-white p-4 rounded-top-4">
                    <h4 class="mb-0 d-flex align-items-center">
                        <span class="icon-circle bg-white bg-opacity-25 me-3">
                            <i class="fas fa-cloud-download-alt"></i>
                        </span>
                        Update Forecast Data
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="post" class="mb-4">
                        <div class="action-item mb-4 p-4 rounded-4 bg-light">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-primary text-white me-3">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Automatic Update</h5>
                                    <p class="text-muted mb-0">Fetch latest data from our weather service provider</p>
                                </div>
                            </div>
                            <button type="submit" name="fetch_api" class="btn btn-primary btn-lg w-100 rounded-3">
                                <i class="fas fa-sync-alt me-2"></i>Fetch Latest Data
                            </button>
                        </div>
                    </form>

                    <form method="post" enctype="multipart/form-data">
                        <div class="action-item p-4 rounded-4 bg-light">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-success text-white me-3">
                                    <i class="fas fa-file-csv"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">CSV Import</h5>
                                    <p class="text-muted mb-0">Upload forecast data via CSV file</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="upload-box p-3 rounded-3 bg-white">
                                    <input type="file" class="form-control border-0" id="csv_file" name="csv_file" accept=".csv" required>
                                </div>
                                <div class="form-text mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Format: location_name, date_time, wave_height, wind_speed<br>
                                    Example: Bondi Beach, 2024-03-20 08:00:00, 1.5, 15
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100 rounded-3">
                                <i class="fas fa-upload me-2"></i>Import CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reference Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 reference-card">
                <div class="card-header bg-gradient-info text-white p-4 rounded-top-4">
                    <h4 class="mb-0 d-flex align-items-center">
                        <span class="icon-circle bg-white bg-opacity-25 me-3">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        Surf Conditions Reference
                    </h4>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="skill-box bg-light p-4 rounded-4 h-100">
                                <div class="icon-circle bg-primary text-white mb-3">
                                    <i class="fas fa-child"></i>
                                </div>
                                <h5 class="mb-3">Beginner</h5>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-wave-square text-primary me-2"></i>
                                        0.3m - 1.0m
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <i class="fas fa-wind text-primary me-2"></i>
                                        ≤ 12 km/h
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="skill-box bg-light p-4 rounded-4 h-100">
                                <div class="icon-circle bg-success text-white mb-3">
                                    <i class="fas fa-running"></i>
                                </div>
                                <h5 class="mb-3">Intermediate</h5>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-wave-square text-success me-2"></i>
                                        0.7m - 2.0m
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <i class="fas fa-wind text-success me-2"></i>
                                        ≤ 20 km/h
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="skill-box bg-light p-4 rounded-4 h-100">
                                <div class="icon-circle bg-danger text-white mb-3">
                                    <i class="fas fa-skiing"></i>
                                </div>
                                <h5 class="mb-3">Advanced</h5>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-wave-square text-danger me-2"></i>
                                        1.5m - 5.0m
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <i class="fas fa-wind text-danger me-2"></i>
                                        ≤ 25 km/h
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-top">
                        <h5 class="mb-3">Status Indicators</h5>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="status-box bg-light p-3 rounded-4 text-center">
                                <span class="badge bg-success px-3 py-2 mb-2">Good</span>
                                <small class="d-block text-muted">Ideal conditions</small>
                            </div>
                            <div class="status-box bg-light p-3 rounded-4 text-center">
                                <span class="badge bg-warning px-3 py-2 mb-2">Fair</span>
                                <small class="d-block text-muted">Acceptable</small>
                            </div>
                            <div class="status-box bg-light p-3 rounded-4 text-center">
                                <span class="badge bg-danger px-3 py-2 mb-2">Poor</span>
                                <small class="d-block text-muted">Not recommended</small>
                            </div>
                            <div class="status-box bg-light p-3 rounded-4 text-center">
                                <span class="badge bg-dark px-3 py-2 mb-2">Dangerous</span>
                                <small class="d-block text-muted">Unsafe conditions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<style>
    .text-primary {
        color: #2196F3 !important;
    }

    .bg-primary {
        background: linear-gradient(135deg, #2196F3, #1976D2) !important;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #00BCD4, #0097A7) !important;
    }

    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40, #1a1e21) !important;
    }

    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .action-card,
    .reference-card {
        border-radius: 1rem;
        transition: all 0.3s ease;
    }

    .action-card:hover,
    .reference-card:hover {
        transform: translateY(-5px);
    }

    .action-item {
        transition: all 0.3s ease;
    }

    .action-item:hover {
        transform: translateY(-2px);
    }

    .skill-box,
    .status-box {
        transition: all 0.3s ease;
    }

    .skill-box:hover,
    .status-box:hover {
        transform: translateY(-2px);
        background-color: #f8f9fa !important;
    }

    .upload-box {
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }

    .upload-box:hover {
        border-color: #2196F3;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table td {
        font-size: 0.95rem;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(33, 150, 243, 0.05);
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .badge {
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .icon-circle {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
        }

        .skill-box,
        .status-box {
            margin-bottom: 1rem;
        }
    }

    /* Table Styles */
    .table-responsive {
        min-height: 300px;
        background-color: white;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .table td {
        font-size: 0.95rem;
        vertical-align: middle;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(33, 150, 243, 0.05);
    }

    .badge {
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .btn-group .btn {
        padding: 0.375rem 0.75rem;
    }

    .btn-group .btn:hover {
        transform: translateY(0);
    }
</style>

<script>
    function editForecast(id) {
        window.location.href = `edit-forecast.php?id=${id}`;
    }

    function deleteForecast(id) {
        if (confirm('Are you sure you want to delete this forecast?')) {
            fetch('delete-forecast.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting forecast');
                    }
                });
        }
    }
</script>
<?php include '../includes/footer.php'; ?>