<?php
/**
 * API REST: Solicitudes de Servicio (Service Requests)
 * Refactorizado con Patrón State, Repository, DTO y Observer
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\CreateServiceRequestDTO;
use App\Application\DTO\ChangeRequestStatusDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\InvalidStateTransitionException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\EntityNotFoundException;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $container = getAppContainer();
    $requestService = $container->getRequestService();

    // GET: Listar solicitudes con filtros
    if ($method === 'GET') {
        $filters = [
            'workerId' => $_GET['workerId'] ?? null,
            'clientId' => $_GET['clientId'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];

        $requests = $requestService->getRequests($filters);
        $data = array_map(fn($r) => $r->toArray(), $requests);

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // POST?action=status: Transición de estado mediante Patrón State
    if ($method === 'POST' && $action === 'status') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new ChangeRequestStatusDTO($input);
        $updatedRequest = $requestService->changeStatus($dto);

        echo json_encode([
            'success' => true,
            'message' => "Estado actualizado a {$updatedRequest->getStatus()}",
            'requestId' => $updatedRequest->getId(),
            'status' => $updatedRequest->getStatus(),
            'updatedAt' => $updatedRequest->getUpdatedAt()
        ]);
        exit;
    }

    // POST: Crear nueva solicitud mediante DTO y Repositorio
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new CreateServiceRequestDTO($input);
        $createdRequest = $requestService->createRequest($dto);

        echo json_encode([
            'success' => true,
            'message' => "¡Solicitud {$createdRequest->getCode()} creada con éxito!",
            'request' => [
                'id' => $createdRequest->getId(),
                'code' => $createdRequest->getCode(),
                'workerName' => $createdRequest->getWorkerName(),
                'status' => $createdRequest->getStatus(),
                'createdAt' => $createdRequest->getCreatedAt(),
            ]
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()]);
} catch (InvalidStateTransitionException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (UnauthorizedActionException $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
