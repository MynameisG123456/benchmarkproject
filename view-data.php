<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

// Check if user is logged in
requireLogin();


// Fetch all data with joins
$sql = "SELECT 
    h.*, 
    p.project_name, 
    e.employees_name, 
    pl.line_name
FROM HourlyOutput h
JOIN Projects p ON h.project_id = p.project_id
JOIN Employees e ON h.employees_id = e.employees_id
JOIN ProductionLines pl ON h.line_id = pl.line_id
ORDER BY h.date DESC, h.hour DESC";

$hourly_data = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Data - Benchmark Electronics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            padding: 2rem;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        .filters {
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-group {
            display: inline-block;
            margin-right: 1rem;
        }

        .filter-group label {
            margin-right: 0.5rem;
        }

        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
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

        .logout-btn {
            padding: 0.5rem 1.5rem;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
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
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </nav>
    <div class="container">
        

        <div class="filters">
            <div class="filter-group">
                <label for="date-filter">Date:</label>
                <input type="date" id="date-filter">
            </div>
            <div class="filter-group">
                <label for="project-filter">Project:</label>
                <select id="project-filter">
                    <option value="">All Projects</option>
                    <?php foreach ($pdo->query("SELECT DISTINCT project_name FROM Projects") as $row): ?>
                        <option value="<?php echo htmlspecialchars($row['project_name']); ?>">
                            <?php echo htmlspecialchars($row['project_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="line-filter">Production Line:</label>
                <select id="line-filter">
                    <option value="">All Lines</option>
                    <?php foreach ($pdo->query("SELECT DISTINCT line_name FROM ProductionLines") as $row): ?>
                        <option value="<?php echo htmlspecialchars($row['line_name']); ?>">
                            <?php echo htmlspecialchars($row['line_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Hour</th>
                    <th>Project</th>
                    <th>Employee</th>
                    <th>Line</th>
                    <th>Target</th>
                    <th>Actual</th>
                    <th>Backlog</th>
                    <th>Trouble</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hourly_data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo sprintf("%02d:00", $row['hour']); ?></td>
                        <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['employees_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['line_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['target']); ?></td>
                        <td><?php echo htmlspecialchars($row['actual']); ?></td>
                        <td><?php echo htmlspecialchars($row['backlog']); ?></td>
                        <td><?php echo htmlspecialchars($row['trouble']); ?></td>
                        <td><?php echo htmlspecialchars($row['corrective_action']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Add filtering functionality
        document.querySelectorAll('.filters select, .filters input').forEach(filter => {
            filter.addEventListener('change', () => {
                const dateFilter = document.getElementById('date-filter').value;
                const projectFilter = document.getElementById('project-filter').value;
                const lineFilter = document.getElementById('line-filter').value;

                document.querySelectorAll('.data-table tbody tr').forEach(row => {
                    const date = row.cells[0].textContent;
                    const project = row.cells[2].textContent;
                    const line = row.cells[4].textContent;

                    const dateMatch = !dateFilter || date === dateFilter;
                    const projectMatch = !projectFilter || project === projectFilter;
                    const lineMatch = !lineFilter || line === lineFilter;

                    row.style.display = dateMatch && projectMatch && lineMatch ? '' : 'none';
                });
            });
        });
    </script>
</body>

</html>