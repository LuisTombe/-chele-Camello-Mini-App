<?php
/**
 * API REST: Chat en Tiempo Real entre Cliente y Trabajador
 * Refactorizado con Patrón Repository, DTO y Adapter de Almacenamiento
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\SendMessageDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\EntityNotFoundException;

$method = $_SERVER['REQUEST_METHOD'];

try {
    $container = getAppContainer();
    $chatService = $container->getChatService();

    if ($method === 'GET') {
        $requestId = $_GET['requestId'] ?? null;
        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'requestId es requerido']);
            exit;
        }

        $messages = $chatService->getMessages($requestId);
        $data = array_map(fn($m) => $m->toArray(), $messages);

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $uploadedFile = $_FILES['attachment'] ?? null;

        $dto = new SendMessageDTO($input);
        $msg = $chatService->sendMessage($dto, $uploadedFile);

        echo json_encode(['success' => true, 'data' => $msg->toArray()]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()]);
} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
