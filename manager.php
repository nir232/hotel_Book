<?php
require "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "manager") {
    header("Location: index.php");
    exit();
}

$message = "";
$error = "";

if (isset($_POST["booking_action"])) {
    $booking_id = (int)$_POST["booking_id"];
    $action = $_POST["booking_action"];

    if ($action === "confirm") {
        $stmt = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id=? AND status='pending'");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $message = "Booking request accepted.";
    } elseif ($action === "delete") {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $message = "Booking request deleted.";
    }
}

if (isset($_POST["delete_booking"])) {
    $booking_id = (int)$_POST["booking_id"];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $message = "Reservation deleted.";
}

if (isset($_POST["add_room"])) {
    $number = trim($_POST["room_number"]);
    $type = trim($_POST["room_type"]);
    $price = (float)$_POST["price"];
    $capacity = (int)$_POST["capacity"];
    $status = $_POST["status"];
    $description = trim($_POST["description"]);

    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price, capacity, status, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $number, $type, $price, $capacity, $status, $description);

    if ($stmt->execute()) {
        $message = "Room added successfully.";
    } else {
        $error = "Could not add room. Room number may already exist.";
    }
}

if (isset($_POST["update_room"])) {
    $id = (int)$_POST["room_id"];
    $number = trim($_POST["room_number"]);
    $type = trim($_POST["room_type"]);
    $price = (float)$_POST["price"];
    $capacity = (int)$_POST["capacity"];
    $status = $_POST["status"];
    $description = trim($_POST["description"]);

    $stmt = $conn->prepare(
        "UPDATE rooms SET room_number=?, room_type=?, price=?, capacity=?, status=?, description=? WHERE id=?"
    );
    $stmt->bind_param("ssdissi", $number, $type, $price, $capacity, $status, $description, $id);

    if ($stmt->execute()) {
        $message = "Room updated successfully.";
    } else {
        $error = "Could not update room.";
    }
}

if (isset($_POST["delete_room"])) {
    $id = (int)$_POST["room_id"];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "Room deleted.";
}

$pending = $conn->query(
    "SELECT b.*, u.full_name, u.email, r.room_number, r.room_type, r.price
     FROM bookings b
     JOIN users u ON b.customer_id=u.id
     JOIN rooms r ON b.room_id=r.id
     WHERE b.status='pending'
     ORDER BY b.id DESC"
);

$confirmed = $conn->query(
    "SELECT b.*, u.full_name, u.email, r.room_number, r.room_type, r.price
     FROM bookings b
     JOIN users u ON b.customer_id=u.id
     JOIN rooms r ON b.room_id=r.id
     WHERE b.status='confirmed'
     ORDER BY b.id DESC"
);

$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number");

$stats = [
    "requests" => $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='pending'")->fetch_assoc()["c"],
    "confirmed" => $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='confirmed'")->fetch_assoc()["c"],
    "rooms" => $conn->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()["c"],
    "available" => $conn->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch_assoc()["c"]
];

$userStmt = $conn->prepare("SELECT full_name, username, email, phone FROM users WHERE id=?");
$userStmt->bind_param("i", $_SESSION["user_id"]);
$userStmt->execute();
$profile = $userStmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manager Dashboard | GrandStay</title>
<link rel="stylesheet" href="dashboard.css">
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="side-brand">Grand<span>Stay</span></div>
    <button class="side-link active" onclick="showSection('dashboard', this)">⌂ Dashboard</button>
    <button class="side-link" onclick="showSection('requests', this)">▣ Booking Requests</button>
    <button class="side-link" onclick="showSection('confirmed', this)">▤ Booked Reservations</button>
    <button class="side-link" onclick="showSection('rooms', this)">▥ Rooms</button>
    <button class="side-link" onclick="showSection('profile', this)">◉ Profile</button>
    <button class="side-link" onclick="showSection('settings', this)">⚙ Settings</button>
    <a class="side-link logout" href="logout.php">↪ Logout</a>
</aside>

<main class="main">
    <header class="topbar">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <div>
            <span class="welcome">Manager Panel</span>
            <strong><?php echo htmlspecialchars($_SESSION["full_name"]); ?></strong>
        </div>
        <div class="top-user">Hotel Manager</div>
    </header>

    <?php if ($message): ?><div class="alert success page-alert"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error page-alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section id="dashboard" class="section active">
        <div class="section-heading">
            <p class="eyebrow">MANAGEMENT PANEL</p>
            <h1>Dashboard</h1>
            <p>Monitor reservations and hotel rooms.</p>
        </div>

        <div class="stats">
            <div class="stat-card"><span>Booking Requests</span><strong><?php echo $stats["requests"]; ?></strong></div>
            <div class="stat-card"><span>Confirmed</span><strong><?php echo $stats["confirmed"]; ?></strong></div>
            <div class="stat-card"><span>Total Rooms</span><strong><?php echo $stats["rooms"]; ?></strong></div>
            <div class="stat-card"><span>Available Rooms</span><strong><?php echo $stats["available"]; ?></strong></div>
        </div>

        <div class="dashboard-card">
            <h2>Manager Actions</h2>
            <p>Review new booking requests, manage confirmed reservations, and maintain your room list.</p>
        </div>
    </section>

    <section id="requests" class="section">
        <div class="section-heading">
            <p class="eyebrow">BOOKINGS</p>
            <h1>Booking Requests</h1>
        </div>
        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Email</th>
                        <th>Room</th>
                        <th>Dates</th>
                        <th>Guests</th>
                        <th>Request</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($pending->num_rows === 0): ?>
                        <tr><td colspan="7" class="empty">No pending booking requests.</td></tr>
                    <?php else: ?>
                        <?php while ($b = $pending->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b["full_name"]); ?></td>
                            <td><?php echo htmlspecialchars($b["email"]); ?></td>
                            <td>Room <?php echo htmlspecialchars($b["room_number"]); ?><br><small><?php echo htmlspecialchars($b["room_type"]); ?></small></td>
                            <td><?php echo htmlspecialchars($b["check_in"]); ?><br>to <?php echo htmlspecialchars($b["check_out"]); ?></td>
                            <td><?php echo (int)$b["guests"]; ?></td>
                            <td><?php echo htmlspecialchars($b["special_request"] ?: "—"); ?></td>
                            <td>
                                <form method="POST" class="action-row">
                                    <input type="hidden" name="booking_id" value="<?php echo $b["id"]; ?>">
                                    <button name="booking_action" value="confirm" class="success-btn">Accept</button>
                                    <button name="booking_action" value="delete" class="danger-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="confirmed" class="section">
        <div class="section-heading">
            <p class="eyebrow">BOOKINGS</p>
            <h1>Booked Reservations</h1>
        </div>
        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Email</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($confirmed->num_rows === 0): ?>
                        <tr><td colspan="7" class="empty">No confirmed reservations.</td></tr>
                    <?php else: ?>
                        <?php while ($b = $confirmed->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b["full_name"]); ?></td>
                            <td><?php echo htmlspecialchars($b["email"]); ?></td>
                            <td>Room <?php echo htmlspecialchars($b["room_number"]); ?><br><small><?php echo htmlspecialchars($b["room_type"]); ?></small></td>
                            <td><?php echo htmlspecialchars($b["check_in"]); ?></td>
                            <td><?php echo htmlspecialchars($b["check_out"]); ?></td>
                            <td><?php echo (int)$b["guests"]; ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="booking_id" value="<?php echo $b["id"]; ?>">
                                    <button name="delete_booking" class="danger-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="rooms" class="section">
        <div class="section-heading">
            <p class="eyebrow">HOTEL INVENTORY</p>
            <h1>Room Management</h1>
        </div>

        <div class="dashboard-card">
            <h2>Add Room</h2>
            <form method="POST" class="form-grid">
                <div>
                    <label>Room Number</label>
                    <input name="room_number" required>
                </div>
                <div>
                    <label>Room Type</label>
                    <input name="room_type" placeholder="Deluxe Room" required>
                </div>
                <div>
                    <label>Price / Night</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div>
                    <label>Capacity</label>
                    <input type="number" min="1" name="capacity" required>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="full-width">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="full-width">
                    <button class="primary-btn" name="add_room">Add Room</button>
                </div>
            </form>
        </div>

        <div class="table-card room-table">
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($r = $rooms->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r["room_number"]); ?></td>
                        <td><?php echo htmlspecialchars($r["room_type"]); ?></td>
                        <td>$<?php echo number_format($r["price"], 2); ?></td>
                        <td><?php echo (int)$r["capacity"]; ?></td>
                        <td><span class="status <?php echo $r["status"]; ?>"><?php echo ucfirst($r["status"]); ?></span></td>
                        <td><?php echo htmlspecialchars($r["description"] ?: "—"); ?></td>
                        <td>
                            <details>
                                <summary class="edit-link">Edit</summary>
                                <form method="POST" class="edit-form">
                                    <input type="hidden" name="room_id" value="<?php echo $r["id"]; ?>">
                                    <input name="room_number" value="<?php echo htmlspecialchars($r["room_number"]); ?>" required>
                                    <input name="room_type" value="<?php echo htmlspecialchars($r["room_type"]); ?>" required>
                                    <input type="number" step="0.01" name="price" value="<?php echo $r["price"]; ?>" required>
                                    <input type="number" min="1" name="capacity" value="<?php echo $r["capacity"]; ?>" required>
                                    <select name="status">
                                        <option value="available" <?php echo $r["status"]==="available" ? "selected" : ""; ?>>Available</option>
                                        <option value="maintenance" <?php echo $r["status"]==="maintenance" ? "selected" : ""; ?>>Maintenance</option>
                                    </select>
                                    <textarea name="description"><?php echo htmlspecialchars($r["description"]); ?></textarea>
                                    <button class="primary-btn" name="update_room">Update</button>
                                </form>
                            </details>
                            <form method="POST" class="inline delete-room">
                                <input type="hidden" name="room_id" value="<?php echo $r["id"]; ?>">
                                <button class="danger-btn" name="delete_room">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="profile" class="section">
        <div class="section-heading">
            <p class="eyebrow">ACCOUNT</p>
            <h1>Profile</h1>
        </div>
        <div class="profile-card">
            <div class="avatar"><?php echo strtoupper(substr($profile["full_name"], 0, 1)); ?></div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($profile["full_name"]); ?></h2>
                <p><b>Username:</b> <?php echo htmlspecialchars($profile["username"]); ?></p>
                <p><b>Email:</b> <?php echo htmlspecialchars($profile["email"]); ?></p>
                <p><b>Phone:</b> <?php echo htmlspecialchars($profile["phone"]); ?></p>
                <p><b>Role:</b> Hotel Manager</p>
            </div>
        </div>
    </section>

    <section id="settings" class="section">
        <div class="section-heading">
            <p class="eyebrow">ACCOUNT</p>
            <h1>Settings</h1>
        </div>
        <div class="dashboard-card">
            <h2>Password</h2>
            <p>Update the manager account password.</p>
            <a href="change_password.php" class="primary-btn">Change Password</a>
        </div>
    </section>
</main>

<script src="dashboard.js"></script>
</body>
</html>
