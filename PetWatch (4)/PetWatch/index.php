<?php
session_start();

// this is the mian homepage controller
// it just makes a view object and then shows the index.phtml or the view part
$view = new stdClass();
$view->pageTitle = 'PetWatch - Find Missing Pets';

require_once('Views/index.phtml');
?>

