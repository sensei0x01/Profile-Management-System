<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}else {
    if (!$_SESSION['verified']) {
        header('Location: verify.php');
        exit();
    }
    
}






?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="assets/index.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h2>Profile Management System</h2>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Main content area -->
    <div class="content">
        <!-- Profile Section -->
        <div class="profile">
            <img src="<?php 
            if(file_exists($_SESSION['uimg'])) {
                echo $_SESSION['uimg'];
            }
            else{
                echo 'images/profile.jpg';
            }
            ?>" alt="Profile Image">
            <h3><?php echo $_SESSION['fname']." ".$_SESSION['lname'];?></h3> <!-- Replace 'Logged In User' with the actual username -->
        </div>
    </div>
</body>
</html>

