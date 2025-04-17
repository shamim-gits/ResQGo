<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "resqgo";


$conn = mysqli_connect($host, $username, $password, $database);


if (!$conn) {
    echo "Connection failed!";
}

?>
