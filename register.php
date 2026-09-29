<?php
require "config.php";

if (isset($_SESSION["user_id"])) {
    header("Location: customer.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($full_name === "" || $username === "" || $email === "" || $phone === "" || $password === "") {
        $error = "Please complete all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must contain at least 6 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->bind_param("sssss", $full_name, $username, $email, $phone, $hashed);

            if ($stmt->execute()) {
                $success = "Registration successful. You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrandStay | Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-topbar">
    <a href="home.php" class="brand">Grand<span>Stay</span></a>
    <div>
        <a href="home.php">Home</a>
        <a href="index.php">Login</a>
    </div>
</div>

<div class="auth-wrap">
    <div class="auth-card register-card">
        <div class="auth-heading">
            <p class="eyebrow">JOIN GRANDSTAY</p>
            <h1>Create an account</h1>
            <p>Register as a guest and start requesting rooms.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div>
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone" required>
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div>
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="primary-btn full">Create Account</button>
        </form>

        <p class="auth-link">Already have an account? <a href="index.php">Login</a></p>
        <a class="home-link" href="home.php">← Back to Homepage</a>
    </div>
</div>

</body>
</html>
