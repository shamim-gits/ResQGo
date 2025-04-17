<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if (!is_customer()) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pickup_location = sanitize_input($_POST['pickup_location']);
    $destination = sanitize_input($_POST['destination']);
    $emergency_type = sanitize_input($_POST['emergency_type']);
    
    if (empty($pickup_location) || empty($destination) || empty($emergency_type)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../customer/request-ride.php");
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO rides (customer_id, pickup_location, destination, emergency_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $pickup_location, $destination, $emergency_type);
        $stmt->execute();
        
        $ride_id = $conn->insert_id;
        
        $_SESSION['success'] = "Ride request submitted successfully! Redirecting to fare selection...";
        
        header("Location: ../customer/fare-selection.php?ride_id=" . $ride_id);
        exit;
        
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../customer/request-ride.php");
        exit;
    }
} else {
    header("Location: ../customer/request-ride.php");
    exit;
}
?>
