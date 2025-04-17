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
    SELECT u.*, d.license_number, d.experience_years, d.status
    FROM users u
    JOIN driver_details d ON u.user_id = d.user_id
    WHERE u.user_id = ?
");

if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    header("Location: ride-requests.php");
    exit;
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "User not found";
        header("Location: ../index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile - ResQGo</title>
<link rel="stylesheet" href="../styles.css">
<style>
    .tabs {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .tab {
        padding: 10px 20px;
        cursor: pointer;
        background-color: #f8f8f8;
        border: 1px solid #ddd;
        border-bottom: none;
        margin-right: 5px;
        border-radius: 5px 5px 0 0;
    }
    
    .tab.active {
        background-color: #fff;
        border-bottom: 1px solid #fff;
        margin-bottom: -1px;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
</style>
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
        <h2>My Profile</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active" onclick="openTab(event, 'profile')">Profile</div>
            <div class="tab" onclick="openTab(event, 'vehicle')">Vehicle Details</div>
            <div class="tab" onclick="openTab(event, 'documents')">Documents</div>
        </div>
        
        <div id="profile" class="tab-content active">
            <div class="card">
                <h3>Personal Information</h3>
                <form action="../process/update_profile_process.php" method="post">
                    <div class="form-group">
                        <label for="full_name">Full Name:</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" value="<?php echo $user['email']; ?>" readonly>
                        <small>Email cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="license_number">Driver's License Number:</label>
                        <input type="text" id="license_number" name="license_number" value="<?php echo $user['license_number']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience_years">Years of Experience:</label>
                        <input type="number" id="experience_years" name="experience_years" min="0" value="<?php echo $user['experience_years']; ?>" required>
                    </div>
                    
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit">Update Profile</button>
                </form>
            </div>
        </div>
        
        <div id="vehicle" class="tab-content">
            <div class="card">
                <h3>Vehicle Details</h3>
                <p>Manage your vehicle information here.</p>
                <a href="vehicle-details.php"><button>Manage Vehicles</button></a>
            </div>
        </div>
        
        <div id="documents" class="tab-content">
            <div class="card">
                <h3>Documents</h3>
                <p>Upload and manage your documents here.</p>
                <a href="documents.php"><button>Manage Documents</button></a>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 ResQGo. All rights reserved.</p>
    </footer>
</div>

<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    
    // Hide all tab content
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].className = tabcontent[i].className.replace(" active", "");
    }
    
    // Remove active class from all tabs
    tablinks = document.getElementsByClassName("tab");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    
    // Show the current tab and add active class
    document.getElementById(tabName).className += " active";
    evt.currentTarget.className += " active";
}
</script>
</body>
</html>