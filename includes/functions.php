<?php

// Function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Function to redirect to login page if user is not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Function to fetch all projects
function getAllProjects($pdo)
{
    $stmt = $pdo->query("SELECT * FROM Projects");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all employees
function getAllEmployees($pdo)
{
    $stmt = $pdo->query("SELECT * FROM Employees");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all production lines
function getAllProductionLines($pdo)
{
    $stmt = $pdo->query("SELECT * FROM ProductionLines");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch hourly data for a specific date
function getHourlyData($pdo, $date)
{
    $stmt = $pdo->prepare("SELECT h.hour, h.target, h.actual, h.backlog, p.project_name
                           FROM HourlyOutput h
                           JOIN Projects p ON h.project_id = p.project_id
                           WHERE h.date = :date");
    $stmt->execute(['date' => $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Other functions like getOeeData and calculateOeeComponents
function getOeeData($pdo, $machine_name) {
    $stmt = $pdo->prepare("
        SELECT 
            date, 
            run_time AS actual_run_time, 
            planned_production_time AS ideal_run_time, 
            ideal_cycle_time, 
            total_count AS total_cycles, 
            good_count AS good_units 
        FROM oee_data 
        WHERE machine_name = :machine_name
    ");
    $stmt->bindParam(':machine_name', $machine_name, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculateOeeComponents($oee_data) {
    $components = [];
    foreach ($oee_data as $data) {
        $availability = ($data['actual_run_time'] / $data['ideal_run_time']) * 100;
        $performance = ($data['ideal_cycle_time'] * $data['total_cycles'] / $data['actual_run_time']) * 100;
        $quality = ($data['good_units'] / $data['total_cycles']) * 100;
        $oee = ($availability * $performance * $quality) / 10000;

        $components[] = [
            'date' => $data['date'],
            'availability' => number_format($availability, 2),
            'performance' => number_format($performance, 2),
            'quality' => number_format($quality, 2),
            'oee' => number_format($oee, 2)
        ];
    }
    return $components;
}
?>
