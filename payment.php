<?php
session_start();
if (!isset($_SESSION['phone']) || !isset($_SESSION['ip_address'])) {
    header("Location: index.php");
    exit();
}
$phone = $_SESSION['phone'];
$ip = $_SESSION['ip_address'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mico Internet - Purchase Package</title>
    <link rel="stylesheet" href="style.css">
     <script src="jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Choose Your Internet Package</h1>
        <p>Phone Number: <?php echo htmlspecialchars($phone); ?></p>
         <p>IP Address: <?php echo htmlspecialchars($ip); ?></p>
        
        <div class="packages">
            <button class="package-btn glow-on-hover" data-package="1hour" data-amount="10">1 Hour - Ksh 10</button>
            <button class="package-btn glow-on-hover" data-package="3hours" data-amount="20">3 Hours - Ksh 20</button>
            <button class="package-btn glow-on-hover" data-package="daily" data-amount="40">Daily - Ksh 40</button>
            <button class="package-btn glow-on-hover" data-package="weekly" data-amount="280">Weekly - Ksh 280</button>
            <button class="package-btn glow-on-hover" data-package="monthly" data-amount="730">Monthly - Ksh 730</button>
        </div>
    </div>

	<script src="script.js"></script>
</body>
</html>
