<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if (!is_driver()) {
    header("Location: ../index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM vehicles WHERE driver_id = ?");

if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    $vehicles = [];
} else {
    $stmt->bind_param("i", $_SESSION['driver_id']);

    $stmt->execute();

    $result = $stmt->get_result();

  
    if ($result) {
        $vehicles = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $vehicles = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vehicle Details - ResQGo</title>
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
        <h2>Vehicle Details</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Add New Vehicle</h3>
            <form action="../process/add_vehicle_process.php" method="post">
                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type:</label>
                    <select id="vehicle_type" name="vehicle_type" required>
                        <option value="">Select Vehicle Type</option>
                        <option value="ambulance">Ambulance</option>
                        <option value="paramedic">Paramedic Vehicle</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="registration_number">Registration Number:</label>
                    <input type="text" id="registration_number" name="registration_number" required>
                </div>
                
                <div class="form-group">
                    <label for="model">Vehicle Model:</label>
                    <input type="text" id="model" name="model" required>
                </div>
                
                <div class="form-group">
                    <label for="year">Year:</label>
                    <input type="number" id="year" name="year" min="1990" max="2025" required>
                </div>
                
                <button type="submit">Add Vehicle</button>
            </form>
        </div>
        
        <h3 style="margin-top: 30px;">Your Vehicles</h3>
        
        <?php if (empty($vehicles)): ?>
            <p style="text-align: center;">You have not added any vehicles yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Vehicle Type</th>
                        <th>Registration Number</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><?php echo ucfirst($vehicle['vehicle_type']); ?></td>
                            <td><?php echo $vehicle['registration_number']; ?></td>
                            <td><?php echo $vehicle['model']; ?></td>
                            <td><?php echo $vehicle['year']; ?></td>
                            <td>
                                <a href="edit-vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>">Edit</a> | 
                                <a href="../process/delete_vehicle_process.php?id=<?php echo $vehicle['vehicle_id']; ?>" onclick="return confirm('Are you sure you want to delete this vehicle?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="profile.php"><button class="secondary-button">Back to Profile</button></a>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>
</body>
</html>