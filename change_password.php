<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";
$back = $_SESSION["role"] === "manager" ? "manager.php" : "customer.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current = $_POST["current_password"] ?? "";
    $new = $_POST["new_password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || !password_verify($current, $row["password"])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must contain at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed, $_SESSION["user_id"]);
        $update->execute();
        $success = "Password changed successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<div class="auth-wrap">
    <div class="auth-card small-card">
        <p class="eyebrow">ACCOUNT SETTINGS</p>
        <h1>Change Password</h1>

        <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <form method="POST">
            <label>Current Password</label>
            <input type="password" name="current_password" required>

            <label>New Password</label>
            <input type="password" name="new_password" required>

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>

            <button class="primary-btn full" type="submit">Update Password</button>
        </form>

        <a class="home-link" href="<?php echo $back; ?>">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
