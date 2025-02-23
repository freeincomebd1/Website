<?php
// config.php: Database Connection
$conn = new mysqli("sql102.infinityfree.com", "if0_38356551", "x5AdReNBSY", "if0_38356551_freeincome");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?><!DOCTYPE html><html>
<head>
    <title>Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form method="POST" action="signup.php">
            Username: <input type="text" name="username" required>
            Password: <input type="password" name="password" required>
            Referral Code (Optional): <input type="text" name="referral_code">
            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html><?php
// signup.php: User Registration
include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $referral_code = substr(md5(time()), 0, 6);
    $referred_by = $_POST['referral_code'] ?? null;
    
    $sql = "INSERT INTO users (username, password, balance, referral_code, referred_by) VALUES ('$username', '$password', 0, '$referral_code', '$referred_by')";
    if ($conn->query($sql) === TRUE) {
        if ($referred_by) {
            $conn->query("UPDATE users SET balance = balance + 5 WHERE referral_code='$referred_by'");
        }
        echo "Registration successful. <a href='login.php'>Login here</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?><?php
// dashboard.php: User Dashboard
session_start();
include 'config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['user'];
$result = $conn->query("SELECT balance, referral_code FROM users WHERE username='$username'");
$row = $result->fetch_assoc();
$balance = $row['balance'];
$referral_code = $row['referral_code'];
?><h2>Welcome, <?php echo $_SESSION['user']; ?>!</h2>
<p>Your balance: $<?php echo number_format($balance, 2); ?></p>
<p>Your Referral Link: <b>https://yoursite.com/signup.php?ref=<?php echo $referral_code; ?></b></p>
<a href="ads.php" class="button">View Ads & Earn</a>
<a href="withdraw.php" class="button">Withdraw Money</a>
<a href="logout.php" class="button">Logout</a><?php
// withdraw.php: Request Payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && $balance >= 200) {
    $method = $_POST['method'];
    $number = $_POST['number'];
    $conn->query("INSERT INTO withdrawals (username, amount, method, number, status) VALUES ('$username', $balance, '$method', '$number', 'Pending')");
    $conn->query("UPDATE users SET balance = 0 WHERE username='$username'");
    echo "Withdrawal request submitted successfully!";
}
?><h2>Withdraw Money</h2>
<p>Minimum Withdraw: 200 Taka</p>
<?php if ($balance >= 200) { ?>
<form method="POST">
    <select name="method" required>
        <option value="Bkash">Bkash</option>
        <option value="Nagad">Nagad</option>
        <option value="Rocket">Rocket</option>
    </select>
    Mobile Number: <input type="text" name="number" required>
    <button type="submit">Withdraw</button>
</form>
<?php } else { echo "You need at least 200 Taka to withdraw."; } ?><?php
// logout.php: Destroy Session
session_destroy();
header("Location: login.php");
exit;
?>