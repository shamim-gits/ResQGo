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
    WHERE r.driver_id = ? AND r.status IN ('accepted', 'en_route', 'picked_up') 
    ORDER BY r.created_at DESC
");

if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    $active_rides = [];
} else {
    $stmt->bind_param("i", $_SESSION['driver_id']); 

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {
        $active_rides = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $active_rides = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Active Rides - ResQGo</title>
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
        <h2 style="margin-bottom: 20px;">Active Rides</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($active_rides)): ?>
            <p style="text-align: center;">You have no active rides at the moment.</p>
            <div style="text-align: center; margin-top: 20px;">
                <a href="ride-requests.php"><button class="secondary-button">View New Requests</button></a>
            </div>
        <?php else: ?>
            <?php foreach ($active_rides as $ride): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3>Emergency: <?php echo ucfirst($ride['emergency_type']); ?></h3>
                            <p><strong>Pickup:</strong> <?php echo $ride['pickup_location']; ?></p>
                            <p><strong>Destination:</strong> <?php echo $ride['destination']; ?></p>
                            <p><strong>Customer:</strong> <?php echo $ride['full_name']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $ride['phone']; ?></p>
                            <p><strong>Status:</strong> <span class="status status-<?php echo $ride['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $ride['status'])); ?></span></p>
                            <?php if ($ride['fare']): ?>
                                <p><strong>Fare:</strong> $<?php echo number_format($ride['fare'], 2); ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right;">
                            <button class="secondary-button" onclick="window.location.href='tel:<?php echo $ride['phone']; ?>'">Call Customer</button>
                            
                            <?php if ($ride['status'] == 'accepted'): ?>
                                <div style="margin-top: 10px;">
                                    <form action="../process/update_ride_status_process.php" method="post">
                                        <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                                        <input type="hidden" name="status" value="en_route">
                                        <button type="submit">En Route to Pickup</button>
                                    </form>
                                </div>
                            <?php elseif ($ride['status'] == 'en_route'): ?>
                                <div style="margin-top: 10px;">
                                    <form action="../process/update_ride_status_process.php" method="post">
                                        <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                                        <input type="hidden" name="status" value="picked_up">
                                        <button type="submit">Arrived at Pickup</button>
                                    </form>
                                </div>
                            <?php elseif ($ride['status'] == 'picked_up'): ?>
                                <div style="margin-top: 10px;">
                                    <form action="../process/update_ride_status_process.php" method="post">
                                        <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit">Complete Ride</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 10px;">
                                <form action="../process/update_ride_status_process.php" method="post" onsubmit="return confirm('Are you sure you want to cancel this ride?');">
                                    <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" style="background-color: #f44336;">Cancel Ride</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($active_rides)): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="ride-requests.php"><button class="secondary-button">View New Requests</button></a>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>