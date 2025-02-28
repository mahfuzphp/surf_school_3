<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';
require_once 'includes/fetch_surf_data.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['error_message'] = "You must be logged in as an admin to update forecast data.";
    header("Location: /login.php");
    exit();
}

// Call the function to fetch and store surf data
$result = fetchAndStoreSurfData();

if ($result) {
    $_SESSION['success_message'] = "Forecast data has been successfully updated with 14 days of data.";
} else {
    $_SESSION['error_message'] = "There was an error updating the forecast data. Please check the error logs.";
}

// Redirect back to the forecast management page
header("Location: /admin/forecast-management.php");
exit();
