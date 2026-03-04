<?php
include 'config.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate token
        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", strtotime('+15 minutes'));

        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $email, $token, $expires);
        $insert->execute();

        // Display reset link (simulate email)
        $reset_link = "http://localhost/smart_plumbing_system/reset_password.php?token=$token";
        $message = "Password reset link (valid 15 min): <br><a href='$reset_link'>$reset_link</a>";
        $messageType = "success";

    } else {
        $message = "Email not found!";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
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
    <h2>Forgot Password</h2>

    <?php if($message!=""){ echo "<div class='$messageType'>$message</div>"; } ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <a href="login.php">Back to Login</a>
</div>
</body>
</html>