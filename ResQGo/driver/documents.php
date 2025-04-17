<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if (!is_driver()) {
    header("Location: ../index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM driver_documents WHERE driver_id = ? ORDER BY uploaded_at DESC");

if (!$stmt) {
    $_SESSION['error'] = "Error preparing the query: " . $conn->error;
    $documents = [];
} else {
    $stmt->bind_param("i", $_SESSION['driver_id']);  

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $documents = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $documents = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documents - ResQGo</title>
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
        <h2>Documents</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Upload New Document</h3>
            <form action="../process/upload_document_process.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="document_type">Document Type:</label>
                    <select id="document_type" name="document_type" required>
                        <option value="">Select Document Type</option>
                        <option value="license">Driver's License</option>
                        <option value="insurance">Insurance</option>
                        <option value="certification">Medical Certification</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="document">Document File (PDF, JPG, PNG):</label>
                    <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                
                <button type="submit">Upload Document</button>
            </form>
        </div>
        
        <h3 style="margin-top: 30px;">Your Documents</h3>
        
        <?php if (empty($documents)): ?>
            <p style="text-align: center;">You have not uploaded any documents yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Uploaded Date</th>
                        <th>Verification Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td><?php echo ucfirst($document['document_type']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($document['uploaded_at'])); ?></td>
                            <td>
                                <?php if ($document['verified']): ?>
                                    <span style="color: green;">Verified</span>
                                <?php else: ?>
                                    <span style="color: orange;">Pending Verification</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="../<?php echo $document['document_path']; ?>" target="_blank">View</a>
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