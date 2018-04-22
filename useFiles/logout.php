<?php
session_start();

if ($_SESSION["HAS_LOGGED_IN"]) {
    session_unset();
    echo "You have succesfully logged out!";
    sleep(1);
}

header('Location: login.php');
?>