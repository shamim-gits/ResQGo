<?php
include '../config/db_connect.php';  
include '../includes/functions.php';  

start_session_if_not_started(); 
require_login(); 


if (!is_customer()) { 
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Ride - ResQGo</title>
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
        <h2>Request Emergency Ambulance</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <form action="../process/ride_request_process.php" method="post">
            <div class="form-group">
                <label for="pickup_location">Pickup Location:</label>
                <input type="text" id="pickup_location" name="pickup_location" required>
            </div>
            
            <div class="form-group">
                <label for="destination">Destination (Hospital):</label>
                <input type="text" id="destination" name="destination" required>
            </div>
            
            <div class="form-group">
                <label for="emergency_type">Emergency Type:</label>
                <select id="emergency_type" name="emergency_type" required>
                    <option value="">Select Emergency Type</option>
                    <option value="cardiac">Cardiac</option>
                    <option value="accident">Accident</option>
                    <option value="pregnancy">Pregnancy</option>
                    <option value="other_medical">Other Medical</option>
                </select>
            </div>
            
            <button type="submit">Request Ambulance</button>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>