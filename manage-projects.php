<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission for adding/editing projects
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : null;
    $project_name = $_POST['project_name'];
    $project_description = $_POST['project_description'];

    if ($project_id) {
        // Update existing project
        $stmt = $pdo->prepare("UPDATE Projects SET project_name = ?, project_description = ? WHERE project_id = ?");
        $stmt->execute([$project_name, $project_description, $project_id]);
    } else {
        // Insert new project
        $stmt = $pdo->prepare("INSERT INTO Projects (project_name, project_description) VALUES (?, ?)");
        $stmt->execute([$project_name, $project_description]);
    }

    header("Location: manage-projects.php");
    exit;
}

// Fetch all projects for display
$projects = $pdo->query("SELECT * FROM Projects")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Benchmark Electronics</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
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
        <main class="main-content">
            <h1>Manage Projects</h1>

            <!-- Example form for adding/editing projects -->
            <form action="manage-projects.php" method="POST">
                <input type="hidden" id="project_id" name="project_id">
                <div class="form-group">
                    <label for="project_name">Project Name</label>
                    <input type="text" id="project_name" name="project_name" required>
                </div>
                <div class="form-group">
                    <label for="project_description">Project Description</label>
                    <textarea id="project_description" name="project_description" required></textarea>
                </div>
                <button type="submit">Save Project</button>
            </form>

            <!-- List of projects with edit buttons -->
            <h2>Existing Projects</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Project Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($project['project_description']); ?></td>
                            <td>
                                <button onclick="editProject(<?php echo $project['project_id']; ?>, '<?php echo htmlspecialchars($project['project_name']); ?>', '<?php echo htmlspecialchars($project['project_description']); ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        function editProject(id, name, description) {
            document.getElementById('project_id').value = id;
            document.getElementById('project_name').value = name;
            document.getElementById('project_description').value = description;
        }
    </script>
</body>

</html>