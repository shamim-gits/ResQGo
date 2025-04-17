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
$customer_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM rides WHERE ride_id = ? AND customer_id = ?");
$stmt->bind_param("ii", $ride_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$ride = $result->fetch_assoc();

if (!$ride) {
    $_SESSION['error'] = "Ride not found";
    header("Location: request-ride.php");
    exit;
}

$distance = rand(2, 15); 


$standard_fare = calculate_fare($ride['emergency_type'], $distance);
$express_fare = $standard_fare * 1.5;
$premium_fare = $standard_fare * 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fare Selection - ResQGo</title>
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
        <h2>Select Fare Option</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Ride Details</h3>
            <p><strong>Pickup:</strong> <?php echo $ride['pickup_location']; ?></p>
            <p><strong>Destination:</strong> <?php echo $ride['destination']; ?></p>
            <p><strong>Emergency Type:</strong> <?php echo ucfirst($ride['emergency_type']); ?></p>
            <p><strong>Estimated Distance:</strong> <?php echo $distance; ?> miles</p>
        </div>
        
        <h3>Choose Your Fare Option</h3>
        
        <div style="display: flex;  justify-content: space-between;">
            <div class="card" style="flex: 1; min-width: 250px; margin: 10px;">
                <h3>Standard</h3>
                <p><strong>Price:</strong> Tk
                <?php echo number_format($standard_fare, 2); ?>
                </p>
                <p>Basic ambulance service with standard response time.</p>
                <form action="../process/update_fare_process.php" method="post">
                    <input type="hidden" name="ride_id" value="<?php echo $ride_id; ?>">
                    <input type="hidden" name="fare" value="<?php echo $standard_fare; ?>">
                    <button type="submit">Select Standard</button>
                </form>
            </div>
            
            <div class="card" style="flex: 1; min-width: 250px; margin: 10px;">
                <h3>Express</h3>
                <p><strong>Price:</strong> Tk <?php echo number_format($express_fare, 2); ?></p>
                <p>Priority dispatch with faster response time.</p>
                <form action="../process/update_fare_process.php" method="post">
                    <input type="hidden" name="ride_id" value="<?php echo $ride_id; ?>">
                    <input type="hidden" name="fare" value="<?php echo $express_fare; ?>">
                    <button type="submit">Select Express</button>
                </form>
            </div>
            
            <div class="card" style="flex: 1; min-width: 250px; margin: 10px;">
                <h3>Premium</h3>
                <p><strong>Price:</strong> Tk <?php echo number_format($premium_fare, 2); ?></p>
                <p>Highest priority with advanced life support equipment.</p>
                <form action="../process/update_fare_process.php" method="post">
                    <input type="hidden" name="ride_id" value="<?php echo $ride_id; ?>">
                    <input type="hidden" name="fare" value="<?php echo $premium_fare; ?>">
                    <button type="submit">Select Premium</button>
                </form>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="request-ride.php"><button class="secondary-button">Cancel</button></a>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>