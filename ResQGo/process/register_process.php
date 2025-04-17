<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../" . $user_type . "/signup.php");
        exit;
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: ../" . $user_type . "/signup.php");
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email already exists";
            header("Location: ../" . $user_type . "/signup.php");
            exit;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("INSERT INTO users (email, password, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $hashed_password, $full_name, $phone, $user_type);
        $stmt->execute();
        
        $user_id = $conn->insert_id;
        
        if ($user_type == 'driver') {
            $license_number = sanitize_input($_POST['license_number']);
            $experience_years = sanitize_input($_POST['experience_years']);
            
            if (empty($license_number) || empty($experience_years)) {
                $conn->rollback();
                $_SESSION['error'] = "Driver details are required";
                header("Location: ../driver/signup.php");
                exit;
            }
            
            $stmt = $conn->prepare("INSERT INTO driver_details (user_id, license_number, experience_years) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $license_number, $experience_years);
            $stmt->execute();
            
            $driver_id = $conn->insert_id;
            
            $_SESSION['driver_id'] = $driver_id;
        }
        
        $conn->commit();
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['full_name'] = $full_name;
        
        redirect_by_user_type();
        
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../" . $user_type . "/signup.php");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>
