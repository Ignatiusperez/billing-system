const PORT = process.env.PORT || 3000;
// CAPTURE USER'S IP ADDRESS
document.addEventListener('DOMContentLoaded', function() {
    fetch('https://api.ipify.org?format=json')
        .then(response => response.json())
        .then(data => {
            document.getElementById('ipAddress').value = data.ip;
        })
        .catch(error => {
            console.error('Error getting IP:', error);
            document.getElementById('ipAddress').value = "<?php echo $_SERVER['REMOTE_ADDR']; ?>";
        });
});

// LOGIN FORM VALIDATION
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const phone = document.getElementById('phone').value;
    if (!phone.startsWith('254') || phone.length !== 12) {
        e.preventDefault();
        alert('Please enter a valid Kenyan phone number starting with 254 and 12 digits long.');
    }
});

// PACKAGE SELECTION & M-PESA INIT (on payment.php)
$(document).ready(function() {
    $('.package-btn').click(function() {
        const package = $(this).data('package');
        const amount = $(this).data('amount');
        const phone = '<?php echo isset($_SESSION['phone']) ? $_SESSION['phone'] : ""; ?>'; 
        const ip = '<?php echo isset($_SESSION['ip_address']) ? $_SESSION['ip_address'] : ""; ?>'; 

        $.ajax({
            type: "POST",
            url: "initiate_mpesa.php",
            data: { package: package, amount: amount, phone: phone, ip_address: ip },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    alert("✅ Check your phone! M-Pesa STK Push has been sent. Enter your M-Pesa PIN to complete payment.");
                } else {
                    alert("❌ Error: " + response.message);
                }
            },
            error: function() {
                alert("💥 Request failed. Please try again.");
            }
        });
    });
});
