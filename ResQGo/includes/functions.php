<?php
function start_session_if_not_started() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    start_session_if_not_started();
    return isset($_SESSION['user_id']);
}

function is_driver() {
    start_session_if_not_started();
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'driver';
}

function is_customer() {
    start_session_if_not_started();
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'customer';
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: ../index.php");
        exit;
    }
}

function redirect_by_user_type() {
    start_session_if_not_started();
    if (is_driver()) {
        header("Location: ../driver/ride-requests.php");
        exit;
    } elseif (is_customer()) {
        header("Location: ../customer/request-ride.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

function calculate_fare($emergency_type, $distance) {
    $base_fare = 30; 
    
    $emergency_rates = [
        'cardiac' => 20,
        'accident' => 25,
        'pregnancy' => 15,
        'other_medical' => 10
    ];
    
    // rate per mile
    $rate_per_mile = 2.5;
    
    // calculate total fare
    $total_fare = $base_fare + $emergency_rates[$emergency_type] + ($distance * $rate_per_mile);
    
    return round($total_fare, 2);
}
?>