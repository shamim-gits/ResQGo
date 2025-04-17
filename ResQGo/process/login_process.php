<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: ../index.php");
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            
            if ($user['user_type'] == 'driver') {
                $stmt = $conn->prepare("SELECT driver_id FROM driver_details WHERE user_id = ?");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $driver = $result->fetch_assoc();
                if ($driver) {
                    $_SESSION['driver_id'] = $driver['driver_id'];
                }
            }
            
            redirect_by_user_type();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header("Location: ../index.php");
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../index.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>
