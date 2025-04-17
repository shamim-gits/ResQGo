<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if (!is_driver()) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $vehicle_id = sanitize_input($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id = ? AND driver_id = ?");
    $stmt->bind_param("ii", $vehicle_id, $_SESSION['driver_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehicle = $result->fetch_assoc();
    
    if (!$vehicle) {
        $_SESSION['error'] = "Vehicle not found or does not belong to you";
        header("Location: ../driver/vehicle-details.php");
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Vehicle deleted successfully!";
        header("Location: ../driver/vehicle-details.php");
        exit;
    } else {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header("Location: ../driver/vehicle-details.php");
        exit;
    }
} else {
    header("Location: ../driver/vehicle-details.php");
    exit;
}
?>
