<?php
require "config.php";

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "manager") {
        header("Location: manager.php");
    } else {
        header("Location: customer.php");
    }
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, username, email, phone, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["phone"] = $user["phone"];
            $_SESSION["role"] = $user["role"];

            header("Location: " . ($user["role"] === "manager" ? "manager.php" : "customer.php"));
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrandStay | Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-topbar">
    <a href="home.php" class="brand">Grand<span>Stay</span></a>
    <div>
        <a href="home.php">Home</a>
        <a href="register.php">Register</a>
    </div>
</div>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-heading">
            <p class="eyebrow">WELCOME BACK</p>
            <h1>Login to your account</h1>
            <p>Manage your hotel booking from your dashboard.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>

            <button type="submit" class="primary-btn full">Login</button>
        </form>

        <p class="auth-link">Don't have an account? <a href="register.php">Register now</a></p>
        <a class="home-link" href="home.php">← Back to Homepage</a>
    </div>
</div>

</body>
</html>
