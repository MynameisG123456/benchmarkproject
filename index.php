<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benchmark Electronics</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>


<body>
    <nav class="navbar">
        <div class="logo">BM</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li class="dropdown">
                <a href="input.php" class="dropdown-toggle">Register Downtime</a>
                <ul class="dropdown-menu">
                    <li><a href="manage-project.php">Manage Project</a></li>
                    <li><a href="view-data.php">View Data</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                </ul>
            </li>
            <li><a href="work-tracking.php">Work Tracking</a></li>
            <li><a href="oee-all.php">OEE All</a></li>
        </ul>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="logout.php" method="POST">
                <button type="submit" class="sign-in">Logout</button>
            </form>
        <?php else: ?>
            <a href="login.php"><button class="sign-in">Login</button></a>
        <?php endif; ?>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1 style="color: white;">Benchmark Electronics</h1>
            <p>Ready to learn more about the full range of advanced capabilities available at Benchmark's global locations? Let's start the conversation!</p>
        </div>
    </header>

    <footer class="footer">
        <div class="footer-content" style="text-align: center;">
            <div class="current-datetime">
                <?php
                date_default_timezone_set('Asia/Bangkok'); // Set timezone to Thailand
                echo date('l, F j, Y h:i A');
                ?>
            </div>
        </div>
    </footer>

    <script src="assets/js/scripts.js"></script>
</body>

</html>