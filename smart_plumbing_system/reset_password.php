<?php
include 'config.php';

$message = "";
$messageType = "";

if(isset($_GET['token'])){
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT email, expires_at, used FROM password_resets WHERE token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows==1){
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $expires = $row['expires_at'];
        $used = $row['used'];

        if($used=="1" || strtotime($expires)<time()){
            $message = "This reset link is invalid or expired!";
            $messageType = "error";
            $valid = false;
        } else {
            $valid = true;
        }

    } else {
        $message = "Invalid reset link!";
        $messageType = "error";
        $valid = false;
    }

} else {
    $message = "No reset token provided!";
    $messageType = "error";
    $valid = false;
}

// Handle new password submission
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['new_password'])){
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Update user's password
    $update = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $update->bind_param("ss",$new_password,$email);
    $update->execute();

    // Mark token as used
    $mark = $conn->prepare("UPDATE password_resets SET used='1' WHERE token=?");
    $mark->bind_param("s",$token);
    $mark->execute();

    $message = "Password successfully updated! You can now <a href='login.php'>login</a>.";
    $messageType = "success";
    $valid = false;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family:'Segoe UI'; background:linear-gradient(135deg,#0f2027,#203a43,#2c5364); display:flex; justify-content:center; align-items:center; height:100vh; margin:0;}
        .card {background:white; padding:40px; width:400px; border-radius:15px; text-align:center;}
        h2 {color:#2c5364;}
        input, button {width:100%; padding:12px; margin:10px 0; border-radius:8px; border:1px solid #ccc;}
        button {border:none; background:#2c5364; color:white; cursor:pointer; transition:.3s;}
        button:hover {background:#203a43;}
        .success {background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;font-size:14px;}
        .error {background:#f8d7da;color:#721c24;padding:10px;border-radius:6px;margin-bottom:15px;font-size:14px;}
        a {color:#2c5364;text-decoration:none;}
    </style>
</head>
<body>
<div class="card">
    <h2>Reset Password</h2>

    <?php if($message!=""){ echo "<div class='$messageType'>$message</div>"; } ?>

    <?php if(isset($valid) && $valid){ ?>
        <form method="POST">
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <button type="submit">Set New Password</button>
        </form>
    <?php } ?>

</div>
</body>
</html>