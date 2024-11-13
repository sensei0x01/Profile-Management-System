<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}else {
    require('config.php');
    logLogoutAction($_SESSION['username'], $_SESSION['email']);
    session_unset();
    session_destroy();
    session_start(); 
    session_regenerate_id(true);
    header('Location: login.php');
    exit();
}




?>