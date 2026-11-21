<?php
session_start();

// this checks if user is logged in user needs to login to report sighting
if (!isset($_SESSION['user_id'])) {
    $pet_id = $_GET['pet_id'] ?? '';
    header("Location: login.php?message=Please login to report a sighting");
    exit();
}

$view = new stdClass();
$view->pageTitle = 'Report Sighting';

// this gets pet id from url
$pet_id = $_GET['pet_id'] ?? '';
if (empty($pet_id)) {
    header("Location: browse.php");
    exit();
}

// this gets all pets to find the one being reported
require_once('Models/PetsDataSet.php');
$petsDataSet = new PetsDataSet();
$allPets = $petsDataSet->fetchAllPets();
$view->currentPet = null;

// this finds the pet with matching id
foreach ($allPets as $pet) {
    if ($pet->getId() == $pet_id) {
        $view->currentPet = $pet;
        break;
    }
}

// this handles when user submits the sighting form
if (isset($_POST['submit_sighting'])) {
    $comment = trim($_POST['comment']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    $user_id = $_SESSION['user_id'];

    // this checks if comment is filled in
    if (!empty($comment)) {
        require_once('Models/Database.php');
        $db = Database::getInstance();
        $connection = $db->getdbConnection();

        // latitude and longitude are related to mapping
        $latitude = !empty($latitude) ? $latitude : null;
        $longitude = !empty($longitude) ? $longitude : null;

        // this inserts the sighting into database
        $stmt = $connection->prepare("INSERT INTO sightings (pet_id, user_id, comment, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array($pet_id, $user_id, $comment, $latitude, $longitude));
        $view->success = "Sighting reported successfully!";
    } else {
        $view->error = "Please provide a comment about the sighting";
    }
}

require_once('Views/report_sighting.phtml');
?>
