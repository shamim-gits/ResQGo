<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if (!is_driver()) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_type = sanitize_input($_POST['vehicle_type']);
    $registration_number = sanitize_input($_POST['registration_number']);
    $model = sanitize_input($_POST['model']);
    $year = sanitize_input($_POST['year']);
    
    if (empty($vehicle_type) || empty($registration_number) || empty($model) || empty($year)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../driver/vehicle-details.php");
        exit;
    }
    
    $valid_types = ['ambulance', 'paramedic'];
    if (!in_array($vehicle_type, $valid_types)) {
        $_SESSION['error'] = "Invalid vehicle type";
        header("Location: ../driver/vehicle-details.php");
        exit;
    }
    
    if ($year < 1990 || $year > 2025) {
        $_SESSION['error'] = "Invalid year";
        header("Location: ../driver/vehicle-details.php");
        exit;
    }
    
    // Insert vehicle
    $stmt = $conn->prepare("INSERT INTO vehicles (driver_id, vehicle_type, registration_number, model, year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $_SESSION['driver_id'], $vehicle_type, $registration_number, $model, $year);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Vehicle added successfully!";
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
