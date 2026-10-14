<?php
/**
 * API REST: Gestión de Trabajadores, Catálogo de Servicios y Acreditaciones
 * Refactorizado con Patrón Strategy (Ordenamiento), Repository, State y DTO
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\SearchWorkersDTO;
use App\Application\DTO\CreateServiceDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\EntityNotFoundException;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $container = getAppContainer();
    $workerService = $container->getWorkerService();

    // GET: Búsqueda y listado con Patrón Strategy
    if ($method === 'GET') {
        $dto = new SearchWorkersDTO($_GET);

        if ($dto->workerId) {
            $worker = $workerService->getWorkerDetail($dto->workerId);
            echo json_encode(['success' => true, 'data' => $worker]);
            exit;
        }

        $workers = $workerService->getWorkers($dto);
        echo json_encode(['success' => true, 'data' => $workers, 'sortBy' => $dto->sortBy]);
        exit;
    }

    // POST?action=toggle_availability
    if ($method === 'POST' && $action === 'toggle_availability') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $workerId = $input['workerId'] ?? null;
        $isAvailable = isset($input['isAvailable']) ? (bool)$input['isAvailable'] : true;

        if (!$workerId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'workerId es requerido']);
            exit;
        }

        $workerService->toggleAvailability($workerId, $isAvailable);
        echo json_encode(['success' => true, 'message' => 'Disponibilidad actualizada', 'isAvailable' => $isAvailable]);
        exit;
    }

    // POST?action=toggle_verification
    if ($method === 'POST' && $action === 'toggle_verification') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $workerId = $input['workerId'] ?? null;
        $certified = isset($input['certified']) ? (bool)$input['certified'] : true;
        $title = $input['title'] ?? null;

        if (!$workerId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'workerId es requerido']);
            exit;
        }

        $workerService->toggleVerification($workerId, $certified, $title);
        echo json_encode(['success' => true, 'message' => 'Acreditación SENA actualizada', 'certified' => $certified]);
        exit;
    }

    // POST?action=add_service
    if ($method === 'POST' && $action === 'add_service') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new CreateServiceDTO($input);
        $service = $workerService->addService($dto);

        echo json_encode(['success' => true, 'message' => 'Servicio agregado al catálogo', 'service' => $service->toArray()]);
        exit;
    }

    // POST?action=toggle_service: Patrón State para publicaciones
    if ($method === 'POST' && $action === 'toggle_service') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $serviceId = $input['serviceId'] ?? null;
        $role = $input['role'] ?? 'TRABAJADOR';

        if (!$serviceId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'serviceId es requerido']);
            exit;
        }

        $service = $workerService->toggleServiceStatus($serviceId, $role);
        echo json_encode(['success' => true, 'message' => 'Estado del servicio actualizado', 'service' => $service->toArray()]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no soportado']);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()]);
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
