<?php
require_once('config.php');
error_reporting(0);
session_start();

if (isset($_SESSION['username']) and isset($_SESSION["verified"])) {
    if ($_SESSION["verified"]) {
        header('Location: index.php');
        exit();
    }else {
        header('Location: verify.php');
        exit();
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST["fname"]) || empty($_POST["lname"]) || empty($_POST["email"]) || empty($_POST["username"]) || empty($_POST["password"]) || empty($_POST["confirm_password"])) {
        $_SESSION['error_message'] = "Please enter all fields";
        header('Location: signup.php');
        exit();
    }else {
        if (!preg_match(NAME_POLICY, $_POST["fname"])) {
            $_SESSION['error_message'] = "Invalid First name";
            header('Location: signup.php');
            exit();
        }

        if (!preg_match(NAME_POLICY, $_POST["lname"])) {
            $_SESSION['error_message'] = "Invalid Sirname";
            header('Location: signup.php');
            exit();
        }

        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Invalid email format";
            header('Location: signup.php');
            exit();
        }

        if (!preg_match(USERNAME_POLICY, $_POST['username'])) {
            $_SESSION['error_message'] = "Invalid username format";
            header('Location: signup.php');
            exit();
        }

        if ($_POST["password"] !== $_POST["confirm_password"]) {
            $_SESSION['error_message'] = "Passwords Don't Match";
            header('Location: signup.php');
            exit(); 
        }

        if (!preg_match(PASSWORD_POLICY, $_POST["password"])) {
            $_SESSION['error_message'] = "Password must be at least 8 characters long and include at least one lowercase letter, one uppercase letter, one digit, and one special character (e.g., !, @, #, etc.).";
            header('Location: signup.php');
            exit();
        }
        $fname = ucfirst(htmlspecialchars($_POST["fname"]));
        $lname = ucfirst(htmlspecialchars($_POST["lname"]));
        $email = htmlspecialchars($_POST["email"]);
        $username = htmlspecialchars($_POST["username"]);
        $password = hash('sha256', $_POST['password'].strrev($_POST['password']));
        $verified = false;
        $sql = "SELECT username FROM `users` WHERE username='{$username}'";
        $result = mysqli_query($conn,$sql);
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error_message'] = "This username already exists";
            header('Location: signup.php');
            exit(); 
        }elseif (mysqli_num_rows(mysqli_query($conn,"SELECT email FROM `users` WHERE email='{$email}'"))>0) {
            $_SESSION['error_message'] = "This email already used";
            header('Location: signup.php');
            exit(); 
        }else {
            $otp = generateNumericOTP(6);
            $sql = "INSERT INTO `users` (`username`, `fname`,`lname`, `email`, `password`,`verified`,`otp`) VALUES ('{$username}','{$fname}','{$lname}','{$email}', '{$password}','{$verified}','{$otp}')";
            $insertResult = mysqli_query($conn, $sql);
            if ($insertResult) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION["username"] = $username;
                $_SESSION["verified"] = $verified;
                $_SESSION["email"] = $email;
                $_SESSION["fname"] = $fname;
                $_SESSION["lname"] = $lname;
                $_SESSION["uimg"] = $row['uimg'];
                $_SESSION["otp"] = $otp;
                define('ALLOW_ACCESS', true);
                require_once('mail.php');
                header('Location: verify.php');
                exit();
            } else {
                $_SESSION['error_message'] = "Registration failed. Please try again later";
                header('Location: signup.php');
                exit(); 
            }
        }

}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="assets/signup.css">
</head>
<body>
    <div class="app-logo">Profile Management System</div>
    <!-- Signup Form -->
    <div class="form-container">
        <h2>Create a new account</h2>
        <p>It's quick and easy.</p>
        
        <form action="signup.php" method="post">
            <div class="name-fields">
                <input type="text" name="fname" placeholder="First name" required>
                <input type="text" name="lname" placeholder="Surname" required>
            </div>
            <input type="email" name="email" placeholder="Email address" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <?php             
                if (isset($_SESSION['error_message'])) {
                echo "<div class='error-message'>" .$_SESSION['error_message']. "</div><br>"; 
                unset($_SESSION['error_message']);
            }?>
            <input type="submit" value="Sign Up">
        </form>
        <p class="login-link"><a href="login.php">Already have an account?</a></p>
    </div>
</body>
</html>
