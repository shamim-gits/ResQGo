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
    $ride_id = sanitize_input($_POST['ride_id']);
    $fare = sanitize_input($_POST['fare']);
    
    if (empty($ride_id) || empty($fare)) {
        $_SESSION['error'] = "Ride ID and fare are required";
        header("Location: ../customer/fare-selection.php?ride_id=" . $ride_id);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM rides WHERE ride_id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $ride_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $ride = $result->fetch_assoc();
        
        if (!$ride) {
            $_SESSION['error'] = "Ride not found or does not belong to you";
            header("Location: ../customer/request-ride.php");
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE rides SET fare = ? WHERE ride_id = ?");
        $stmt->bind_param("di", $fare, $ride_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Fare selected successfully! Your ride request has been submitted.";
        header("Location: ../customer/ride-confirmation.php?ride_id=" . $ride_id);
        exit;
        
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../customer/fare-selection.php?ride_id=" . $ride_id);
        exit;
    }
} else {
    header("Location: ../customer/request-ride.php");
    exit;
}
?>
