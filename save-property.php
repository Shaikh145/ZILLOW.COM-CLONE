<?php
session_start();
include 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "Please login to save properties";
    exit;
}

// Check if property ID is provided
if(!isset($_POST['property_id']) || !is_numeric($_POST['property_id'])) {
    echo "Invalid property ID";
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = $_POST['property_id'];

// Check if property exists
$stmt = $conn->prepare("SELECT id FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Property not found";
    exit;
}

// Check if property is already saved
$stmt = $conn->prepare("SELECT id FROM saved_properties WHERE user_id = ? AND property_id = ?");
$stmt->bind_param("ii", $user_id, $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Property already saved";
    exit;
}

// Save property
$stmt = $conn->prepare("INSERT INTO saved_properties (user_id, property_id, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $user_id, $property_id);

if ($stmt->execute()) {
    echo "Property saved successfully";
} else {
    echo "Error saving property: " . $stmt->error;
}
?>
