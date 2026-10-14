<?php
/**
 * API REST: Chat en Tiempo Real entre Cliente y Trabajador
 * Endpoints: GET?requestId=xxx, POST (enviar mensaje)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = Database::getConnection();

    if ($method === 'GET') {
        $requestId = $_GET['requestId'] ?? null;
        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'requestId es requerido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM `chat_messages` WHERE `request_id` = ? ORDER BY `created_at` ASC");
        $stmt->execute([$requestId]);
        $messages = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $messages]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $requestId = $input['requestId'] ?? null;
        $text = trim($input['text'] ?? '');
        $role = $input['role'] ?? 'CLIENTE';
        $senderName = $input['senderName'] ?? ($role === 'CLIENTE' ? 'Tú (Cliente)' : 'Técnico Especialista');
        $senderId = $input['senderId'] ?? ($role === 'CLIENTE' ? 'cli-me' : 'w-1');
        $attachmentUrl = $input['attachmentUrl'] ?? null;

        if (!$requestId || $text === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'requestId y texto son requeridos']);
            exit;
        }

        $id = 'msg-' . round(microtime(true) * 1000);
        $timeStr = date('H:i');

        $stmt = $pdo->prepare("INSERT INTO `chat_messages` (`id`, `request_id`, `sender_id`, `sender_name`, `sender_role`, `text`, `timestamp`, `attachment_url`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $requestId, $senderId, $senderName, $role, $text, $timeStr, $attachmentUrl]);

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $id,
                'request_id' => $requestId,
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'sender_role' => $role,
                'text' => $text,
                'timestamp' => $timeStr,
                'attachment_url' => $attachmentUrl
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
