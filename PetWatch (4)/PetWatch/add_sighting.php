<?php
// this file saves a sighting when a user submits the form
header('Content-Type: application/json');
session_start();

// this checks the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'error' => 'You must be logged in'));
    exit();
}

// this checks token from map.js
if (!isset($_POST['token']) || !isset($_SESSION['ajax_token']) || $_POST['token'] !== $_SESSION['ajax_token']) {
    echo json_encode(array('success' => false, 'error' => 'Invalid token'));
    exit();
}

// this gets the data sent from the form
$pet_id    = $_POST['pet_id'];
$comment   = trim($_POST['comment']);
$latitude  = trim($_POST['latitude']);
$longitude = trim($_POST['longitude']);
$user_id   = $_SESSION['user_id'];

// this checks the comment is not empty
if (empty($comment)) {
    echo json_encode(array('success' => false, 'error' => 'Comment cannot be empty'));
    exit();
}

// this cleans the comment to stop XSS
$comment = htmlspecialchars($comment);
// this keeps empty coordinates as null so latest map location still works
$latitude = ($latitude !== '') ? $latitude : null;
$longitude = ($longitude !== '') ? $longitude : null;

require_once('Models/Database.php');

$db = Database::getInstance();
$connection = $db->getdbConnection();

// this updates latest sighting for this pet, if none it adds a new one
$stmt = $connection->prepare("UPDATE sightings SET user_id = ?, comment = ?, latitude = ?, longitude = ?, timestamp = NOW() WHERE pet_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute(array($user_id, $comment, $latitude, $longitude, $pet_id));
if ($stmt->rowCount() == 0) {
    $stmt = $connection->prepare("INSERT INTO sightings (pet_id, user_id, comment, latitude, longitude, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute(array($pet_id, $user_id, $comment, $latitude, $longitude));
}

echo json_encode(array('success' => true, 'message' => 'Sighting reported!'));
?>
