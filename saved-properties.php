<?php
session_start();
include 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "Please login to unsave properties";
    exit;
}

// Check if property ID is provided
if(!isset($_POST['property_id']) || !is_numeric($_POST['property_id'])) {
    echo "Invalid property ID";
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = $_POST['property_id'];

// Delete saved property
$stmt = $conn->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
$stmt->bind_param("ii", $user_id, $property_id);

if ($stmt->execute()) {
    echo "Property removed from saved list";
} else {
    echo "Error removing property: " . $stmt->error;
}
?>
