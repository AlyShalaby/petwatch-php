<?php
session_start();

$view = new stdClass();
$view->pageTitle = 'Pet Map';
$view->isLoggedIn = isset($_SESSION['user_id']);
// this makes ajax token for map calls
if (!isset($_SESSION['ajax_token'])) {
    $_SESSION['ajax_token'] = bin2hex(random_bytes(32));
}
$view->ajaxToken = $_SESSION['ajax_token'];
$view->selectedPetId = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : 0;


require_once('Views/map.phtml');
?>
