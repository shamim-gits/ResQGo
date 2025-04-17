<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = sanitize_input($_POST['full_name']);
    $phone = sanitize_input($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name) || empty($phone)) {
        $_SESSION['error'] = "Name and phone are required";
        header("Location: ../" . $_SESSION['user_type'] . "/profile.php");
        exit;
    }
    
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            $conn->rollback();
            $_SESSION['error'] = "User not found";
            header("Location: ../" . $_SESSION['user_type'] . "/profile.php");
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $full_name, $phone, $_SESSION['user_id']);
        $stmt->execute();
        
        if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
            if (!password_verify($current_password, $user['password'])) {
                $conn->rollback();
                $_SESSION['error'] = "Current password is incorrect";
                header("Location: ../" . $_SESSION['user_type'] . "/profile.php");
                exit;
            }
            
            if ($new_password !== $confirm_password) {
                $conn->rollback();
                $_SESSION['error'] = "New passwords do not match";
                header("Location: ../" . $_SESSION['user_type'] . "/profile.php");
                exit;
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            $stmt->execute();
        }
        
        if (is_driver() && isset($_POST['license_number']) && isset($_POST['experience_years'])) {
            $license_number = sanitize_input($_POST['license_number']);
            $experience_years = sanitize_input($_POST['experience_years']);
            
            if (!empty($license_number) && !empty($experience_years)) {
                $stmt = $conn->prepare("UPDATE driver_details SET license_number = ?, experience_years = ? WHERE driver_id = ?");
                $stmt->bind_param("ssi", $license_number, $experience_years, $_SESSION['driver_id']);
                $stmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Update session variable
        $_SESSION['full_name'] = $full_name;
        
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: ../" . $_SESSION['user_type'] . "/profile.php");
        exit;
        
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../" . $_SESSION['user_type'] . "/profile.php");
        exit;
    }
} else {
    header("Location: ../" . $_SESSION['user_type'] . "/profile.php");
    exit;
}
?>
