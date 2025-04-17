<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if (!is_driver()) {
    header("Location: ../index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT r.*, u.full_name 
    FROM rides r 
    JOIN users u ON r.customer_id = u.user_id 
    WHERE r.driver_id = ? AND r.status IN ('completed', 'cancelled') 
    ORDER BY r.completed_at DESC
");

if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    $ride_history = [];
} else {
    $stmt->bind_param("i", $_SESSION['driver_id']);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {
        $ride_history = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $ride_history = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ride History - ResQGo</title>
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
            
            <li><a href="ride-requests.php">Ride Requests</a></li>
            <li><a href="active-rides.php">Active Rides</a></li>
            <li><a href="ride-history.php">Ride History</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="../process/logout_process.php">Logout</a></li>
        </ul>
    </nav>
    
    <div>
        <h2 style="margin-bottom: 20px;">Your Ride History</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($ride_history)): ?>
            <p style="text-align: center;">You have no ride history yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Pickup</th>
                        <th>Destination</th>
                        <th>Customer</th>
                        <th>Emergency Type</th>
                        <th>Fare</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ride_history as $ride): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($ride['completed_at'])); ?></td>
                            <td><?php echo $ride['pickup_location']; ?></td>
                            <td><?php echo $ride['destination']; ?></td>
                            <td><?php echo $ride['full_name']; ?></td>
                            <td><?php echo ucfirst($ride['emergency_type']); ?></td>
                            <td><?php echo $ride['fare'] ? '$' . number_format($ride['fare'], 2) : 'N/A'; ?></td>
                            <td><span class="status status-<?php echo $ride['status']; ?>"><?php echo ucfirst($ride['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="ride-requests.php"><button class="secondary-button">View New Requests</button></a>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>