<?php
include '../includes/functions.php';

start_session_if_not_started();

$_SESSION = array();


session_destroy();

header("Location: ../index.php");
exit;
?>