<?php
session_start();
include 'config.php';

// Only allow logged-in plumbers
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'plumber'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Get plumber ID
$getPlumber = $conn->query("SELECT id FROM plumbers WHERE user_id=$user_id");
$plumber = $getPlumber->fetch_assoc();
$plumber_id = $plumber['id'];

// -------- ACCEPT JOB --------
if(isset($_POST['accept_job'])){
    $request_id = $_POST['request_id'];

    $conn->query("UPDATE service_requests 
                  SET status='accepted' 
                  WHERE id=$request_id AND plumber_id=$plumber_id");

    $conn->query("UPDATE plumbers 
                  SET availability_status='busy' 
                  WHERE id=$plumber_id");

    $_SESSION['flash_success'] = "Job accepted successfully!";
    header("Location: plumber_dashboard.php");
    exit();
}

// -------- REJECT JOB --------
if(isset($_POST['reject_job'])){
    $request_id = $_POST['request_id'];

    $conn->query("UPDATE service_requests 
                  SET plumber_id=NULL, status='pending' 
                  WHERE id=$request_id AND plumber_id=$plumber_id");

    $conn->query("UPDATE plumbers 
                  SET availability_status='available' 
                  WHERE id=$plumber_id");

    $_SESSION['flash_success'] = "Job rejected.";
    header("Location: plumber_dashboard.php");
    exit();
}

// -------- MARK AS COMPLETED --------
if(isset($_POST['complete_job'])){
    $request_id = $_POST['request_id'];

    $conn->query("UPDATE service_requests 
                  SET status='completed' 
                  WHERE id=$request_id AND plumber_id=$plumber_id");

    $conn->query("UPDATE plumbers 
                  SET availability_status='available' 
                  WHERE id=$plumber_id");

    $_SESSION['flash_success'] = "Job marked as completed.";
    header("Location: plumber_dashboard.php");
    exit();
}

// Fetch assigned jobs
$jobs = $conn->query("
    SELECT sr.*, u.full_name AS customer_name
    FROM service_requests sr
    JOIN users u ON sr.customer_id = u.id
    WHERE sr.plumber_id=$plumber_id
    ORDER BY sr.created_at DESC
");

// Calculate total earnings (only paid jobs)
$earnings = $conn->query("
    SELECT SUM(amount) AS total_earnings
    FROM payments
    WHERE request_id IN (
        SELECT id FROM service_requests WHERE plumber_id=$plumber_id
    ) AND payment_status='paid'
");

$total = $earnings->fetch_assoc()['total_earnings'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Plumber Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {font-family:'Segoe UI'; background:#f4f4f4; margin:0;}
header {background:#007bff; color:white; padding:15px; text-align:center; position:relative;}
.container {width:95%; max-width:1000px; margin:20px auto; background:white; padding:20px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.2);}
table {width:100%; border-collapse:collapse; margin-top:15px;}
table th, table td {border:1px solid #ccc; padding:8px; text-align:center;}
table th {background:#007bff; color:white;}
button {padding:6px 10px; border:none; border-radius:5px; cursor:pointer; margin:2px;}
.accept {background:green; color:white;}
.reject {background:red; color:white;}
.complete {background:orange; color:white;}
.success {background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px;}
.earnings {background:#e9f5ff; padding:15px; border-radius:8px; margin-bottom:20px; font-size:18px;}
a.logout {position:absolute; top:15px; right:15px; color:white; text-decoration:none;}
</style>
</head>
<body>

<header>
<h2>Welcome, <?php echo $name; ?> (Plumber)</h2>
<a class="logout" href="logout.php">Logout</a>
</header>

<div class="container">

<!-- FLASH MESSAGE -->
<?php 
if(isset($_SESSION['flash_success'])){
    echo "<div class='success' id='flash-message'>{$_SESSION['flash_success']}</div>";
    unset($_SESSION['flash_success']);
}
?>

<div class="earnings">
<strong>Total Earnings: </strong> KES <?php echo number_format($total,2); ?>
</div>

<h3>My Assigned Jobs</h3>

<table>
<tr>
<th>ID</th>
<th>Customer</th>
<th>Issue</th>
<th>Location</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = $jobs->fetch_assoc()){ 
    $payment_check = $conn->query("SELECT * FROM payments WHERE request_id={$row['id']} LIMIT 1");
    $payment = $payment_check->fetch_assoc();
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['customer_name']; ?></td>
<td><?php echo $row['issue_description']; ?></td>
<td><?php echo $row['location']; ?></td>
<td>
<?php 
// Show status according to workflow
if($row['status']=='pending'){
    echo "Pending - awaiting admin assignment";
} elseif($row['status']=='accepted' && !$payment){
    echo "Accepted - waiting for customer payment";
} elseif($payment && $payment['payment_status']=='paid' && $row['status']!='completed'){
    echo "Payment received - ready to complete";
} elseif($row['status']=='completed'){
    echo "Completed";
} else {
    echo ucfirst($row['status']);
}
?>
</td>
<td>
<?php 
if($row['status']=='pending'){ ?>
    <form method="POST" style="display:inline;">
    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
    <button type="submit" name="accept_job" class="accept">Accept</button>
    <button type="submit" name="reject_job" class="reject">Reject</button>
    </form>
<?php } elseif($payment && $payment['payment_status']=='paid' && $row['status']!='completed'){ ?>
    <form method="POST">
        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
        <button type="submit" name="complete_job" class="complete">Mark Completed</button>
    </form>
<?php } elseif($row['status']=='accepted' && !$payment){ 
    echo "Waiting for customer payment";
} elseif($row['status']=='completed'){ 
    echo "Completed";
} ?>
</td>
</tr>
<?php } ?>
</table>

</div>

<script>
  // Hide flash messages after 4 seconds
  const flash = document.getElementById('flash-message');
  if(flash){
    setTimeout(() => {
      flash.style.display = 'none';
    }, 4000);
  }
</script>

</body>
</html>