<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mico Internet Services</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Mico Internet Services</h1>
        <p>Please enter your phone number to continue</p>
        
        <form id="loginForm" action="process_login.php" method="POST">
            <input type="hidden" name="ip_address" id="ipAddress" value="">
            <input type="tel" id="phone" name="phone" placeholder="Enter Phone Number (e.g., 254712345678)" required pattern="25[4-9][0-9]{8}">
            <button type="submit" class="glow-on-hover">Login</button>
        </form>
    </div>
    
    <script src="script.js"></script> 
</body>
</html>
