<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch all projects, employees, and production lines for dropdowns
$projects = $pdo->query("SELECT * FROM Projects")->fetchAll();
$employees = $pdo->query("SELECT * FROM Employees")->fetchAll();
$production_lines = $pdo->query("SELECT * FROM ProductionLines")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO HourlyOutput 
            (project_id, employees_id, line_id, date, shift, hour, target, actual, backlog, trouble, corrective_action)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $_POST['project_id'],
            $_POST['employees_id'],
            $_POST['line_id'],
            $_POST['date'],
            $_POST['shift'],
            $_POST['hour'],
            $_POST['target'],
            $_POST['actual'],
            $_POST['backlog'],
            $_POST['trouble'],
            $_POST['corrective_action']
        ]);

        $success_message = "Data successfully recorded!";
    } catch (PDOException $e) {
        $error_message = "Error recording data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Hourly Output</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        h1 {
            color: #1f2937;
            font-size: 1.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #4b5563;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: white;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            background-color: #2563eb;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            width: 100%;
        }

        button:hover {
            background-color: #1d4ed8;
        }

        .help-text {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }
        }

        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }
        
        .nav-links a.active {
            color: #2563eb;
        }

        .nav-links a.active::after {
            content: '';
            display: block;
            width: 100%;
            height: 2px;
            background-color: #2563eb;
            margin-top: 2px;
        }
        
        .nav-links a:hover {
            color:rgb(11, 22, 47);
        }

        .nav-links a::after {
            content: '';
            display: block;
            width: 0;
            height: 2px;
            background-color:rgb(11, 22, 47);
            margin-top: 2px;
            transition: width 0.3s;
        }

        .logout-btn {
            padding: 0.5rem 1.5rem;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background-color: #000;
        }

        .logout-btn:active {
            transform: scale(0.95);
        }

        .logout-btn:focus {
            outline: none;
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
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Input Hourly Output</h1>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="project_id">Project</label>
                        <select name="project_id" id="project_id" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['project_id']; ?>">
                                    <?php echo htmlspecialchars($project['project_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="employees_id">Employee</label>
                        <select name="employees_id" id="employees_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['employees_id']; ?>">
                                    <?php echo htmlspecialchars($employee['employees_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="line_id">Production Line</label>
                        <select name="line_id" id="line_id" required>
                            <option value="">Select Production Line</option>
                            <?php foreach ($production_lines as $line): ?>
                                <option value="<?php echo $line['line_id']; ?>">
                                    <?php echo htmlspecialchars($line['line_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" required>
                    </div>

                    <div class="form-group">
                        <label for="shift">Shift</label>
                        <select name="shift" id="shift" required>
                            <option value="">Select Shift</option>
                            <option value="Morning">Morning</option>
                            <option value="Afternoon">Afternoon</option>
                            <option value="Night">Night</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hour">Hour</label>
                        <input type="number" name="hour" id="hour" min="0" max="23" required>
                        <div class="help-text">Enter hour in 24-hour format (0-23)</div>
                    </div>

                    <div class="form-group">
                        <label for="target">Target</label>
                        <input type="number" name="target" id="target" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="actual">Actual</label>
                        <input type="number" name="actual" id="actual" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="backlog">Backlog</label>
                        <input type="number" name="backlog" id="backlog" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="trouble">Trouble</label>
                        <input type="text" name="trouble" id="trouble">
                        <div class="help-text">Optional: Describe any issues encountered</div>
                    </div>

                    <div class="form-group full-width">
                        <label for="corrective_action">Corrective Action</label>
                        <textarea name="corrective_action" id="corrective_action"></textarea>
                        <div class="help-text">Optional: Describe actions taken to resolve issues</div>
                    </div>

                    <div class="form-group full-width">
                        <button type="submit">Submit Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

</html>