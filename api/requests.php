<?php
/**
 * API REST: Solicitudes de Servicio (Service Requests)
 * Endpoints: GET, POST (crear), POST?action=status (cambiar estado)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $pdo = Database::getConnection();

    if ($method === 'GET') {
        $workerId = $_GET['workerId'] ?? null;
        $clientId = $_GET['clientId'] ?? null;
        $status = $_GET['status'] ?? null;

        $sql = "SELECT * FROM `service_requests` WHERE 1=1";
        $params = [];

        if ($workerId) {
            $sql .= " AND `worker_id` = ?";
            $params[] = $workerId;
        }
        if ($clientId) {
            $sql .= " AND `client_id` = ?";
            $params[] = $clientId;
        }
        if ($status && $status !== 'TODAS') {
            $sql .= " AND `status` = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY `id` DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll();

        // Convert booleans / numbers
        foreach ($requests as &$req) {
            $req['urgent'] = (bool)$req['urgent'];
            $req['has_review'] = (bool)$req['has_review'];
            $req['estimated_cost'] = (int)$req['estimated_cost'];
        }

        echo json_encode(['success' => true, 'data' => $requests]);
        exit;
    }

    if ($method === 'POST' && $action === 'status') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $requestId = $input['requestId'] ?? null;
        $newStatus = $input['status'] ?? null;
        $role = $input['role'] ?? 'CLIENTE';

        if (!$requestId || !$newStatus) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'requestId y status son requeridos']);
            exit;
        }

        $now = date('Y-m-d H:i');
        $timeStr = date('H:i');

        // Actualizar solicitud
        $stmt = $pdo->prepare("UPDATE `service_requests` SET `status` = ?, `updated_at` = ? WHERE `id` = ?");
        $stmt->execute([$newStatus, $now, $requestId]);

        // Obtener código y detalles de la solicitud
        $stmtReq = $pdo->prepare("SELECT `code`, `worker_name` FROM `service_requests` WHERE `id` = ?");
        $stmtReq->execute([$requestId]);
        $reqData = $stmtReq->fetch();

        // Mensajes automáticos del sistema según estado
        $statusNotices = [
            'ACEPTADA' => 'El camellador ha aceptado el trabajo. Coordinando visita técnica.',
            'RECHAZADA' => 'El camellador no tiene disponibilidad para esta franja horaria.',
            'PROGRAMADA' => 'El servicio ha sido agendado.',
            'EN_PROCESO' => 'El técnico inició la labor en la dirección indicada.',
            'FINALIZADA' => 'El camello ha sido marcado como finalizado con éxito.',
            'CANCELADA' => 'La solicitud ha sido cancelada.',
            'PENDIENTE' => 'Solicitud en revisión.',
        ];
        $msgText = '[ESTADO ACTUALIZADO]: ' . ($statusNotices[$newStatus] ?? "Estado cambiado a {$newStatus}");

        // Insertar aviso en chat
        $msgId = 'msg-' . round(microtime(true) * 1000);
        $stmtChat = $pdo->prepare("INSERT INTO `chat_messages` (`id`, `request_id`, `sender_id`, `sender_name`, `sender_role`, `text`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtChat->execute([$msgId, $requestId, 'sistema', 'Échele Camello Bot', 'TRABAJADOR', $msgText, $timeStr]);

        // Registrar auditoría
        $audId = 'aud-' . round(microtime(true) * 1000);
        $audUser = ($role === 'TRABAJADOR') ? 'tecnico_camellador' : 'usuario_cliente';
        $code = $reqData['code'] ?? $requestId;
        $stmtAud = $pdo->prepare("INSERT INTO `audit_logs` (`id`, `action`, `user`, `timestamp`, `schema_name`, `details`, `severity`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtAud->execute([$audId, 'STATUS_TRANSITION', $audUser, date('Y-m-d H:i:s'), 'schema_solicitudes', "Solicitud {$code} cambió a {$newStatus}.", 'INFO']);

        echo json_encode([
            'success' => true,
            'message' => "Estado actualizado a {$newStatus}",
            'requestId' => $requestId,
            'status' => $newStatus,
            'updatedAt' => $now
        ]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $newId = 'req-' . round(microtime(true) * 1000);
        $newCode = 'CAM-2026-' . rand(100, 999);
        $now = date('Y-m-d H:i');
        $timeStr = date('H:i');
        $dateStr = date('Y-m-d');

        $clientId = $input['clientId'] ?? 'cli-me';
        $clientName = $input['clientName'] ?? 'Tú (Cliente)';
        $clientPhone = $input['clientPhone'] ?? '+57 300 000 0000';
        $clientAvatar = $input['clientAvatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
        $workerId = $input['workerId'] ?? '';
        $workerName = $input['workerName'] ?? '';
        $workerSpecialty = $input['workerSpecialty'] ?? '';
        $workerAvatar = $input['workerAvatar'] ?? '';
        $serviceTitle = $input['serviceTitle'] ?? 'Servicio Técnico';
        $category = $input['category'] ?? 'General';
        $status = 'PENDIENTE';
        $scheduledDate = $input['scheduledDate'] ?? $dateStr;
        $scheduledTime = $input['scheduledTime'] ?? $timeStr;
        $address = $input['address'] ?? '';
        $neighborhood = $input['neighborhood'] ?? 'Neiva';
        $description = $input['description'] ?? '';
        $estimatedCost = (int)($input['estimatedCost'] ?? 40000);
        $urgent = !empty($input['urgent']) ? 1 : 0;
        $diagnosisPhoto = $input['diagnosisPhoto'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO `service_requests` (
            `id`, `code`, `client_id`, `client_name`, `client_phone`, `client_avatar`,
            `worker_id`, `worker_name`, `worker_specialty`, `worker_avatar`,
            `service_title`, `category`, `status`, `scheduled_date`, `scheduled_time`,
            `address`, `neighborhood`, `description`, `estimated_cost`, `urgent`,
            `diagnosis_photo`, `has_review`, `created_at`, `updated_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)");

        $stmt->execute([
            $newId, $newCode, $clientId, $clientName, $clientPhone, $clientAvatar,
            $workerId, $workerName, $workerSpecialty, $workerAvatar,
            $serviceTitle, $category, $status, $scheduledDate, $scheduledTime,
            $address, $neighborhood, $description, $estimatedCost, $urgent,
            $diagnosisPhoto, $now, $now
        ]);

        // Mensaje inicial de chat
        $chatId = 'msg-' . round(microtime(true) * 1000);
        $initialText = "¡Hola {$workerName}! He creado una solicitud para {$serviceTitle} en el barrio {$neighborhood}. Detalle: \"{$description}\"";
        $stmtChat = $pdo->prepare("INSERT INTO `chat_messages` (`id`, `request_id`, `sender_id`, `sender_name`, `sender_role`, `text`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtChat->execute([$chatId, $newId, $clientId, $clientName, 'CLIENTE', $initialText, $timeStr]);

        // Registro de auditoría
        $audId = 'aud-' . round(microtime(true) * 1000);
        $stmtAud = $pdo->prepare("INSERT INTO `audit_logs` (`id`, `action`, `user`, `timestamp`, `schema_name`, `details`, `severity`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtAud->execute([$audId, 'SERVICE_REQUEST_CREATED', 'usuario_cliente', date('Y-m-d H:i:s'), 'schema_solicitudes', "Solicitud {$newCode} registrada para {$workerName} ({$category}).", 'INFO']);

        echo json_encode([
            'success' => true,
            'message' => "¡Solicitud {$newCode} creada con éxito!",
            'request' => [
                'id' => $newId,
                'code' => $newCode,
                'workerName' => $workerName,
                'status' => $status,
                'createdAt' => $now,
            ]
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
