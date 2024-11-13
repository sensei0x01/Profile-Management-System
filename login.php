<?php 
session_start();
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();}
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        // Validate email
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $email = htmlspecialchars($_POST["email"]); //encode special chars
            echo $_email;
            $pass = hash('sha256', $_POST['password'].strrev($_POST['password'])); //hash password
            $sql = "SELECT * FROM `users` WHERE email='{$email}' and password='{$pass}'";
            $result = mysqli_query($conn,$sql);
            if (mysqli_num_rows($result) > 0) {
                session_regenerate_id(true); // Prevent session fixation
                $row = mysqli_fetch_assoc($result);
                $_SESSION['username'] = $row['username'];
                $_SESSION['verified'] = $row['verified'];
                $_SESSION["email"] = $row['email'];
                $_SESSION["fname"] = $row['fname'];
                $_SESSION["lname"] = $row['lname'];
                $_SESSION["uimg"] = $row['uimg'];
                logLoginAction($_SESSION['username'], $_SESSION["email"], 'Success');

                if ($_SESSION['verified']) {
                    header('Location: index.php');
                    exit();
                }else {
                    define('ALLOW_ACCESS', true);
                    define('LOGIN', true);
                    require_once('mail.php');
                    header('Location: verify.php');
                    exit();
                }
            }else {
                $_SESSION['error_message'] = 'Login failed. Invalid email or password';
                logLoginAction('-', $_POST['email'], 'Failure');
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = 'Invalid email format';
            header('Location: login.php');
            exit();
        }
    }else {
        $_SESSION['error_message'] = 'Please enter your email and password';
        header('Location: login.php');
        exit();
        }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="assets/login.css"> 
</head>
<body>
    <div class="login-wrapper">
        <div class="header">Profile Management System</div>

        <!-- Login form -->
        <form action="login.php" method="post" class="login-form">
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <?php 
            // Display error message if login_error is set
            if (isset($_SESSION['error_message'])) {
                echo "<div class='error-message'>" .$_SESSION['error_message']. "</div><br>"; 
                unset($_SESSION['error_message']);
            }
            ?>
            <input type="submit" value="Log In">
            <hr>
        </form>
        <!-- Sign Up -->
        <a role="button" class="signup-button" href="signup.php">Sign Up</a>
    </div>
</body>
</html>
