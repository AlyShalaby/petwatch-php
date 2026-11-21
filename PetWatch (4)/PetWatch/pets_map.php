<?php
// this file returns all pets as json for the map
header('Content-Type: application/json');

require_once('Models/PetsDataSet.php');
require_once('Models/UsersDataSet.php');

$petsDataSet = new PetsDataSet();
$usersDataSet = new UsersDataSet();
$pets = $petsDataSet->fetchAllPets();

$result = array();

// this loops through the pets and builds the result array
foreach ($pets as $pet) {
    $sighting = $petsDataSet->fetchLatestSightingWithCoords($pet->getId());

    // this sets the coordinates if the pet has a sighting
    $latitude  = null;
    $longitude = null;
    $last_seen = '';

    if ($sighting) {
        $latitude  = $sighting['latitude'];
        $longitude = $sighting['longitude'];
        $last_seen = htmlspecialchars($sighting['comment']);
    }

    // this gets owner username for popup info
    $owner_name = 'Unknown';
    $owner = $usersDataSet->fetchUserByID($pet->getUserId());
    if ($owner) {
        $owner_name = htmlspecialchars($owner->getUsername());
    }

    $result[] = array(
        'id'        => $pet->getId(),
        'name'      => htmlspecialchars($pet->getName()),
        'species'   => htmlspecialchars($pet->getSpecies()),
        'status'    => htmlspecialchars($pet->getStatus()),
        'photo_url' => htmlspecialchars($pet->getPhotoUrl()),
        'latitude'  => $latitude,
        'longitude' => $longitude,
        'last_seen' => $last_seen,
        'owner'     => $owner_name
    );
}

echo json_encode(array('success' => true, 'pets' => $result));
?>
