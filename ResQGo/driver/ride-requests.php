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
    SELECT r.*, u.full_name, u.phone 
    FROM rides r 
    JOIN users u ON r.customer_id = u.user_id 
    WHERE r.status = 'requested' 
    ORDER BY r.created_at DESC
");

if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    $ride_requests = [];
} else {

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $ride_requests = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $ride_requests = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ride Requests - ResQGo</title>
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
        <h2 style="margin-bottom: 20px;">Available Ride Requests</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($ride_requests)): ?>
            <p style="text-align: center;">No ride requests available at the moment.</p>
        <?php else: ?>
            <?php foreach ($ride_requests as $ride): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3>Emergency: <?php echo ucfirst($ride['emergency_type']); ?></h3>
                            <p><strong>Pickup:</strong> <?php echo $ride['pickup_location']; ?></p>
                            <p><strong>Destination:</strong> <?php echo $ride['destination']; ?></p>
                            <p><strong>Customer:</strong> <?php echo $ride['full_name']; ?></p>
                            <p><strong>Requested:</strong> <?php echo date('M j, Y g:i A', strtotime($ride['created_at'])); ?></p>
                            <?php if ($ride['fare']): ?>
                                <p><strong>Fare:</strong> $<?php echo number_format($ride['fare'], 2); ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right;">
                            <form action="../process/accept_ride_process.php" method="post">
                                <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                                <button type="submit">Accept Ride</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>