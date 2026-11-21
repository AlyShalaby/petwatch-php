  <?php
session_start();

// this checks if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php?message=Please login as pet owner to manage pets");
    exit();
}

$view = new stdClass();
$view->pageTitle = 'Manage Pets';

// this shows message if pet was updated
if (isset($_GET['updated'])) {
    $view->message = "Pet updated!";
}
if (isset($_GET['deleted'])) {
    $view->message = "Pet deleted!";
}
if (isset($_GET['notfound'])) {
    $view->error = "Pet was not found";
}

require_once('Models/PetsDataSet.php');
require_once('Models/Database.php');
require_once('Models/PetData.php');

$petsDataSet = new PetsDataSet();
$db = Database::getInstance();
$connection = $db->getdbConnection();

// this addss a new pet
if (isset($_POST['add_pet'])) {
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = isset($_POST['breed']) ? trim($_POST['breed']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $status = $_POST['status'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $user_id = $_SESSION['user_id'];

    if ($name && $species) {
        $date_reported = date('Y-m-d H:i:s');
        $photo_url = '';

        // this handles the picrture upload
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
            $upload_dir = 'Images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // this makes a unique filename
            $ext = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
            $photo_url = uniqid('pet_', true) . '.' . $ext;

            if (!move_uploaded_file($_FILES['picture']['tmp_name'], $upload_dir . $photo_url)) {
                $photo_url = '';
            }
        }

        $stmt = $connection->prepare("INSERT INTO pets (name, species, breed, color, status, date_reported, description, photo_url, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array($name, $species, $breed, $color, $status, $date_reported, $description, $photo_url, $user_id));
        $view->message = "Pet added!";
    } else {
        $view->error = "Need name and species";
    }
}

// this deletes a pet - admin can delete any pet
if (isset($_GET['delete'])) {
    try {
        $stmt = $connection->prepare("DELETE FROM sightings WHERE pet_id = ?");
        $stmt->execute(array($_GET['delete']));
        $stmt = $connection->prepare("DELETE FROM pets WHERE id = ?");
        $stmt->execute(array($_GET['delete']));
        if ($stmt->rowCount() > 0) header("Location: manage_pets.php?deleted=1");
        else header("Location: manage_pets.php?notfound=1");
        exit();
    } catch (Exception $e) {
        $view->error = "Could not delete this pet";
    }
}

// this updates pet if lost or found - admin can update any pet
if (isset($_POST['update_status'])) {
    $stmt = $connection->prepare("UPDATE pets SET status = ? WHERE id = ?");
    $stmt->execute(array($_POST['new_status'], $_POST['id']));
    $view->message = "Status updated!";
}

// this updates pet information
if (isset($_POST['update_pet'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = isset($_POST['breed']) ? trim($_POST['breed']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $status = $_POST['status'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($name && $species) {
        $photo_url = null;
        // this handles picture upload for updating the pitcutere
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
            $upload_dir = 'Images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // this makes unique filename
            $ext = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
            $photo_url = uniqid('pet_', true) . '.' . $ext;

            if (!move_uploaded_file($_FILES['picture']['tmp_name'], $upload_dir . $photo_url)) {
                $photo_url = null;
            }
        }

        // this updates pet photo if not keeps the old one - admin can update any pet
        if ($photo_url) {
            $stmt = $connection->prepare("UPDATE pets SET name = ?, species = ?, breed = ?, color = ?, status = ?, description = ?, photo_url = ? WHERE id = ?");
            $stmt->execute(array($name, $species, $breed, $color, $status, $description, $photo_url, $id));
        } else {
            $stmt = $connection->prepare("UPDATE pets SET name = ?, species = ?, breed = ?, color = ?, status = ?, description = ? WHERE id = ?");
            $stmt->execute(array($name, $species, $breed, $color, $status, $description, $id));
        }

        header("Location: manage_pets.php?updated=1");
        exit();
    } else {
        $view->error = "Need name and species";
    }
}

// this gets the pet to edit - admin can edit any pet
$view->editing_pet = null;
if (isset($_GET['edit'])) {
    $stmt = $connection->prepare("SELECT * FROM pets WHERE id = ?");
    $stmt->execute(array($_GET['edit']));
    $row = $stmt->fetch();
    if ($row) {
        $view->editing_pet = new PetData($row);
    }
}

// this handles pagination for manage pets page
$pets_per_page  = 10;
$current_page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset         = ($current_page - 1) * $pets_per_page;
$total_pets     = count($petsDataSet->fetchAllPets());
$total_pages    = ceil($total_pets / $pets_per_page);

$view->pets        = $petsDataSet->fetchAllPetsPaginated($pets_per_page, $offset);
$view->current_page  = $current_page;
$view->total_pages   = $total_pages;

require_once('Views/manage_pets.phtml');
?>
