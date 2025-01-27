<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

// Check if user is logged in
requireLogin();

// Retrieve the machine name from the URL parameter
$machine_name = isset($_GET['machine_id']) ? $_GET['machine_id'] : '';

// Fetch the data for the selected machine
$query = "
    SELECT
        m.machine_name,
        pr.production_date AS date,
        SUM(pr.total_cycles) AS total_cycles,
        SUM(pr.good_units) AS good_units,
        SUM(pr.rejected_units) AS rejected_units,
        SUM(pd.actual_run_time) AS actual_run_time,
        SUM(pd.ideal_cycle_time * pr.total_cycles) AS ideal_run_time,
        SUM(pd.actual_run_time - pd.ideal_cycle_time * pr.total_cycles) AS downtime
    FROM machines m
    JOIN production_record pr ON m.machine_id = pr.machine_id
    JOIN performance_data pd ON pr.record_id = pd.record_id
    WHERE m.machine_name = :machine_name
    GROUP BY m.machine_name, pr.production_date
    ORDER BY pr.production_date
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':machine_name', $machine_name);
$stmt->execute();
$machineData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the chart
$dates = [];
$availabilities = [];
$performances = [];
$qualities = [];
$oees = [];

foreach ($machineData as $data) {
    $dates[] = $data['date'];
    $availability = calculateAvailability($data['actual_run_time'], $data['downtime']);
    $performance = calculatePerformance($data['ideal_run_time'], $data['actual_run_time']);
    $quality = calculateQuality($data['good_units'], $data['total_cycles']);
    $oee = calculateOEE($data['good_units'], $data['total_cycles'], $data['actual_run_time'], $data['ideal_run_time']);

    $availabilities[] = $availability;
    $performances[] = $performance;
    $qualities[] = $quality;
    $oees[] = $oee;
}

function calculateOEE($good_units, $total_cycles, $actual_run_time, $ideal_run_time)
{
    if ($total_cycles == 0 || $actual_run_time == 0 || $ideal_run_time == 0) {
        return 0;
    }
    $availability = $actual_run_time / ($actual_run_time + $ideal_run_time);
    $performance = $ideal_run_time / $actual_run_time;
    $quality = $good_units / $total_cycles;
    return $availability * $performance * $quality * 100;
}

function calculateAvailability($actual_run_time, $downtime)
{
    if ($actual_run_time + $downtime == 0) {
        return 0;
    }
    return ($actual_run_time / ($actual_run_time + $downtime)) * 100;
}

function calculatePerformance($ideal_run_time, $actual_run_time)
{
    if ($actual_run_time == 0) {
        return 0;
    }
    return ($ideal_run_time / $actual_run_time) * 100;
}

function calculateQuality($good_units, $total_cycles)
{
    if ($total_cycles == 0) {
        return 0;
    }
    return ($good_units / $total_cycles) * 100;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machine Details - Benchmark Electronics</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        <h1 class="text-2xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($machine_name); ?></h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">OEE</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo round($oees[count($oees) - 1], 2); ?>%</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Total Cycles</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo number_format($machineData[count($machineData) - 1]['total_cycles']); ?></div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Good Units</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo number_format($machineData[count($machineData) - 1]['good_units']); ?></div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Rejected Units</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo number_format($machineData[count($machineData) - 1]['rejected_units']); ?></div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Actual Run Time</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo number_format($machineData[count($machineData) - 1]['actual_run_time'], 2); ?> hrs</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ideal Run Time</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo number_format($machineData[count($machineData) - 1]['ideal_run_time'], 2); ?> hrs</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Downtime</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo number_format($machineData[count($machineData) - 1]['downtime'], 2); ?> hrs</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Availability</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo round($availabilities[count($availabilities) - 1], 2); ?>%</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Performance</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo round($performances[count($performances) - 1], 2); ?>%</div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quality</h2>
                <div class="text-6xl font-bold text-gray-900"><?php echo round($qualities[count($qualities) - 1], 2); ?>%</div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">OEE Components Over Time</h2>
            <canvas id="oeeChart"></canvas>
        </div>
    </div>

    <script>
        // Prepare the data for the chart
        const dates = <?php echo json_encode($dates); ?>;
        const availabilities = <?php echo json_encode($availabilities); ?>;
        const performances = <?php echo json_encode($performances); ?>;
        const qualities = <?php echo json_encode($qualities); ?>;
        const oees = <?php echo json_encode($oees); ?>;

        // Create the chart
        const ctx = document.getElementById('oeeChart').getContext('2d');
        const oeeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Availability',
                        data: availabilities,
                        borderColor: 'rgb(75, 192, 192)',
                        fill: false
                    },
                    {
                        label: 'Performance',
                        data: performances,
                        borderColor: 'rgb(54, 162, 235)',
                        fill: false
                    },
                    {
                        label: 'Quality',
                        data: qualities,
                        borderColor: 'rgb(255, 206, 86)',
                        fill: false
                    },
                    {
                        label: 'OEE',
                        data: oees,
                        borderColor: 'rgb(255, 99, 132)',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'OEE Components Over Time'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>