<?php
session_start();

$view = new stdClass();
$view->pageTitle = 'Login';
$view->message = $_GET['message'] ?? '';

// this handles when user clicks login button
if (isset($_POST['loginbutton'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $view->error = "Please enter both username and password";
    } else {
        require_once('Models/UsersDataSet.php');
        $user = (new UsersDataSet())->fetchUser($username);

        // this checks if password matches
        if ($user && password_verify($password, $user->getPasswordHash())) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();
            $_SESSION['user_type'] = $user->getType();
            // this sends admin to manage page, regular user to browse page
            header("Location: " . ($_SESSION['user_type'] == 'admin' ? 'manage_pets.php' : 'browse.php'));
            exit();
        } else {
            $view->error = $user ? "Invalid password" : "User not found";
        }
    }
}

require_once('Views/login.phtml');
?>
