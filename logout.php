<?php
session_start();
require 'db.php'; // Make sure this is added to use the $conn object
function addLog($conn, $message) {
    // Use prepared statement to prevent SQL injection and handle special characters
    $stmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
    if ($stmt === false) {
        // Error preparing the statement
        die('Error preparing statement: ' . $conn->error);
    }

    // Bind parameters securely
    $stmt->bind_param("s", $message);

    // Execute the query
    if (!$stmt->execute()) {
        die('Error executing query: ' . $stmt->error);
    }

    // Close the statement
    $stmt->close();
}
// Log the logout action if the user is logged in
if (isset($_SESSION['username'])) {
    $username = $conn->real_escape_string($_SESSION['username']);
    $log_msg = "User '{$username}' logged out.";
    addLog($conn, $log_msg);
}

// Clear session and redirect
session_unset();
session_destroy();
header('Location: login.php?success=You have been logged out.');
exit();
?>
