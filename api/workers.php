<?php
/**
 * API REST: Gestión de Trabajadores, Catálogo de Servicios y Acreditaciones
 * Endpoints: GET, POST?action=toggle_availability, POST?action=add_service, POST?action=toggle_service, POST?action=toggle_verification
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $pdo = Database::getConnection();

    if ($method === 'GET') {
        $workerId = $_GET['id'] ?? null;
        $categoryId = $_GET['category'] ?? null;
        $neighborhood = $_GET['neighborhood'] ?? null;
        $search = trim($_GET['search'] ?? '');

        if ($workerId) {
            $stmt = $pdo->prepare("SELECT * FROM `workers` WHERE `id` = ?");
            $stmt->execute([$workerId]);
            $worker = $stmt->fetch();

            if (!$worker) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Trabajador no encontrado']);
                exit;
            }

            // Parse JSON fields
            $worker['secondary_trades'] = json_decode($worker['secondary_trades'] ?? '[]', true) ?: [];
            $worker['portfolio'] = json_decode($worker['portfolio'] ?? '[]', true) ?: [];
            $worker['sena_certified'] = (bool)$worker['sena_certified'];
            $worker['is_available'] = (bool)$worker['is_available'];

            // Fetch services
            $stmtSvc = $pdo->prepare("SELECT * FROM `services` WHERE `worker_id` = ? ORDER BY `created_at` DESC");
            $stmtSvc->execute([$workerId]);
            $services = $stmtSvc->fetchAll();
            foreach ($services as &$s) {
                $s['is_active'] = (bool)$s['is_active'];
                $s['estimated_price'] = (int)$s['estimated_price'];
            }
            $worker['services'] = $services;

            // Fetch reviews
            $stmtRev = $pdo->prepare("SELECT * FROM `reviews` WHERE `worker_id` = ? ORDER BY `created_at` DESC");
            $stmtRev->execute([$workerId]);
            $reviews = $stmtRev->fetchAll();
            foreach ($reviews as &$r) {
                $r['rating'] = (int)$r['rating'];
            }
            $worker['reviews'] = $reviews;

            echo json_encode(['success' => true, 'data' => $worker]);
            exit;
        }

        // List all workers
        $sql = "SELECT * FROM `workers` WHERE 1=1";
        $params = [];

        if ($categoryId) {
            $sql .= " AND `category_id` = ?";
            $params[] = $categoryId;
        }
        if ($neighborhood && $neighborhood !== 'todos') {
            $sql .= " AND `neighborhood` = ?";
            $params[] = $neighborhood;
        }
        if ($search !== '') {
            $sql .= " AND (`name` LIKE ? OR `specialty` LIKE ? OR `neighborhood` LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $workers = $stmt->fetchAll();

        // Attach services and reviews summary
        foreach ($workers as &$w) {
            $w['secondary_trades'] = json_decode($w['secondary_trades'] ?? '[]', true) ?: [];
            $w['portfolio'] = json_decode($w['portfolio'] ?? '[]', true) ?: [];
            $w['sena_certified'] = (bool)$w['sena_certified'];
            $w['is_available'] = (bool)$w['is_available'];

            $stmtSvc = $pdo->prepare("SELECT * FROM `services` WHERE `worker_id` = ? ORDER BY `created_at` DESC");
            $stmtSvc->execute([$w['id']]);
            $services = $stmtSvc->fetchAll();
            foreach ($services as &$s) {
                $s['is_active'] = (bool)$s['is_active'];
                $s['estimated_price'] = (int)$s['estimated_price'];
            }
            $w['services'] = $services;

            $stmtRev = $pdo->prepare("SELECT * FROM `reviews` WHERE `worker_id` = ? ORDER BY `created_at` DESC");
            $stmtRev->execute([$w['id']]);
            $w['reviews'] = $stmtRev->fetchAll();
        }

        echo json_encode(['success' => true, 'data' => $workers]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        if ($action === 'toggle_availability') {
            $workerId = $input['workerId'] ?? 'w-1';
            $stmt = $pdo->prepare("UPDATE `workers` SET `is_available` = NOT `is_available` WHERE `id` = ?");
            $stmt->execute([$workerId]);

            $stmtGet = $pdo->prepare("SELECT `is_available` FROM `workers` WHERE `id` = ?");
            $stmtGet->execute([$workerId]);
            $newVal = (bool)$stmtGet->fetchColumn();

            echo json_encode(['success' => true, 'is_available' => $newVal, 'message' => 'Disponibilidad actualizada']);
            exit;
        }

        if ($action === 'toggle_service') {
            $serviceId = $input['serviceId'] ?? null;
            if (!$serviceId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'serviceId es requerido']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE `services` SET `is_active` = NOT `is_active` WHERE `id` = ?");
            $stmt->execute([$serviceId]);

            $stmtGet = $pdo->prepare("SELECT `is_active` FROM `services` WHERE `id` = ?");
            $stmtGet->execute([$serviceId]);
            $newVal = (bool)$stmtGet->fetchColumn();

            echo json_encode(['success' => true, 'is_active' => $newVal, 'message' => 'Estado del servicio actualizado']);
            exit;
        }

        if ($action === 'add_service') {
            $workerId = $input['workerId'] ?? 'w-1';
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $estimatedPrice = (int)($input['estimatedPrice'] ?? 40000);
            $priceType = $input['priceType'] ?? 'desde';
            $categoryId = $input['categoryId'] ?? 'electricidad';

            if ($title === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Título del servicio es requerido']);
                exit;
            }

            $serviceId = 'svc-' . round(microtime(true) * 1000);
            $stmt = $pdo->prepare("INSERT INTO `services` (`id`, `worker_id`, `category_id`, `title`, `description`, `estimated_price`, `price_type`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$serviceId, $workerId, $categoryId, $title, $description, $estimatedPrice, $priceType]);

            echo json_encode([
                'success' => true,
                'message' => 'Servicio agregado con éxito',
                'service' => [
                    'id' => $serviceId,
                    'worker_id' => $workerId,
                    'category_id' => $categoryId,
                    'title' => $title,
                    'description' => $description,
                    'estimated_price' => $estimatedPrice,
                    'price_type' => $priceType,
                    'is_active' => true
                ]
            ]);
            exit;
        }

        if ($action === 'toggle_verification') {
            $workerId = $input['workerId'] ?? null;
            if (!$workerId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'workerId es requerido']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE `workers` SET `sena_certified` = NOT `sena_certified` WHERE `id` = ?");
            $stmt->execute([$workerId]);

            $stmtGet = $pdo->prepare("SELECT `sena_certified`, `name` FROM `workers` WHERE `id` = ?");
            $stmtGet->execute([$workerId]);
            $res = $stmtGet->fetch();
            $newVal = (bool)$res['sena_certified'];

            // Log audit
            $audId = 'aud-' . round(microtime(true) * 1000);
            $statusText = $newVal ? 'ACREDITADO' : 'REVOCADO';
            $stmtAud = $pdo->prepare("INSERT INTO `audit_logs` (`id`, `action`, `user`, `timestamp`, `schema_name`, `details`, `severity`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtAud->execute([$audId, 'SENA_BADGE_TOGGLE', 'admin_adso_sena', date('Y-m-d H:i:s'), 'schema_usuarios', "Insignia SENA actualizada a {$statusText} para {$res['name']}.", 'INFO']);

            echo json_encode(['success' => true, 'sena_certified' => $newVal, 'message' => "Acreditación SENA actualizada a {$statusText}"]);
            exit;
        }
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
