<?php
session_start();

// destroy the session so bassiaclly logs th user out
session_destroy();

// goes back to login page
header("Location: login.php");
exit();
?>