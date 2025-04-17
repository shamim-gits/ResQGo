<?php
include 'includes/functions.php';
start_session_if_not_started();

if (is_logged_in()) {
    redirect_by_user_type();
    exit;
} else {
    if (isset($_SESSION['error'])) {
        $error = urlencode($_SESSION['error']);
        unset($_SESSION['error']);
        header("Location: index.html?error=$error");
    } else {
       
        header("Location: index.html");
    }
    exit;
}
?>