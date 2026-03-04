<?php
session_start();
include 'config.php';

// Only allow logged-in customers
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// ---------- BOOK SERVICE ----------
if(isset($_POST['book_service'])){
    $issue_description = $_POST['issue_description'];
    $location = $_POST['location'];
    $plumber_level_id = $_POST['plumber_level'];

    $stmt = $conn->prepare("INSERT INTO service_requests (customer_id, issue_description, location, level_id, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issi", $user_id, $issue_description, $location, $plumber_level_id);

    if($stmt->execute()){
        $_SESSION['flash_success'] = "Service request submitted successfully! Waiting for admin to assign a plumber.";
    } else {
        $_SESSION['flash_error'] = "Failed to submit service request.";
    }

    header("Location: customer_dashboard.php");
    exit();
}

// ---------- PAY NOW ----------
if(isset($_POST['pay_now'])){
    $request_id = $_POST['request_id'];
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];

    $stmt = $conn->prepare("INSERT INTO payments (request_id, amount, payment_method, payment_status, payment_date) VALUES (?, ?, ?, 'paid', NOW())");
    $stmt->bind_param("ids", $request_id, $amount, $method);

    if($stmt->execute()){
        $conn->query("UPDATE service_requests SET status='awaiting_completion' WHERE id=$request_id");
        $_SESSION['flash_success'] = "Payment successful! The plumber can now complete the job.";
    } else {
        $_SESSION['flash_error'] = "Payment failed. Try again.";
    }

    header("Location: customer_dashboard.php");
    exit();
}

// ---------- SUBMIT RATING ----------
if(isset($_POST['submit_rating'])){
    $request_id = $_POST['request_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $customer_id = $_SESSION['user_id'];

    $get = $conn->query("SELECT plumber_id FROM service_requests WHERE id=$request_id");
    $data = $get->fetch_assoc();
    $plumber_id = $data['plumber_id'];

    $stmt = $conn->prepare("INSERT INTO ratings (request_id, plumber_id, customer_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $request_id, $plumber_id, $customer_id, $rating, $comment);

    if($stmt->execute()){
        $_SESSION['flash_success'] = "Rating submitted successfully!";
    } else {
        $_SESSION['flash_error'] = "Failed to submit rating.";
    }

    header("Location: customer_dashboard.php");
    exit();
}

// Fetch plumber levels
$levels = $conn->query("SELECT * FROM plumber_levels");

// Fetch customer's service requests
$requests = $conn->query("
    SELECT sr.*, p.full_name as plumber_name, pl.level_name, pl.base_price
    FROM service_requests sr 
    LEFT JOIN plumbers pm ON sr.plumber_id = pm.id
    LEFT JOIN users p ON pm.user_id = p.id
    LEFT JOIN plumber_levels pl ON pl.id = sr.level_id
    WHERE sr.customer_id = $user_id
    ORDER BY sr.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Customer Dashboard - Smart Plumbing</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {font-family:'Segoe UI'; margin:0; background:#f4f4f4;}
header {background:#2c5364; color:white; padding:15px; text-align:center;}
h2 {color:#2c5364;}
.container {width:90%; max-width:1100px; margin:20px auto; background:white; padding:20px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.2);}
form input, form select, form textarea, form button {width:100%; padding:8px; margin:6px 0; border-radius:6px; border:1px solid #ccc; font-size:14px;}
form button {background:#2c5364; color:white; border:none; cursor:pointer;}
form button:hover {background:#203a43;}
table {width:100%; border-collapse:collapse; margin-top:20px;}
table th, table td {border:1px solid #ccc; padding:8px; text-align:center;}
table th {background:#2c5364; color:white;}
.success {background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;}
.error {background:#f8d7da;color:#721c24;padding:10px;border-radius:6px;margin-bottom:15px;}
a.logout {float:right; color:white; text-decoration:none; font-weight:bold;}
</style>
</head>
<body>

<header>
<h1>Welcome, <?php echo $name; ?></h1>
<a class="logout" href="logout.php">Logout</a>
</header>

<div class="container">

<?php 
if(isset($_SESSION['flash_success'])){
    echo "<div class='success'>{$_SESSION['flash_success']}</div>";
    unset($_SESSION['flash_success']);
} elseif(isset($_SESSION['flash_error'])){
    echo "<div class='error'>{$_SESSION['flash_error']}</div>";
    unset($_SESSION['flash_error']);
}
?>

<h2>Book a Plumber</h2>
<form method="POST">
    <textarea name="issue_description" placeholder="Explain your plumbing issue" required></textarea>
    <input type="text" name="location" placeholder="Your location" required>
    <select name="plumber_level" required>
        <?php while($level = $levels->fetch_assoc()){ ?>
            <option value="<?php echo $level['id']; ?>">
                <?php echo $level['level_name']; ?> (Base price: KES <?php echo $level['base_price']; ?>)
            </option>
        <?php } ?>
    </select>
    <button type="submit" name="book_service">Book Service</button>
</form>

<h2>Your Service Requests</h2>
<table>
<tr>
<th>Plumber</th>
<th>Level</th>
<th>Issue</th>
<th>Location</th>
<th>Status</th>
<th>Payment</th>
<th>Notification</th>
<th>Rate Plumber</th>
</tr>

<?php while($row = $requests->fetch_assoc()){ ?>
<tr>
<td><?php echo $row['plumber_name'] ?? 'Unassigned'; ?></td>
<td><?php echo $row['level_name'] ?? '-'; ?></td>
<td><?php echo $row['issue_description']; ?></td>
<td><?php echo $row['location']; ?></td>
<td><?php echo ucfirst($row['status']); ?></td>

<td>
<?php
$payment_check = $conn->query("SELECT * FROM payments WHERE request_id={$row['id']} LIMIT 1");
$payment = $payment_check->fetch_assoc();

if($row['status']=='accepted' && !$payment){ ?>
    <form method="POST">
        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="amount" value="<?php echo $row['base_price'] ?? 1000; ?>">
        <select name="payment_method" required>
            <option value="cash">Cash</option>
            <option value="mpesa">Mpesa</option>
            <option value="card">Card</option>
        </select>
        <button type="submit" name="pay_now">Pay Now</button>
    </form>
<?php } elseif($payment && $payment['payment_status']=='paid'){
    echo "Paid";
} else {
    echo "-";
}
?>
</td>

<td>
<?php
if($row['status']=='accepted' && !$payment){
    echo "Your plumber has been assigned. Please make payment.";
} elseif($row['status']=='awaiting_completion' && $payment){
    echo "The plumber is completing your job.";
} elseif($row['status']=='completed'){
    echo "Job completed.";
} else {
    echo "-";
}
?>
</td>

<td>
<?php
$rating_check = $conn->query("SELECT id FROM ratings WHERE request_id={$row['id']} LIMIT 1");
$rating_exists = $rating_check->num_rows > 0;

if($row['status']=='completed' && !$rating_exists){ ?>
    <form method="POST">
        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
        <select name="rating" required>
            <option value="">Rate</option>
            <option value="5">⭐⭐⭐⭐⭐ (5)</option>
            <option value="4">⭐⭐⭐⭐ (4)</option>
            <option value="3">⭐⭐⭐ (3)</option>
            <option value="2">⭐⭐ (2)</option>
            <option value="1">⭐ (1)</option>
        </select>
        <textarea name="comment" placeholder="Optional comment"></textarea>
        <button type="submit" name="submit_rating">Submit</button>
    </form>
<?php } elseif($rating_exists){
    echo "Already Rated";
} else {
    echo "-";
}
?>
</td>

</tr>
<?php } ?>
</table>

</div>
</body>
</html>