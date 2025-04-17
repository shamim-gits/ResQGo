<?php
include '../config/db_connect.php';  
include '../includes/functions.php';  

start_session_if_not_started();
require_login();  


if (!is_customer()) {
    header("Location: ../index.php");  
    exit;
}

if (!isset($_GET['ride_id'])) {
    header("Location: request-ride.php");  
    exit;
}

$ride_id = sanitize_input($_GET['ride_id']);  

// ride details using MySQLi
$stmt = $conn->prepare("
    SELECT r.*, u.full_name, u.phone
    FROM rides r
    LEFT JOIN driver_details d ON r.driver_id = d.driver_id
    LEFT JOIN users u ON d.user_id = u.user_id
    WHERE r.ride_id = ? AND r.customer_id = ?
");

if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    header("Location: request-ride.php");
    exit;
}

// Bind parameters (i = integer for both parameters)
$stmt->bind_param("ii", $ride_id, $_SESSION['user_id']);  

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
$ride = $result->fetch_assoc();

// Check if the ride exists
if (!$ride) {
    $_SESSION['error'] = "Ride not found or does not belong to you";
    header("Location: request-ride.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ride Confirmation - ResQGo</title>
<link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container">
    <header>
        <h1>ResQGo</h1>
        <p>Emergency at Your Fingertips</p>
    </header>
    
    <nav>
        <ul>
            <li><a href="request-ride.php">Request Ride</a></li>
            <li><a href="ride-history.php">Ride History</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="../process/logout_process.php">Logout</a></li>
        </ul>
    </nav>
    
    <div>
        <h2>Ride Confirmation</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Ride Details</h3>
            <p><strong>Pickup:</strong> <?php echo $ride['pickup_location']; ?></p>
            <p><strong>Destination:</strong> <?php echo $ride['destination']; ?></p>
            <p><strong>Emergency Type:</strong> <?php echo ucfirst($ride['emergency_type']); ?></p>
            <p><strong>Fare:</strong> $<?php echo number_format($ride['fare'], 2); ?></p>
            <p><strong>Status:</strong> <span class="status status-<?php echo $ride['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $ride['status'])); ?></span></p>
            
            <?php if ($ride['driver_id']): ?>
                <h3 style="margin-top: 20px;">Driver Information</h3>
                <p><strong>Driver:</strong> <?php echo $ride['full_name']; ?></p>
                <p><strong>Phone:</strong> <?php echo $ride['phone']; ?></p>
                <button class="secondary-button" onclick="window.location.href='tel:<?php echo $ride['phone']; ?>'">Call Driver</button>
            <?php else: ?>
                <p style="margin-top: 20px;"><strong>Driver:</strong> Waiting for a driver to accept your ride...</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="ride-history.php"><button class="secondary-button">View All Rides</button></a>
            <a href="request-ride.php"><button>Request Another Ride</button></a>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>