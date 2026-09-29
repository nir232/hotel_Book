<?php
require "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: index.php");
    exit();
}

                            
                               
                            

$message = "";
$error = "";


if (isset($_POST["book_room"])) {
    $room_id = (int)($_POST["room_id"] ?? 0);
    $check_in = $_POST["check_in"] ?? "";
    $check_out = $_POST["check_out"] ?? "";
    $guests = (int)($_POST["guests"] ?? 0);
    $special_request = trim($_POST["special_request"] ?? "");

    

    if (!$room_id || !$check_in || !$check_out || !$guests) {
        $error = "Please complete all booking fields.";
    } elseif ($check_out <= $check_in) {
        $error = "Check-out date must be after check-in date.";
    } else {
        $roomStmt = $conn->prepare("SELECT capacity, status FROM rooms WHERE id = ?");
        $roomStmt->bind_param("i", $room_id);
        $roomStmt->execute();
        $room = $roomStmt->get_result()->fetch_assoc();

        if (!$room || $room["status"] !== "available") {
            $error = "This room is not available.";
        } elseif ($guests > $room["capacity"]) {
            $error = "Number of guests exceeds the room capacity.";
        } else {
            $overlap = $conn->prepare(
                "SELECT id FROM bookings
                 WHERE room_id = ?
                 AND status IN ('pending','confirmed')
                 AND check_in < ?
                 AND check_out > ?"
            );
            $overlap->bind_param("iss", $room_id, $check_out, $check_in);
            $overlap->execute();

            if ($overlap->get_result()->num_rows > 0) {
                $error = "This room is already requested/booked for those dates.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO bookings
                    (customer_id, room_id, check_in, check_out, guests, special_request, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')"
                );
                $stmt->bind_param(
                    "iissis",
                    $_SESSION["user_id"], $room_id, $check_in, $check_out, $guests, $special_request
                );

                if ($stmt->execute()) {
                    $message = "Booking request submitted successfully.";
                } else {
                    $error = "Could not submit booking request.";
                }
            }
        }
    }
}

if (isset($_POST["cancel_booking"])) {
    $booking_id = (int)$_POST["booking_id"];

    $stmt = $conn->prepare(
        "UPDATE bookings SET status = 'cancelled'
         WHERE id = ? AND customer_id = ? AND status = 'pending'"
    );
    $stmt->bind_param("ii", $booking_id, $_SESSION["user_id"]);
    $stmt->execute();
    $message = "Booking request cancelled.";
}

$rooms = $conn->query("SELECT * FROM rooms WHERE status = 'available' ORDER BY room_number");

$statsStmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(status='pending') AS pending_count,
        SUM(status='confirmed') AS confirmed_count
     FROM bookings WHERE customer_id = ?"
);
$statsStmt->bind_param("i", $_SESSION["user_id"]);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

$availableRooms = $conn->query("SELECT COUNT(*) AS c FROM rooms WHERE status='available'")->fetch_assoc()["c"];

$bookingStmt = $conn->prepare(
    "SELECT b.*, r.room_number, r.room_type, r.price
     FROM bookings b
     JOIN rooms r ON b.room_id = r.id
     WHERE b.customer_id = ?
     ORDER BY b.id DESC"
);
$bookingStmt->bind_param("i", $_SESSION["user_id"]);
$bookingStmt->execute();
$bookings = $bookingStmt->get_result();

$userStmt = $conn->prepare("SELECT full_name, username, email, phone FROM users WHERE id = ?");
$userStmt->bind_param("i", $_SESSION["user_id"]);
$userStmt->execute();
$profile = $userStmt->get_result()->fetch_assoc();




?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Dashboard | GrandStay</title>
<link rel="stylesheet" href="dashboard.css">
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="side-brand">Grand<span>Stay</span></div>
    <button class="side-link active" onclick="showSection('dashboard', this)">⌂ Dashboard</button>
    <button class="side-link" onclick="showSection('book', this)">▣ Book Room</button>
    <button class="side-link" onclick="showSection('bookings', this)">▤ My Bookings</button>
    <button class="side-link" onclick="showSection('profile', this)">◉ Profile</button>
    <button class="side-link" onclick="showSection('settings', this)">⚙ Settings</button>
    <a class="side-link logout" href="logout.php">↪ Logout</a>
</aside>

<main class="main">
    <header class="topbar">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <div>
            <span class="welcome">Welcome,</span>
            <strong><?php echo htmlspecialchars($_SESSION["full_name"]); ?></strong>
        </div>
        <div class="top-user"><?php echo htmlspecialchars($_SESSION["username"]); ?></div>
    </header>

    <?php if ($message): ?><div class="alert success page-alert"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error page-alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section id="dashboard" class="section active">
        <div class="section-heading">
            <p class="eyebrow">CUSTOMER PANEL</p>
            <h1>Dashboard</h1>
            <p>Manage your hotel reservations from here.</p>
        </div>

        <div class="stats">
            <div class="stat-card"><span>Total Bookings</span><strong><?php echo (int)($stats["total"] ?? 0); ?></strong></div>
            <div class="stat-card"><span>Pending</span><strong><?php echo (int)($stats["pending_count"] ?? 0); ?></strong></div>
            <div class="stat-card"><span>Confirmed</span><strong><?php echo (int)($stats["confirmed_count"] ?? 0); ?></strong></div>
            <div class="stat-card"><span>Available Rooms</span><strong><?php echo (int)$availableRooms; ?></strong></div>
        </div>

        <div class="dashboard-card">
            <h2>Quick Booking</h2>
            <p>Need a room? Open <b>Book Room</b> from the sidebar and submit a request.</p>
        </div>
    </section>

    <section id="book" class="section">
        <div class="section-heading">
            <p class="eyebrow">RESERVATION</p>
            <h1>Book a Room</h1>
        </div>

        <div class="dashboard-card">
            <form method="POST" class="form-grid">
                <div class="full-width">
                    <label>Room</label>
                    <select name="room_id" required>
                        <option value="">Select a room</option>
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <option value="<?php echo $room["id"]; ?>">
                                Room <?php echo htmlspecialchars($room["room_number"]); ?> -
                                <?php echo htmlspecialchars($room["room_type"]); ?> -
                                $<?php echo number_format($room["price"], 2); ?> / night -
                                Capacity <?php echo $room["capacity"]; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label>Check-in</label>
                    <input type="date" name="check_in" required>
                </div>
                <div>
                    <label>Check-out</label>
                    <input type="date" name="check_out" required>
                </div>
                <div>
                    <label>Guests</label>
                    <input type="number" name="guests" min="1" required>
                </div>
                <div class="full-width">
                    <label>Special Request</label>
                    <textarea name="special_request" rows="4" placeholder="Optional request"></textarea>
                </div>
                <div class="full-width">
                    <button type="submit" name="book_room" class="primary-btn">Submit Booking Request</button>
                </div>
            </form>
        </div>
    </section>

    <section id="bookings" class="section">
        <div class="section-heading">
            <p class="eyebrow">RESERVATIONS</p>
            <h1>My Bookings</h1>
        </div>

       

        
       

        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Stay Duration</th>
                            <th>Total Cost</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($bookings->num_rows === 0): ?>
                        <tr><td colspan="9" class="empty">No bookings found.</td></tr>
                    <?php else: ?>
                        <?php while ($b = $bookings->fetch_assoc()): ?>
                            <?php
                             $checkIn = new DateTime($b["check_in"]);
                             $checkOut = new DateTime($b["check_out"]);

                             $nights = $checkIn->diff($checkOut)->days;
                            $totalCost = $nights * (float)$b["price"];
                            ?>
                            

                            

                              

    
                            <tr>
                                <td>Room <?php echo htmlspecialchars($b["room_number"]); ?></td>
                                <td><?php echo htmlspecialchars($b["room_type"]); ?></td>
                                <td><?php echo htmlspecialchars($b["check_in"]); ?></td>
                                <td><?php echo htmlspecialchars($b["check_out"]); ?></td>
                                <td><?php echo (int)$b["guests"]; ?></td>
                                <td><span class="status <?php echo htmlspecialchars($b["status"]); ?>"><?php echo ucfirst($b["status"]); ?></span></td>
                                <td><?php echo $nights ?></td>
                                <td><?php echo $totalCost ?></td>
                                <td>
                                    <?php if ($b["status"] === "pending"): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $b["id"]; ?>">
                                            <button name="cancel_booking" class="danger-btn">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                
                                
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
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
                <p><b>Role:</b> Customer</p>
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
            <p>Update your account password.</p>
            <a href="change_password.php" class="primary-btn">Change Password</a>
        </div>
    </section>
</main>

<script src="dashboard.js"></script>
</body>
</html>
