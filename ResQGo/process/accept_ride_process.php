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
    $ride_id = sanitize_input($_POST['ride_id']);
    
    if (empty($ride_id)) {
        $_SESSION['error'] = "Ride ID is required";
        header("Location: ../driver/ride-requests.php");
        exit;
    }
    
    $conn->begin_transaction();
    
    $stmt = $conn->prepare("SELECT * FROM rides WHERE ride_id = ? AND status = 'requested'");
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();
    
    if (!$ride) {
        $conn->rollback();
        $_SESSION['error'] = "Ride is no longer available";
        header("Location: ../driver/ride-requests.php");
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE rides SET driver_id = ?, status = 'accepted' WHERE ride_id = ?");
    $stmt->bind_param("ii", $_SESSION['driver_id'], $ride_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("UPDATE driver_details SET status = 'busy' WHERE driver_id = ?");
    $stmt->bind_param("i", $_SESSION['driver_id']);
    $stmt->execute();
    
    $conn->commit();
    
    $_SESSION['success'] = "Ride accepted successfully!";
    header("Location: ../driver/active-rides.php");
    exit;
    
} else {
    header("Location: ../driver/ride-requests.php");
    exit;
}
?>
