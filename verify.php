<?php
session_start();
if (isset($_SESSION['username']) and isset($_SESSION["verified"])) {
    if ($_SESSION["verified"]) {
        header('Location: index.php');
        exit();
    }
}else {
    header('Location: login.php');
    exit();
}
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (preg_match(OTP_POLICY, $_POST["code"])) {
        $sql = "SELECT * FROM `users` WHERE username='".$_SESSION['username']."'";
        $result = mysqli_query($conn,$sql); 
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if ($row['otp'] === $_POST["code"]) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION["verified"] = true ; 
                $sql = "UPDATE `users` SET verified = '".$_SESSION["verified"]."' WHERE username = '".$_SESSION['username']."'";
                $updateResult = mysqli_query($conn, $sql);
                if ($updateResult) {
                    $sql = "UPDATE `users` SET otp = NULL WHERE username = '".$_SESSION['username']."'";
                    $updateResult = mysqli_query($conn, $sql);
                    if (isset($_SESSION['update_data'])) {
                        updateData($conn);
                        header('Location: profile.php');
                        exit();
                    }
                    header('Location: index.php');
                    exit();
                } 
            }else {
                $_SESSION['error_message'] = 'Wrong code';
                header('Location: verify.php');
                exit();
            }
        }
    } else {
        $_SESSION['error_message'] = 'Invalid code. code must be 6 digits number';
        header('Location: verify.php');
        exit();
    }
}

if (isset($_GET['send']) && $_GET['send']=== 'true' ) {
    define('ALLOW_ACCESS', true);
    define('VERIFY', true);
    require_once('mail.php');
    exit();
}

if (isset($_SESSION['update_data'])) {
    $verification_email = substr($_SESSION['update_data']['email'],9,-1);
}else {
    $verification_email = $_SESSION['email'];
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management System</title>
    <link rel="stylesheet" href="assets/verify.css">
</head>
<body>

    <div class="container">
        <div class="header">Profile Management System</div>
        <div class="title">Enter Security Code</div>
        <div class="instructions">Please check your emails for a message with your code. Your code is 6 numbers long.</div>

        <!-- Form with POST method -->
        <form action="" method="POST">
            <input type="text" name="code" class="input-box" placeholder="Enter code" required>
            <div class="instructions">We sent your code to:<br> <b><?php echo  $verification_email;?></b></div>
            <?php 
            // Display error message if login_error is set
            if (isset($_SESSION['error_message'])) {
                echo "<div class='error-message'>" .$_SESSION['error_message']. "</div><br>"; 
                unset($_SESSION['error_message']);
            }
            ?>
            <div class="buttons">
                <a href="logout.php" class="button cancel">Cancel</a>
                <button type="submit" class="button continue">Continue</button>
            </div>
        </form>

        <a href="verify.php?send=true" class="link">Didn't get a code?</a>
    </div>
    
</body>
</html>


