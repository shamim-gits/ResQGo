<?php
include '../config/db_connect.php';  
include '../includes/functions.php';

start_session_if_not_started();
require_login();  


if (!is_customer()) {
    header("Location: ../index.php");  
    exit;
}

$stmt = $conn->prepare("
    SELECT r.*, u.full_name 
    FROM rides r 
    LEFT JOIN driver_details d ON r.driver_id = d.driver_id
    LEFT JOIN users u ON d.user_id = u.user_id
    WHERE r.customer_id = ? 
    ORDER BY r.created_at DESC
");


if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    $ride_history = [];
} else {
    
    $stmt->bind_param("i", $_SESSION['user_id']); 
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
            
            <li><a href="request-ride.php">Request Ride</a></li>
            <li><a href="ride-history.php">Ride History</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="../process/logout_process.php">Logout</a></li>
        </ul>
    </nav>
    
    <div>
        <h2>Your Ride History</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
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
                        <th>Driver</th>
                        <th>Emergency Type</th>
                        <th>Fare</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ride_history as $ride): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($ride['created_at'])); ?></td>
                            <td><?php echo $ride['pickup_location']; ?></td>
                            <td><?php echo $ride['destination']; ?></td>
                            <td><?php echo $ride['full_name'] ? $ride['full_name'] : 'Not assigned'; ?></td>
                            <td><?php echo ucfirst($ride['emergency_type']); ?></td>
                            <td><?php echo $ride['fare'] ? '$' . number_format($ride['fare'], 2) : 'Not set'; ?></td>
                            <td><span class="status status-<?php echo $ride['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $ride['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="request-ride.php"><button>Request New Ride</button></a>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>