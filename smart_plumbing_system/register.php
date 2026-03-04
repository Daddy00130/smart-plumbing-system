<?php
include 'config.php';

$message = "";
$messageType = "";

// Fetch plumber levels to populate dropdown
$levels_result = $conn->query("SELECT id, level_name FROM plumber_levels ORDER BY level_name ASC");
$plumber_levels = [];
if($levels_result){
    while($row = $levels_result->fetch_assoc()){
        $plumber_levels[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Additional fields for plumbers
    $experience_years = isset($_POST['experience_years']) ? intval($_POST['experience_years']) : null;
    $education_level = isset($_POST['education_level']) ? trim($_POST['education_level']) : null;
    $level_id = isset($_POST['level_id']) ? intval($_POST['level_id']) : null;

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Email already registered!";
        $messageType = "error";
    } else {

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $email, $phone, $password, $role);

        if ($stmt->execute()) {

            // Get the new user ID
            $new_user_id = $conn->insert_id;

            // If plumber, insert into plumbers table
            if ($role == "plumber") {
                $insertPlumber = $conn->prepare("
                    INSERT INTO plumbers (user_id, level_id, experience_years, education_level, availability_status)
                    VALUES (?, ?, ?, ?, 'available')
                ");
                $insertPlumber->bind_param("iiis", $new_user_id, $level_id, $experience_years, $education_level);
                $insertPlumber->execute();
            }

            $message = "Registration successful! You can now login.";
            $messageType = "success";

        } else {
            $message = "Something went wrong!";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account - Smart Plumbing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-card {
            background: #ffffff;
            width: 420px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            text-align: center;
            animation: slideIn 0.6s ease;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        h2 { margin-bottom: 25px; color: #1e3c72; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; transition: 0.3s; }
        input:focus, select:focus { border-color: #1e3c72; outline: none; box-shadow: 0 0 6px rgba(30,60,114,0.4); }
        button { width: 100%; padding: 12px; margin-top: 15px; border: none; border-radius: 8px; background: #1e3c72; color: white; font-size: 16px; cursor: pointer; transition: 0.3s; }
        button:hover { background: #16325c; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .links { margin-top: 20px; font-size: 14px; }
        .links a { color: #1e3c72; text-decoration: none; font-weight: bold; }
        .links a:hover { text-decoration: underline; }
        @media (max-width: 480px) { .register-card { width: 90%; padding: 25px; } }
    </style>
</head>
<body>

<div class="register-card">

    <h2>Create Your Account</h2>

    <?php
    if ($message != "") {
        if ($messageType == "success") {
            echo "<div class='success'>$message</div>";
        } else {
            echo "<div class='error'>$message</div>";
        }
    }
    ?>

    <form method="POST">

        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Create Password" required>

        <select name="role" id="roleSelect" required>
            <option value="">-- Select Role --</option>
            <option value="customer">Register as Customer</option>
            <option value="plumber">Register as Plumber</option>
        </select>

        <!-- Plumber only fields -->
        <div id="plumberFields" style="display:none;">
            <input type="number" name="experience_years" placeholder="Years of Experience" min="0">
            <input type="text" name="education_level" placeholder="Education Level">
            <select name="level_id">
                <option value="">Select Plumber Level</option>
                <?php foreach($plumber_levels as $level){ ?>
                    <option value="<?php echo $level['id']; ?>"><?php echo $level['level_name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <button type="submit">Create Account</button>

    </form>

    <div class="links">
        Already have an account? <a href="login.php">Login</a><br><br>
        <a href="index.php">Back to Home</a>
    </div>

</div>

<script>
    // Show plumber fields only if role is plumber
    document.getElementById('roleSelect').addEventListener('change', function(){
        var plumberFields = document.getElementById('plumberFields');
        if(this.value === 'plumber'){
            plumberFields.style.display = 'block';
        } else {
            plumberFields.style.display = 'none';
        }
    });
</script>

</body>
</html>