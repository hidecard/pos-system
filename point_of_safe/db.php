<?php
$conn = mysqli_connect("localhost", "root", "", "point_of_safe");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
