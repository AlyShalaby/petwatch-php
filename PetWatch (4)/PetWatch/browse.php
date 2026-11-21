<?php
session_start();

require_once('Models/PetsDataSet.php');

// this makes the view object to pass data to the view
$view = new stdClass();
$view->pageTitle = 'Browse Pets';
// this makes ajax token for search calls
if (!isset($_SESSION['ajax_token'])) {
    $_SESSION['ajax_token'] = bin2hex(random_bytes(32));
}
$view->ajaxToken = $_SESSION['ajax_token'];

$petsDataSet = new PetsDataSet();

// this sets up paginatioin
$pets_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
// this calculates how many pets to skip in this instance we skip by 10
$offset = ($current_page - 1) * $pets_per_page;

// this gets search parameters from the form
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$species = isset($_GET['species']) ? trim($_GET['species']) : '';
$breed = isset($_GET['breed']) ? trim($_GET['breed']) : '';
$color = isset($_GET['color']) ? trim($_GET['color']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$description = isset($_GET['description']) ? trim($_GET['description']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

// this checks if user is seareching or just browsing
if ($name || $species || $breed || $color || $status || $description || $location) {
    $view->total_pets = $petsDataSet->countPets($name, $species, $breed, $color, $status, $description, $location);
    $view->pets = $petsDataSet->searchPets($name, $species, $breed, $color, $status, $description, $location, $pets_per_page, $offset);
} else {
    $view->total_pets = $petsDataSet->countAllPets();
    $view->pets = $petsDataSet->fetchAllPetsPaginated($pets_per_page, $offset);
}

// this calculates total pages needed
$view->total_pages = ceil($view->total_pets / $pets_per_page);
if ($view->total_pages < 1) $view->total_pages = 1;
$view->current_page = $current_page;
// this saves search params so form can remember what user searched
$view->search_params = array('name' => $name, 'species' => $species, 'breed' => $breed, 'color' => $color, 'status' => $status, 'description' => $description, 'location' => $location);

require_once('Views/browse.phtml');
?>
