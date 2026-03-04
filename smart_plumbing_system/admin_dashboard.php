<?php
session_start();
include 'config.php';

// Only allow logged-in admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];
$message = "";

// --- Assign plumber to a request ---
if(isset($_POST['assign_plumber'])){
    $request_id = $_POST['request_id'];
    $plumber_id = $_POST['plumber_id'];

    $stmt = $conn->prepare("UPDATE service_requests SET plumber_id=?, status='accepted' WHERE id=?");
    $stmt->bind_param("ii", $plumber_id, $request_id);
    if($stmt->execute()){
        $conn->query("UPDATE plumbers SET availability_status='busy' WHERE id=$plumber_id");
        $_SESSION['flash_success'] = "Plumber assigned successfully!";
    } else {
        $_SESSION['flash_error'] = "Failed to assign plumber.";
    }

    header("Location: admin_dashboard.php");
    exit();
}

// --- Manage Plumber Levels ---
if(isset($_POST['add_level'])){
    $level_name = $_POST['level_name'];
    $description = $_POST['description'];
    $base_price = $_POST['base_price'];

    $stmt = $conn->prepare("INSERT INTO plumber_levels (level_name, description, base_price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $level_name, $description, $base_price);
    if($stmt->execute()){
        $_SESSION['flash_success'] = "Plumber level added successfully!";
    }
}

// Delete plumber level
if(isset($_GET['delete_level'])){
    $id = $_GET['delete_level'];
    $conn->query("DELETE FROM plumber_levels WHERE id=$id");
    $_SESSION['flash_success'] = "Plumber level deleted!";
    header("Location: admin_dashboard.php");
    exit();
}

// --- Fetch data ---
$levels = $conn->query("SELECT * FROM plumber_levels");
$users = $conn->query("SELECT * FROM users");

// Fetch service requests with plumber and payment info
$requests = $conn->query("
    SELECT sr.id, sr.issue_description, sr.status AS request_status, sr.level_id, sr.location, sr.created_at,
           c.full_name AS customer_name,
           pm.id AS plumber_table_id, p.full_name AS plumber_name,
           pl.level_name,
           pay.payment_status
    FROM service_requests sr
    LEFT JOIN users c ON sr.customer_id = c.id
    LEFT JOIN plumbers pm ON sr.plumber_id = pm.id
    LEFT JOIN users p ON pm.user_id = p.id
    LEFT JOIN plumber_levels pl ON sr.level_id = pl.id
    LEFT JOIN payments pay ON sr.id = pay.request_id
    ORDER BY sr.created_at DESC
");

// Fetch payments
$payments = $conn->query("
    SELECT pay.*, sr.issue_description, u.full_name AS customer_name 
    FROM payments pay
    LEFT JOIN service_requests sr ON pay.request_id = sr.id
    LEFT JOIN users u ON sr.customer_id = u.id
    ORDER BY pay.payment_date DESC
");

// --- Fetch ratings ---
$ratings = $conn->query("
    SELECT r.*, u.full_name AS customer_name, p.full_name AS plumber_name, sr.issue_description
    FROM ratings r
    LEFT JOIN service_requests sr ON r.request_id = sr.id
    LEFT JOIN users u ON sr.customer_id = u.id
    LEFT JOIN plumbers pl ON sr.plumber_id = pl.id
    LEFT JOIN users p ON pl.user_id = p.id
    ORDER BY r.created_at DESC
");

// --- Fetch plumber rating summary ---
$plumber_summary = $conn->query("
    SELECT u.full_name AS plumber_name, 
           COUNT(r.id) AS total_ratings,
           ROUND(AVG(r.rating),2) AS avg_rating
    FROM plumbers pl
    JOIN users u ON pl.user_id = u.id
    LEFT JOIN service_requests sr ON sr.plumber_id = pl.id
    LEFT JOIN ratings r ON r.request_id = sr.id
    GROUP BY pl.id
    ORDER BY avg_rating DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Smart Plumbing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {font-family:'Segoe UI'; margin:0; background:#f4f4f4;}
        header {background:#6f42c1; color:white; padding:15px; text-align:center; position: relative;}
        h2 {color:#6f42c1; margin-top:30px;}
        .container {width:95%; max-width:1200px; margin:20px auto; background:white; padding:20px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.2);}
        table {width:100%; border-collapse:collapse; margin-top:15px;}
        table th, table td {border:1px solid #ccc; padding:8px; text-align:center;}
        table th {background:#6f42c1; color:white;}
        input, textarea, select, button {padding:8px; margin:5px 0; border-radius:5px; border:1px solid #ccc;}
        button {background:#6f42c1; color:white; border:none; cursor:pointer; transition:.3s;}
        button:hover {background:#5936a2;}
        .success {background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;}
        .error {background:#f8d7da;color:#721c24;padding:10px;border-radius:6px;margin-bottom:15px;}
        a.logout {position:absolute; top:15px; right:15px; color:white; text-decoration:none; font-weight:bold;}
        a.logout:hover {text-decoration:underline;}
        .delete {background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;}
        .delete:hover {opacity:0.8;}
        form.assign-form {display:flex; gap:5px; justify-content:center;}
        form.assign-form select {width:160px;}
        h1 span{font-weight: normal; font-size:18px;}
    </style>
</head>
<body>
<header>
    <h1>Welcome, <?php echo $name; ?> (Admin) <span>Dashboard</span></h1>
    <a class="logout" href="logout.php">Logout</a>
</header>

<div class="container">

<!-- FLASH MESSAGES -->
<?php 
if(isset($_SESSION['flash_success'])){
    echo "<div class='success' id='flash-message'>{$_SESSION['flash_success']}</div>";
    unset($_SESSION['flash_success']);
} elseif(isset($_SESSION['flash_error'])){
    echo "<div class='error' id='flash-message'>{$_SESSION['flash_error']}</div>";
    unset($_SESSION['flash_error']);
}
?>

<h2>Manage Plumber Levels</h2>
<form method="POST">
    <input type="text" name="level_name" placeholder="Level Name" required>
    <input type="text" name="description" placeholder="Description">
    <input type="number" step="0.01" name="base_price" placeholder="Base Price" required>
    <button type="submit" name="add_level">Add Level</button>
</form>

<table>
<tr>
<th>ID</th>
<th>Level Name</th>
<th>Description</th>
<th>Base Price</th>
<th>Action</th>
</tr>
<?php while($level = $levels->fetch_assoc()){ ?>
<tr>
<td><?php echo $level['id']; ?></td>
<td><?php echo $level['level_name']; ?></td>
<td><?php echo $level['description']; ?></td>
<td><?php echo $level['base_price']; ?></td>
<td><a class="delete" href="?delete_level=<?php echo $level['id']; ?>" onclick="return confirm('Delete this level?')">Delete</a></td>
</tr>
<?php } ?>
</table>

<h2>All Users</h2>
<table>
<tr>
<th>ID</th>
<th>Full Name</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
</tr>
<?php while($user = $users->fetch_assoc()){ ?>
<tr>
<td><?php echo $user['id']; ?></td>
<td><?php echo $user['full_name']; ?></td>
<td><?php echo $user['email']; ?></td>
<td><?php echo $user['phone']; ?></td>
<td><?php echo ucfirst($user['role']); ?></td>
</tr>
<?php } ?>
</table>

<h2>All Service Requests</h2>
<table>
<tr>
<th>ID</th>
<th>Customer</th>
<th>Plumber</th>
<th>Level</th>
<th>Issue</th>
<th>Location</th>
<th>Status</th>
<th>Created At</th>
<th>Assign</th>
</tr>
<?php while($row = $requests->fetch_assoc()){ ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['customer_name']; ?></td>
<td><?php echo $row['plumber_name'] ?? 'Unassigned'; ?></td>
<td><?php echo $row['level_name'] ?? '-'; ?></td>
<td><?php echo $row['issue_description']; ?></td>
<td><?php echo $row['location']; ?></td>
<td>
<?php 
if($row['request_status']=='completed' && $row['payment_status']=='paid'){
    echo "Completed";
} elseif($row['request_status']=='accepted' && $row['payment_status']=='paid'){
    echo "Paid - awaiting plumber completion";
} elseif($row['request_status']=='accepted' && !$row['payment_status']){
    echo "Accepted - awaiting customer payment";
} else {
    echo ucfirst($row['request_status']);
}
?>
</td>
<td><?php echo $row['created_at']; ?></td>
<td>
<?php
if($row['request_status']=='pending'){ 
    if(!empty($row['level_id'])){
        $level_id = $row['level_id'];
        $plumbers_query = $conn->query("
            SELECT pl.id AS plumber_id, u.full_name
            FROM plumbers pl
            JOIN users u ON pl.user_id = u.id
            WHERE pl.level_id = $level_id AND pl.availability_status='available'
        ");
    } else {
        $plumbers_query = false;
    }

    if($plumbers_query && $plumbers_query->num_rows>0){ ?>
        <form method="POST" class="assign-form">
            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
            <select name="plumber_id" required>
                <?php while($pl = $plumbers_query->fetch_assoc()){ ?>
                    <option value="<?php echo $pl['plumber_id']; ?>"><?php echo $pl['full_name']; ?></option>
                <?php } ?>
            </select>
            <button type="submit" name="assign_plumber">Assign</button>
        </form>
    <?php } else {
        echo "No available plumber / level not selected";
    }
} else { 
    echo $row['plumber_name'] ?? '-';
}
?>
</td>
</tr>
<?php } ?>
</table>

<h2>All Payments</h2>
<table>
<tr>
<th>ID</th>
<th>Customer</th>
<th>Service</th>
<th>Amount</th>
<th>Payment Method</th>
<th>Status</th>
<th>Date</th>
</tr>
<?php while($pay = $payments->fetch_assoc()){ ?>
<tr>
<td><?php echo $pay['id']; ?></td>
<td><?php echo $pay['customer_name']; ?></td>
<td><?php echo $pay['issue_description']; ?></td>
<td><?php echo $pay['amount']; ?></td>
<td><?php echo $pay['payment_method']; ?></td>
<td><?php echo ucfirst($pay['payment_status']); ?></td>
<td><?php echo $pay['payment_date']; ?></td>
</tr>
<?php } ?>
</table>

<h2>All Ratings</h2>
<table>
<tr>
<th>ID</th>
<th>Customer</th>
<th>Plumber</th>
<th>Service</th>
<th>Rating</th>
<th>Comment</th>
<th>Rated At</th>
</tr>
<?php while($rate = $ratings->fetch_assoc()){ ?>
<tr>
<td><?php echo $rate['id']; ?></td>
<td><?php echo $rate['customer_name'] ?? '-'; ?></td>
<td><?php echo $rate['plumber_name'] ?? '-'; ?></td>
<td><?php echo $rate['issue_description'] ?? '-'; ?></td>
<td><?php echo $rate['rating']; ?> / 5</td>
<td><?php echo $rate['comment'] ?? '-'; ?></td>
<td><?php echo $rate['created_at']; ?></td>
</tr>
<?php } ?>
</table>

<!-- NEW SECTION: Plumber Ratings Summary -->
<h2>Plumber Ratings Summary</h2>
<table>
<tr>
<th>Plumber</th>
<th>Total Ratings</th>
<th>Average Rating</th>
</tr>
<?php while($plum = $plumber_summary->fetch_assoc()){ ?>
<tr>
<td><?php echo $plum['plumber_name']; ?></td>
<td><?php echo $plum['total_ratings']; ?></td>
<td><?php echo $plum['avg_rating'] ?? '0'; ?> / 5</td>
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
