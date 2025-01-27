<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Your existing PHP code for data fetching remains the same
$query = "
    SELECT 
        m.machine_name,
        SUM(pr.total_cycles) AS total_cycles,
        SUM(pr.good_units) AS good_units,
        SUM(pr.rejected_units) AS rejected_units,
        SUM(pd.actual_run_time) AS actual_run_time,
        SUM(pd.ideal_cycle_time * pr.total_cycles) AS ideal_run_time
    FROM Machines m
    JOIN Production_Record pr ON m.machine_id = pr.machine_id
    JOIN Performance_Data pd ON pr.record_id = pd.record_id
    GROUP BY m.machine_name
";
$machines = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

function calculateOEE($good_units, $total_cycles, $actual_run_time, $ideal_run_time)
{
    if ($total_cycles == 0 || $actual_run_time == 0 || $ideal_run_time == 0) {
        return 0;
    }
    $availability = $actual_run_time / $ideal_run_time;
    $performance = $ideal_run_time / $actual_run_time;
    $quality = $good_units / $total_cycles;
    return $availability * $performance * $quality * 100;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OEE Dashboard - Benchmark Electronics</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: white;
            position: absolute;
            width: 100%;
            z-index: 10;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
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

        .logout-btn {
            padding: 0.5rem 1.5rem;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1a237e',
                        secondary: '#0d47a1',
                    }
                }
            }
        }
    </script>
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

<body class="bg-gray-50">
    <!-- Navigation -->
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

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">OEE Dashboard</h1>
            <div class="flex space-x-2">
                <button class="bg-white px-4 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <button class="bg-white px-4 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>

        <!-- OEE Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php foreach ($machines as $machine): ?>
                <?php
                $oee = calculateOEE(
                    $machine['good_units'],
                    $machine['total_cycles'],
                    $machine['actual_run_time'],
                    $machine['ideal_run_time']
                );

                // Determine status color based on OEE value
                $statusColor = 'bg-red-100 text-red-800';
                if ($oee >= 85) {
                    $statusColor = 'bg-green-100 text-green-800';
                } elseif ($oee >= 60) {
                    $statusColor = 'bg-yellow-100 text-yellow-800';
                }
                ?>
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition duration-150">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($machine['machine_name']); ?>
                            </h3>
                            <span class="<?php echo $statusColor; ?> px-3 py-1 rounded-full text-sm font-medium">
                                <?php echo round($oee, 2); ?>% OEE
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Total Units</span>
                                <span class="text-gray-900 font-medium"><?php echo number_format($machine['total_cycles']); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Good Units</span>
                                <span class="text-gray-900 font-medium"><?php echo number_format($machine['good_units']); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Rejected Units</span>
                                <span class="text-gray-900 font-medium"><?php echo number_format($machine['rejected_units']); ?></span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a href="view-details.php?machine_id=<?php echo urlencode($machine['machine_name']); ?>"
                                class="text-primary hover:text-blue-700 text-sm font-medium flex items-center">
                                View Details
                                <i class="fas fa-chevron-right ml-2 text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            <nav class="flex items-center space-x-2">
                <a href="#" class="px-3 py-2 rounded-md bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Previous</a>
                <a href="#" class="px-3 py-2 rounded-md bg-primary text-white text-sm font-medium">1</a>
                <a href="#" class="px-3 py-2 rounded-md bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">2</a>
                <a href="#" class="px-3 py-2 rounded-md bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">3</a>
                <a href="#" class="px-3 py-2 rounded-md bg-white border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Next</a>
            </nav>
        </div>
    </div>
</body>

</html>