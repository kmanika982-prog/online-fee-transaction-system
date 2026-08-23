<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.html");
    exit();
}

$fee_id = $_POST['fee_id'] ?? '';
$amount = $_POST['amount'] ?? '';

if (empty($fee_id) || empty($amount)) {
    die("Invalid payment information.");
}

// Validate amount
if (!is_numeric($amount) || $amount <= 0) {
    die("Invalid payment amount.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment Method</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="topbar">

    <div class="menu">☰</div>

    <div class="brand">
        Online Fee Transaction System
    </div>

</header>

<main class="main">

    <h1>Choose Payment Method</h1>

    <p class="muted">
        Select your preferred payment method
    </p>

    <div class="card" style="max-width:600px;margin-top:25px;">

        <form action="backend/process-payment.php" method="POST">

            <input type="hidden"
                   name="fee_id"
                   value="<?php echo htmlspecialchars($fee_id); ?>">

            <input type="hidden"
                   name="amount"
                   value="<?php echo htmlspecialchars($amount); ?>">

            <h3>
                Amount:
                Rs. <?php echo number_format((float)$amount, 2); ?>
            </h3>

            <br>

            <label for="payment_method">
                Payment Method
            </label>

            <select name="payment_method"
                    id="payment_method"
                    required
                    style="width:100%;padding:12px;margin-top:8px;">

                <option value="">
                    -- Select Payment Method --
                </option>

                <option value="eSewa">
                    📱 eSewa
                </option>

                <option value="Khalti">
                    📱 Khalti
                </option>

                <option value="Bank Transfer">
                    🏦 Bank Transfer
                </option>

                <option value="Cash">
                    💵 Cash
                </option>

            </select>

            <br><br>

            <button type="submit" class="btn">
                Pay Now →
            </button>

        </form>

    </div>

</main>

<footer class="footer">

    © 2026 Online Fee Transaction System.
    All rights reserved.

</footer>

</body>
</html>