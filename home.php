<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrandStay Hotel | Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-body">

<header class="home-nav">
    <div class="brand">Grand<span>Stay</span></div>
    <nav>
        <a href="home.php">Home</a>
        <a href="#rooms">Rooms</a>
        <a href="#about">About</a>
        <a href="index.php">Login</a>
        <a class="nav-btn" href="register.php">Register</a>
    </nav>
</header>

<section class="hero">
    <div class="hero-content">
        <p class="eyebrow">WELCOME TO GRANDSTAY</p>
        <h1>A comfortable stay<br>starts here.</h1>
        <p>Book your room easily, manage reservations, and enjoy a simple hotel booking experience.</p>
        <div class="hero-actions">
            <a href="register.php" class="primary-btn">Book Your Stay</a>
            <a href="index.php" class="secondary-btn">Login</a>
        </div>
    </div>
</section>

<section class="features" id="rooms">
    <div class="section-title">
        <p class="eyebrow">OUR SERVICES</p>
        <h2>Everything you need for your stay</h2>
    </div>

    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">🛏️</div>
            <h3>Comfortable Rooms</h3>
            <p>Choose from single, double, deluxe and family rooms.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>Easy Booking</h3>
            <p>Request a room with your preferred check-in and check-out dates.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔔</div>
            <h3>Booking Updates</h3>
            <p>Track your reservation status directly from your dashboard.</p>
        </div>
    </div>
</section>

<section class="about-home" id="about">
    <div>
        <p class="eyebrow">ABOUT GRANDSTAY</p>
        <h2>A simple hotel management experience.</h2>
        <p>GrandStay connects guests and hotel managers in one easy-to-use system.</p>
    </div>
</section>

<footer>© <?php echo date("Y"); ?> GrandStay Hotel Management System</footer>

</body>
</html>
