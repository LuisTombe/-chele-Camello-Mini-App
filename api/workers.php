<?php
/**
 * API REST: Gestión de Trabajadores, Perfiles, Catálogo de Servicios y Categorías
 * Implementa Repository, DTO, State (Estado de publicaciones), Strategy y Adapter (Archivos/Fotos)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\SearchWorkersDTO;
use App\Application\DTO\CreateServiceDTO;
use App\Application\DTO\UpdateServiceDTO;
use App\Application\DTO\UpdateWorkerProfileDTO;
use App\Application\DTO\UpdateLocationDTO;
use App\Application\DTO\CreateCategoryDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\EntityNotFoundException;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $container = getAppContainer();
    $workerService = $container->getWorkerService();

    // 1. GET: Categorías
    if ($method === 'GET' && $action === 'categories') {
        $cats = $workerService->getCategories();
        echo json_encode(['success' => true, 'data' => $cats]);
        exit;
    }

    // 2. GET: Búsqueda y listado con Patrón Strategy
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

    // 3. POST?action=update_profile: Actualizar perfil profesional del trabajador
    if ($method === 'POST' && $action === 'update_profile') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new UpdateWorkerProfileDTO($input);
        $updated = $workerService->updateProfile($dto);

        echo json_encode(['success' => true, 'message' => 'Perfil profesional actualizado exitosamente', 'worker' => $updated->toArray()]);
        exit;
    }

    // 4. POST?action=update_location: Actualizar barrio y ubicación de cobertura
    if ($method === 'POST' && $action === 'update_location') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new UpdateLocationDTO($input);
        $workerService->updateLocation($dto);

        echo json_encode(['success' => true, 'message' => 'Ubicación y cobertura actualizadas']);
        exit;
    }

    // 5. POST?action=upload_avatar: Adapter de archivos para foto de perfil
    if ($method === 'POST' && $action === 'upload_avatar') {
        $workerId = $_POST['workerId'] ?? null;
        $file = $_FILES['avatar'] ?? null;

        if (!$workerId || !$file) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'workerId y archivo avatar son requeridos']);
            exit;
        }

        $avatarUrl = $workerService->uploadAvatar($workerId, $file);
        echo json_encode(['success' => true, 'message' => 'Foto de perfil actualizada', 'avatarUrl' => $avatarUrl]);
        exit;
    }

    // 6. POST?action=upload_certificate: Adapter de archivos para certificado SENA
    if ($method === 'POST' && $action === 'upload_certificate') {
        $workerId = $_POST['workerId'] ?? null;
        $title = $_POST['title'] ?? 'Certificado Técnico Laboral SENA';
        $file = $_FILES['certificate'] ?? null;

        if (!$workerId || !$file) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'workerId y archivo de certificado son requeridos']);
            exit;
        }

        $certUrl = $workerService->uploadCertificate($workerId, $file, $title);
        echo json_encode(['success' => true, 'message' => 'Certificado subido y acreditado exitosamente', 'certificateUrl' => $certUrl]);
        exit;
    }

    // 7. POST?action=toggle_availability
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

    // 8. POST?action=toggle_verification
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

    // 9. POST?action=add_service
    if ($method === 'POST' && $action === 'add_service') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new CreateServiceDTO($input);
        $service = $workerService->addService($dto);

        echo json_encode(['success' => true, 'message' => 'Servicio agregado al catálogo', 'service' => $service->toArray()]);
        exit;
    }

    // 10. POST?action=update_service: Editar servicio existente
    if ($method === 'POST' && $action === 'update_service') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new UpdateServiceDTO($input);
        $service = $workerService->updateService($dto);

        echo json_encode(['success' => true, 'message' => 'Servicio actualizado exitosamente', 'service' => $service->toArray()]);
        exit;
    }

    // 11. POST?action=toggle_service: Patrón State para publicaciones (Activo / Inactivo)
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

    // 12. POST?action=suspend_service: Suspensión administrativa (State)
    if ($method === 'POST' && $action === 'suspend_service') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $serviceId = $input['serviceId'] ?? null;
        $role = $input['role'] ?? 'ADMINISTRADOR';
        $reason = $input['reason'] ?? 'Suspensión administrativa por moderación';

        if (!$serviceId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'serviceId es requerido']);
            exit;
        }

        $service = $workerService->suspendService($serviceId, $role, $reason);
        echo json_encode(['success' => true, 'message' => 'Servicio suspendido por moderación', 'service' => $service->toArray()]);
        exit;
    }

    // 13. POST?action=unsuspend_service: Levantar suspensión administrativa (State)
    if ($method === 'POST' && $action === 'unsuspend_service') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $serviceId = $input['serviceId'] ?? null;
        $role = $input['role'] ?? 'ADMINISTRADOR';

        if (!$serviceId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'serviceId es requerido']);
            exit;
        }

        $service = $workerService->unsuspendService($serviceId, $role);
        echo json_encode(['success' => true, 'message' => 'Suspensión levantada exitosamente', 'service' => $service->toArray()]);
        exit;
    }

    // 14. POST?action=add_category: Gestión de categorías por administrador
    if ($method === 'POST' && $action === 'add_category') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new CreateCategoryDTO($input);
        $category = $workerService->createCategory($dto);

        echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente', 'category' => $category->toArray()]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método o acción no soportada']);

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
