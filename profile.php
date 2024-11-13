<?php
session_start();
include('config.php');
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}else {
    if (!$_SESSION['verified']) {
        header('Location: verify.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    //validate profile picture
    if(isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK && !isset($_SESSION['update_data'])) {
        $file_tmp = $_FILES['img']['tmp_name'];
        $file_name = $_FILES['img']['name'];
        $file_size = $_FILES['img']['size'];
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));


        //maximum accepted size
        $max_file_size = 2 * 1024 * 1024; // 2MB
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        // Accepted extensions
        if ($file_size > $max_file_size) {
            $_SESSION['error_message'] = "File size exceeds the maximum limit of 2MB.";
            header('Location: profile.php');
            exit();
        }
        if(in_array($file_ext, $allowed_extensions)) {
            // Generate a unique filename to avoid conflicts and xss attacks
            $new_filename = uniqid('', true) . '.' . $file_ext;

            // Move the uploaded file to the desired directory
            $upload_path = 'images/' . $new_filename;
            if(move_uploaded_file($file_tmp, $upload_path)) {
                // Update the user's profile photo in the database
                $modifiedFields['uimg'] = "uimg = '$upload_path'";
            }   
        }else {
            $_SESSION['error_message'] = "Invalid file format. Only JPG, JPEG, PNG, and GIF files are allowed.";
            header('Location: profile.php');
            exit();
        }
    }

    if ($_POST["fname"] !== $_SESSION["fname"] && !isset($_SESSION['update_data'])) {
        if (!preg_match(NAME_POLICY, $_POST["fname"])) {
            $_SESSION['error_message'] = "Invalid First name";
            header('Location: profile.php');
            exit();
        }else {
            $new_fname = ucfirst(htmlspecialchars($_POST["fname"]));
            $modifiedFields['fname'] = "fname = '$new_fname'";
        }
    }

    if ($_POST["lname"] !== $_SESSION["lname"] && !isset($_SESSION['update_data'])) {
        if (!preg_match(NAME_POLICY, $_POST["lname"])) {
            $_SESSION['error_message'] = "Invalid Sirname";
            header('Location: profile.php');
            exit();
        }else {
            $new_lname = ucfirst(htmlspecialchars($_POST["lname"]));
            $modifiedFields['lname'] = "lname = '$new_lname'";
        }
    }
    if ($_POST["email"] !== $_SESSION["email"] && !isset($_SESSION['update_data'])) {
        $new_email = htmlspecialchars($_POST["email"]);
        if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)){
            $_SESSION['error_message'] = "Invalid email format";
            header('Location: profile.php');
            exit();
        }else{
            $sql = "SELECT * FROM `users` WHERE email='{$new_email}'";
            $result = mysqli_query($conn,$sql);
            if (mysqli_num_rows($result) > 0) {
                $_SESSION['error_message'] = "This email is already used";
                header('Location: profile.php');
                exit();
            }else {
                $new_email = htmlspecialchars($_POST["email"]);
                $modifiedFields['email'] = "email = '$new_email'";
            }
        }
    }

    if (!empty($modifiedFields)) {
        if (isset($modifiedFields['email']) && !isset($_SESSION['update_data'])) {
            $_SESSION['update_data'] = $modifiedFields;
            $_SESSION['verified'] = false;
            define('ALLOW_ACCESS', true);
            define('PROFILE', true);
            require_once('mail.php');
            header('Location: verify.php');
            exit();
        }
        
        
        // Construct the SQL query directly
        $imploded = implode(", ", array_map(function ($key, $value) {return "$value";}, array_keys($modifiedFields),$modifiedFields));
        
        $sql = "UPDATE users SET " . $imploded . " WHERE username = '".$_SESSION['username']."'";
        if (mysqli_query($conn,$sql) === TRUE) {
            $sql = "SELECT * FROM `users` WHERE username='".$_SESSION['username']."'";
            $result = mysqli_query($conn,$sql);
            $row = mysqli_fetch_assoc($result);
            $_SESSION['verified'] = $row['verified'];
            $_SESSION["email"] = $row['email'];
            $_SESSION["fname"] = $row['fname'];
            $_SESSION["lname"] = $row['lname'];
            $_SESSION["uimg"] = $row['uimg'];
            $_SESSION['error_message'] = "Profile updated successfully.";
            logUpdateAction($_SESSION['username'], $modifiedFields);
            header('Location: profile.php');
            exit();
        } else {
            unlink($upload_path);
            $_SESSION['error_message'] = "Field to update data";
            header('Location: profile.php');
            exit();
        }
    }else {
        $_SESSION['error_message'] = "No data has been updated";
        header('Location: profile.php');
        exit();
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="assets/profile.css">
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
        <form action="profile.php" method="post" class="facebook-form" enctype="multipart/form-data">
            <div class="profile-pic-container">
            <input type="file" name="img" id="upload" accept="image/*" />
                <label for="upload">
                    <img id="profile-pic" src="<?php 
            if(file_exists($_SESSION['uimg'])) {
                echo $_SESSION['uimg'];
            }
            else{
                echo 'images/profile.jpg';
            }
            ?>" alt="Profile Picture">
                </label>
            </div>

            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" name="fname" id="fname" value="<?php echo $_SESSION['fname']; ?>" required>
            </div>

            <div class="form-group">
                <label for="lname">Surname</label>
                <input type="text" name="lname" id="lname" value="<?php echo $_SESSION['lname']; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo $_SESSION['email']; ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo $_SESSION['username']; ?>" readonly>
            </div>
            <?php
            if (isset($_SESSION['error_message'])) {
                echo "<div class='error-message'>" .$_SESSION['error_message']. "</div><br>"; 
                unset($_SESSION['error_message']);
            }
            ?>
            <div class="form-group">
                <input type="submit" value="Update" class="submit-btn">
            </div>
        </form>  
    </div>
</body>
</html>
