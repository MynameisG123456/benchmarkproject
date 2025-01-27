<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'use_strict_mode' => true
]);

require_once 'config.php'; // Include your database connection file

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        try {
            // Prepare SQL statement to prevent SQL injection
            $sql = "SELECT user_id, username, password, role FROM Users WHERE BINARY username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] === 'Admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $error_message = "Incorrect password.";
                }
            } else {
                $error_message = "Invalid username.";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage(), 3, 'error_log.log');
            $error_message = "An error occurred. Please try again later.";
        }
    } else {
        $error_message = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Benchmark Electronics</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: white;
            border-bottom: 1px solid #eee;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .sign-in {
            padding: 0.5rem 1.5rem;
            background-color: white;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .login-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #444;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .footer {
            padding: 2rem;
            background-color: white;
            border-top: 1px solid #eee;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .social-links a {
            margin-right: 1rem;
            color: #333;
            text-decoration: none;
        }

        .footer-links {
            display: flex;
            gap: 2rem;
        }

        .footer-section {
            margin-right: 2rem;
        }

        .footer-section h3 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #666;
            text-decoration: none;
            font-size: 0.8rem;
        }

        /* Dropdown styles */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            z-index: 1000;
        }

        .dropdown-menu li {
            list-style: none;
        }

        .dropdown-menu a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #333;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .dropdown-menu a:hover {
            background-color: #f4f4f9;
        }

        /* Show dropdown menu on hover */
        .dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="logo">BM</a>
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
        <button class="sign-in">Sign in</button>
    </nav>

    <div class="login-container">
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="submit-btn">Sign In</button>
        </form>
    </div>

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
</body>

</html>