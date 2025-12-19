<?php
$conn = new mysqli(
    "localhost",
    "foodfram_user_dbuser",   // Database Username
    "54.hZTriemhqC4y",                // Database Password
    "foodfram_user_db"        // Database Name
);

if ($conn->connect_error) {
    die("Database connection failed");
}
?>
