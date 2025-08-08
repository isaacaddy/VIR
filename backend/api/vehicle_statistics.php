<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

try {
    if (isset($_GET['type'])) {
        switch ($_GET['type']) {
            case 'dashboard':
                getDashboardStats($pdo);
                break;
            case 'monthly':
                getMonthlyStats($pdo);
                break;
            case 'registration_records':
                getRegistrationRecordsStats($pdo);
                break;
            default:
                getAllStats($pdo);
                break;
        }
    } else {
        getAllStats($pdo);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function getDashboardStats($pdo) {
    try {
        // Get total registrations
        $total_sql = "SELECT COUNT(*) as total FROM vehicle_registrations WHERE status = 'Active'";
        $total_stmt = $pdo->prepare($total_sql);
        $total_stmt->execute();
        $total_count = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get today's registrations
        $today_sql = "SELECT COUNT(*) as today FROM vehicle_registrations WHERE DATE(registration_date) = CURDATE()";
        $today_stmt = $pdo->prepare($today_sql);
        $today_stmt->execute();
        $today_count = $today_stmt->fetch(PDO::FETCH_ASSOC)['today'];
        
        // Get this month's registrations
        $month_sql = "SELECT COUNT(*) as month FROM vehicle_registrations WHERE MONTH(registration_date) = MONTH(CURDATE()) AND YEAR(registration_date) = YEAR(CURDATE())";
        $month_stmt = $pdo->prepare($month_sql);
        $month_stmt->execute();
        $month_count = $month_stmt->fetch(PDO::FETCH_ASSOC)['month'];
        
        // Get recent activities
        $recent_sql = "SELECT COUNT(*) as recent FROM vehicle_history WHERE DATE(performed_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $recent_stmt = $pdo->prepare($recent_sql);
        $recent_stmt->execute();
        $recent_count = $recent_stmt->fetch(PDO::FETCH_ASSOC)['recent'];
        
        // Get vehicle type distribution
        $types_sql = "SELECT body_type, COUNT(*) as count FROM vehicle_registrations WHERE status = 'Active' GROUP BY body_type ORDER BY count DESC LIMIT 5";
        $types_stmt = $pdo->prepare($types_sql);
        $types_stmt->execute();
        $vehicle_types = $types_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get fuel type distribution
        $fuel_sql = "SELECT fuel_type, COUNT(*) as count FROM vehicle_registrations WHERE status = 'Active' GROUP BY fuel_type";
        $fuel_stmt = $pdo->prepare($fuel_sql);
        $fuel_stmt->execute();
        $fuel_types = $fuel_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_registrations' => $total_count,
                'today_registrations' => $today_count,
                'month_registrations' => $month_count,
                'recent_activities' => $recent_count,
                'vehicle_types' => $vehicle_types,
                'fuel_types' => $fuel_types
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getMonthlyStats($pdo) {
    try {
        $sql = "SELECT 
                    DATE_FORMAT(stat_date, '%Y-%m') as month,
                    SUM(total_registrations) as total,
                    SUM(new_registrations) as new_reg,
                    SUM(renewals) as renewals,
                    SUM(ownership_transfers) as transfers,
                    SUM(inspections_completed) as inspections
                FROM registration_statistics 
                WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(stat_date, '%Y-%m')
                ORDER BY month DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $stats
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getRegistrationRecordsStats($pdo) {
    try {
        // Get total registrations (including all statuses)
        $total_sql = "SELECT COUNT(*) as count FROM vehicle_registrations";
        $total_stmt = $pdo->prepare($total_sql);
        $total_stmt->execute();
        $total_count = $total_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get today's registrations (using registration_date)
        $today_sql = "SELECT COUNT(*) as count FROM vehicle_registrations WHERE DATE(registration_date) = CURDATE()";
        $today_stmt = $pdo->prepare($today_sql);
        $today_stmt->execute();
        $today_count = $today_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get this week's registrations (Monday to Sunday, using registration_date)
        $week_sql = "SELECT COUNT(*) as count FROM vehicle_registrations 
                     WHERE YEARWEEK(registration_date, 1) = YEARWEEK(CURDATE(), 1)";
        $week_stmt = $pdo->prepare($week_sql);
        $week_stmt->execute();
        $week_count = $week_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get this month's registrations (using registration_date)
        $month_sql = "SELECT COUNT(*) as count FROM vehicle_registrations 
                      WHERE MONTH(registration_date) = MONTH(CURDATE()) AND YEAR(registration_date) = YEAR(CURDATE())";
        $month_stmt = $pdo->prepare($month_sql);
        $month_stmt->execute();
        $month_count = $month_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get deleted/inactive registrations count
        $deleted_sql = "SELECT COUNT(*) as count FROM vehicle_registrations WHERE status = 'Inactive'";
        $deleted_stmt = $pdo->prepare($deleted_sql);
        $deleted_stmt->execute();
        $deleted_count = $deleted_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'total' => $total_count,
                'today' => $today_count,
                'week' => $week_count,
                'month' => $month_count,
                'deleted' => $deleted_count
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function getAllStats($pdo) {
    try {
        $sql = "SELECT * FROM registration_statistics ORDER BY stat_date DESC LIMIT 30";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $stats
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>