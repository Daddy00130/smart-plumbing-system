<?php
session_start();
session_destroy();
//redirect to homepage
header("Location: index.php");
exit();
?>