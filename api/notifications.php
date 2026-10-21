<?php
/**
 * API REST: Notificaciones de Usuario (ms-comunicacion)
 * Implementa Observer / Publish-Subscribe, Repository y DTO
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\MarkNotificationReadDTO;
use App\Domain\Exception\ValidationException;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $container = getAppContainer();
    $notificationService = $container->getNotificationService();

    // 1. GET: Obtener notificaciones del usuario
    if ($method === 'GET') {
        $userId = $_GET['userId'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'userId es requerido']);
            exit;
        }

        $unreadOnly = !empty($_GET['unreadOnly']);
        $result = $notificationService->getUserNotifications($userId, $unreadOnly);

        echo json_encode(array_merge(['success' => true], $result));
        exit;
    }

    // 2. POST?action=mark_read: Marcar como leída
    if ($method === 'POST' && ($action === 'mark_read' || $action === '')) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new MarkNotificationReadDTO($input);
        $notificationService->markAsRead($dto);

        echo json_encode(['success' => true, 'message' => 'Notificaciones actualizadas']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
