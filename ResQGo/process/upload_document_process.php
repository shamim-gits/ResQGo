<?php
include '../config/db_connect.php';
include '../includes/functions.php';

start_session_if_not_started();
require_login();

if (!is_driver()) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = sanitize_input($_POST['document_type']);
    
    if (empty($document_type) || !isset($_FILES['document']) || $_FILES['document']['error'] != 0) {
        $_SESSION['error'] = "Document type and file are required";
        header("Location: ../driver/documents.php");
        exit;
    }
    
    $valid_types = ['license', 'insurance', 'certification', 'other'];
    if (!in_array($document_type, $valid_types)) {
        $_SESSION['error'] = "Invalid document type";
        header("Location: ../driver/documents.php");
        exit;
    }
    
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error'] = "Only PDF, JPG, JPEG, and PNG files are allowed";
        header("Location: ../driver/documents.php");
        exit;
    }
    
    $upload_dir = "../uploads/documents/" . $_SESSION['driver_id'] . "/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = $document_type . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $filename;
    
    try {
        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            $document_path = "uploads/documents/" . $_SESSION['driver_id'] . "/" . $filename;
            
            $stmt = $conn->prepare("INSERT INTO driver_documents (driver_id, document_type, document_path) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $_SESSION['driver_id'], $document_type, $document_path);
            $stmt->execute();
            
            $_SESSION['success'] = "Document uploaded successfully!";
        } else {
            $_SESSION['error'] = "Failed to upload document";
        }
        
        header("Location: ../driver/documents.php");
        exit;
        
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../driver/documents.php");
        exit;
    }
} else {
    header("Location: ../driver/documents.php");
    exit;
}
?>
