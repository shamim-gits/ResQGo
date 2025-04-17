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
    $status = sanitize_input($_POST['status']);
    
    if (empty($ride_id) || empty($status)) {
        $_SESSION['error'] = "Ride ID and status are required";
        header("Location: ../driver/active-rides.php");
        exit;
    }
    
    $valid_statuses = ['accepted', 'en_route', 'picked_up', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status";
        header("Location: ../driver/active-rides.php");
        exit;
    }
    
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("SELECT * FROM rides WHERE ride_id = ? AND driver_id = ?");
        $stmt->bind_param('ii', $ride_id, $_SESSION['driver_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $ride = $result->fetch_assoc();
        
        if (!$ride) {
            $conn->rollback();
            $_SESSION['error'] = "Ride not found or does not belong to you";
            header("Location: ../driver/active-rides.php");
            exit;
        }

        $stmt = $conn->prepare("UPDATE rides SET status = ? WHERE ride_id = ?");
        $stmt->bind_param('si', $status, $ride_id);
        $stmt->execute();
        
        
        if ($status == 'completed' || $status == 'cancelled') {
            $stmt = $conn->prepare("UPDATE driver_details SET status = 'available' WHERE driver_id = ?");
            $stmt->bind_param('i', $_SESSION['driver_id']);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE rides SET completed_at = NOW() WHERE ride_id = ?");
            $stmt->bind_param('i', $ride_id);
            $stmt->execute();
        }
        
        $conn->commit();
        
        $_SESSION['success'] = "Ride status updated successfully!";
        
        if ($status == 'completed' || $status == 'cancelled') {
            header("Location: ../driver/ride-history.php");
        } else {
            header("Location: ../driver/active-rides.php");
        }
        exit;
        
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../driver/active-rides.php");
        exit;
    }
} else {
    header("Location: ../driver/active-rides.php");
    exit;
}
?>
