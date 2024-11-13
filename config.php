<?php
$db_server = 'localhost';
$db_user = 'root';
$db_pass =  '' ;
$db_name = 'task1' ; 

try {
    $conn = new mysqli( $db_server, 
                    $db_user, 
                    $db_pass,
                    $db_name);
    if ($conn) {
        //echo "The connection has been established :)";
    }
} catch (mysqli_sql_exception) {
    exit("Couldn't connect to the server :(");
}

define('OTP_POLICY',"/^\d{6}$/");
define('PASSWORD_POLICY','/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/');
define('USERNAME_POLICY','/^(?!.*[_]{2})[a-zA-Z][a-zA-Z0-9_]{1,18}[a-zA-Z0-9]$/');
define('NAME_POLICY','/^(?! )[A-Za-z\'\- ]{3,50}(?<! )$/');

define('MAILHOST','smtp.gmail.com');
define('USERNAME','testawly@gmail.com');
define('PASSWORD','oaeyignifsgpjjsf');
define('SEND_FROM','no-reply@naderly.io');
define('SEND_FROM_NAME','Naderly.io');

// Ensure the logs directory exists; if not, create it
if (!is_dir('logs')) {
    mkdir('logs', 0777, true);
}

define('LOG_FILE_PATH','logs/action.log');

//functiong to generate otp
function generateNumericOTP($n) { 
    $generator = "1357902468"; 
    $result = ""; 
    for ($i = 1; $i <= $n; $i++) { 
        $result .= substr($generator, (rand()%(strlen($generator))), 1); 
    } 
    return $result; 
} 

//create a log of signup
function logSignUpAction($username, $email) {
    
    // Create a timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    
    // Format the log entry
    $log_entry = "[$timestamp] [SIGNUP] Username: $username | Email: $email | IP Address: $ipAddress\n";
    
    // Output log
    file_put_contents(LOG_FILE_PATH, $log_entry, FILE_APPEND);
}

//create a log for login
function logLoginAction($username, $email, $status) {
    
    // Create a timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    
    // Get session ID
    $sessionId = session_id();
    
    // Format the log entry
    $log_entry = "[$timestamp] [LOGIN] Username: $username | Email: $email | Status: $status | IP Address: $ipAddress | Session ID: $sessionId\n";
    
    // Output log
    file_put_contents(LOG_FILE_PATH, $log_entry, FILE_APPEND);
}

// create a log for logout
function logLogoutAction($username, $email) {

    // Create a timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    
    // Get session ID
    $sessionId = session_id();
    
    // Format the log entry
    $log_entry = "[$timestamp] [LOGOUT] Username: $username | Email: $email | IP Address: $ipAddress | Session ID: $sessionId\n";
    
    // output log
    file_put_contents(LOG_FILE_PATH, $log_entry, FILE_APPEND);
    
}

//create a log for updates
function logUpdateAction($username, $updates) {
    // Create a timestamp for the log entry
    $timestamp = date('Y-m-d H:i:s');
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    
    // Get session ID
    $sessionId = session_id();
    
    // Format the update details
    $updateDetails = "";
    foreach ($updates as $field => $newValue) {
        $updateDetails .= "$field:$_SESSION[$field] | ";
    }
    $updateDetails = rtrim($updateDetails, " | "); // Remove last pipe
    
    // Format the log entry
    if (isset($updateDetails['email'])) {
        $log_entry = "[$timestamp] [UPDATE] Username: $username | old_email: $username | IP Address: $ipAddress | Session ID: $sessionId | Changes: $updateDetails\n";
    }else{
        $email = $_SESSION['email'];
        $log_entry = "[$timestamp] [UPDATE] Username: $username | Email: $email | IP Address: $ipAddress | Session ID: $sessionId | Changes: $updateDetails\n";
    }
    
    // Write the log entry to the file
    file_put_contents(LOG_FILE_PATH, $log_entry, FILE_APPEND);
}


function updateData($conn){
    if(isset($_SESSION['update_data']) && $_SESSION['verified']){
        $modifiedFields = $_SESSION['update_data'];
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
            unset($_SESSION['update_data']);
            header('Location: profile.php');
            exit();
        } else {
            unlink(substr($_SESSION['update_data']['uimg']),8,-1);
            $_SESSION['error_message'] = "Field to update data";
            header('Location: profile.php');
            exit();
        }
    }
}




?>