<?php
// Only start session if one hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Please login to continue.";
        header("Location: /login.php");
        exit();
    }
}

function uploadProfileImage($file)
{
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    // Add file upload logic here
}

function dd($data)
{
    echo '<div style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        padding: 20px;
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        z-index: 9999;
        font-family: monospace;
        font-size: 14px;
    ">';

    echo '<strong style="color: #dc3545;">Debug Output:</strong><br><br>';

    if ($data === false) {
        echo '<span style="color: red;">false</span>';
    } elseif ($data === true) {
        echo '<span style="color: green;">true</span>';
    } elseif ($data === null) {
        echo '<span style="color: gray;">null</span>';
    } elseif (empty($data)) {
        echo '<span style="color: orange;">empty</span>';
    } else {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    echo '<br><small style="color: #6c757d;">Script execution stopped</small>';
    echo '</div>';
    exit;
}

// Debug function without exit
function dump($data)
{
    echo '<div style="
        margin: 20px;
        padding: 15px;
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        font-family: monospace;
        font-size: 14px;
    ">';

    if ($data === false) {
        echo '<span style="color: red;">false</span>';
    } elseif ($data === true) {
        echo '<span style="color: green;">true</span>';
    } elseif ($data === null) {
        echo '<span style="color: gray;">null</span>';
    } elseif (empty($data)) {
        echo '<span style="color: orange;">empty</span>';
    } else {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    echo '</div>';
}

// Error handler with redirect
function handleError($message, $redirect_url)
{
    $_SESSION['error_message'] = $message;
    header("Location: " . $redirect_url);
    exit();
}

// Success handler with redirect
function handleSuccess($message, $redirect_url)
{
    $_SESSION['success_message'] = $message;
    header("Location: " . $redirect_url);
    exit();
}
