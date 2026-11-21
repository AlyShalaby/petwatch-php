<?php
// this file searches pets and returns the results as json
header('Content-Type: application/json');
session_start();

require_once('Models/PetsDataSet.php');

// this checks token from search.js
if (!isset($_GET['token']) || !isset($_SESSION['ajax_token']) || $_GET['token'] !== $_SESSION['ajax_token']) {
    echo json_encode(array('success' => false, 'pets' => array(), 'total' => 0, 'error' => 'Invalid token'));
    exit();
}

// this gets the search values from the url
$name    = isset($_GET['name'])    ? trim($_GET['name'])    : '';
$species = isset($_GET['species']) ? trim($_GET['species']) : '';
$status  = isset($_GET['status'])  ? trim($_GET['status'])  : '';

// this stops the search if all fields are empty
if (!$name && !$species && !$status) {
    echo json_encode(array('success' => true, 'pets' => array(), 'total' => 0));
    exit();
}

$petsDataSet = new PetsDataSet();

// this searches the database and gets matching pets
$pets  = $petsDataSet->searchPets($name, $species, '', '', $status, '', '', 20, 0);
$total = $petsDataSet->countPets($name, $species, '', '', $status);

// this builds the results array to send back
$result = array();
foreach ($pets as $pet) {
    $result[] = array(
        'id'        => $pet->getId(),
        'name'      => htmlspecialchars($pet->getName()),
        'species'   => htmlspecialchars($pet->getSpecies()),
        'status'    => htmlspecialchars($pet->getStatus()),
        'photo_url' => htmlspecialchars($pet->getPhotoUrl())
    );
}

echo json_encode(array('success' => true, 'pets' => $result, 'total' => $total));
?>
