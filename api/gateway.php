<?php
/**
 * =========================================================================
 * Échele Camello — API Gateway Unificado
 * =========================================================================
 * Punto de entrada único para Web, Android y Panel Administrador.
 * Enruta las peticiones hacia los microservicios/dominios propietarios:
 *  - ms-usuarios: Autenticación, registro, perfil de usuario y recuperación.
 *  - ms-perfiles: Perfil profesional, acreditación SENA, fotos y cobertura.
 *  - ms-servicios: Catálogo de servicios, categorías y estado de publicación.
 *  - ms-busqueda: Búsqueda dinámica con Strategy y Adapter de Geolocalización.
 *  - ms-solicitudes: Ciclo de vida con State, agenda y control de concurrencia.
 *  - ms-comunicacion: Chat en tiempo real, mensajes y notificaciones push/email.
 *  - ms-reputacion: Calificaciones, reseñas y moderación con eventos Observer.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Role, X-User-Id');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\RegisterUserDTO;
use App\Application\DTO\LoginDTO;
use App\Application\DTO\PasswordRecoveryDTO;
use App\Application\DTO\ResetPasswordDTO;
use App\Application\DTO\SearchWorkersDTO;
use App\Application\DTO\CreateServiceRequestDTO;
use App\Application\DTO\ChangeRequestStatusDTO;
use App\Application\DTO\CreateReviewDTO;
use App\Application\DTO\SendMessageDTO;
use App\Application\DTO\CreateReportDTO;
use App\Application\DTO\ResolveReportDTO;
use App\Application\DTO\MarkNotificationReadDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\InvalidStateTransitionException;

$route = $_GET['route'] ?? 'health';
$method = $requestMethod;
$action = $_GET['action'] ?? '';

// Capturar payload JSON o POST estándar
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

try {
    $container = getAppContainer();

    // Healthcheck de microservicios y base de datos
    if ($route === 'health') {
        echo json_encode([
            'success' => true,
            'gateway' => 'Échele Camello API Gateway v1.0',
            'status' => 'ONLINE',
            'timestamp' => date('Y-m-d H:i:s'),
            'microservices' => [
                'ms-usuarios'      => ['status' => 'UP', 'patterns' => ['Repository', 'DTO', 'State', 'Adapter (correo)']],
                'ms-perfiles'      => ['status' => 'UP', 'patterns' => ['Repository', 'DTO', 'Adapter (archivos/fotos)']],
                'ms-servicios'     => ['status' => 'UP', 'patterns' => ['Repository', 'DTO', 'State (publicaciones)']],
                'ms-busqueda'      => ['status' => 'UP', 'patterns' => ['Strategy', 'Adapter (geolocalización/haversine)', 'DTO']],
                'ms-solicitudes'   => ['status' => 'UP', 'patterns' => ['State', 'Observer', 'Repository', 'DTO']],
                'ms-comunicacion'  => ['status' => 'UP', 'patterns' => ['Observer / Pub-Sub', 'Adapter (archivos/correo)', 'Repository', 'DTO']],
                'ms-reputacion'    => ['status' => 'UP', 'patterns' => ['State', 'Repository', 'DTO', 'Observer']],
            ],
            'database' => [
                'status' => 'CONNECTED',
                'port' => Database::getActivePort(),
            ]
        ]);
        exit;
    }

    // =========================================================================
    // ENRUTAMIENTO POR DOMINIO
    // =========================================================================

    // 1. MS-USUARIOS
    if ($route === 'auth' || $route === 'usuarios') {
        $authService = $container->getAuthService();

        if ($action === 'register') {
            $dto = new RegisterUserDTO($input);
            $res = $authService->register($dto);
            echo json_encode(['success' => true, 'data' => $res, 'service' => 'ms-usuarios']);
            exit;
        }

        if ($action === 'login') {
            $dto = new LoginDTO($input);
            $res = $authService->login($dto);
            echo json_encode(['success' => true, 'data' => $res, 'service' => 'ms-usuarios']);
            exit;
        }

        if ($action === 'recovery') {
            $dto = new PasswordRecoveryDTO($input);
            $res = $authService->sendPasswordRecovery($dto);
            echo json_encode(['success' => true, 'data' => $res, 'service' => 'ms-usuarios']);
            exit;
        }

        if ($action === 'reset_password') {
            $dto = new ResetPasswordDTO($input);
            $res = $authService->resetPassword($dto);
            echo json_encode(['success' => true, 'data' => $res, 'service' => 'ms-usuarios']);
            exit;
        }

        if ($action === 'verify') {
            $token = $_GET['token'] ?? '';
            $res = $authService->verifyAccount($token);
            echo json_encode(['success' => true, 'data' => $res, 'service' => 'ms-usuarios']);
            exit;
        }
    }

    // 2. MS-BUSQUEDA & GEOLOCALIZACIÓN
    if ($route === 'search' || $route === 'busqueda') {
        $searchService = $container->getSearchService();
        $dto = new SearchWorkersDTO($_GET);
        $userLat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
        $userLon = isset($_GET['lon']) ? (float)$_GET['lon'] : null;

        $results = $searchService->search($dto, $userLat, $userLon);
        echo json_encode([
            'success' => true,
            'data' => $results,
            'sortBy' => $dto->sortBy,
            'service' => 'ms-busqueda'
        ]);
        exit;
    }

    // 3. MS-PERFILES & MS-SERVICIOS
    if ($route === 'workers' || $route === 'perfiles' || $route === 'servicios') {
        // Redirigir a api/workers.php internamente
        require __DIR__ . '/workers.php';
        exit;
    }

    // 4. MS-SOLICITUDES & AGENDAMIENTO
    if ($route === 'requests' || $route === 'solicitudes') {
        require __DIR__ . '/requests.php';
        exit;
    }

    // 5. MS-COMUNICACION (CHAT & NOTIFICACIONES)
    if ($route === 'chat') {
        require __DIR__ . '/chat.php';
        exit;
    }

    if ($route === 'notifications' || $route === 'notificaciones') {
        require __DIR__ . '/notifications.php';
        exit;
    }

    // 6. MS-REPUTACION & MODERACION
    if ($route === 'reviews' || $route === 'calificaciones') {
        require __DIR__ . '/reviews.php';
        exit;
    }

    if ($route === 'reports' || $route === 'moderacion') {
        require __DIR__ . '/reports.php';
        exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => "Ruta de Gateway no encontrada: '{$route}'"]);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors(), 'gateway' => true]);
} catch (UnauthorizedActionException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'gateway' => true]);
} catch (InvalidStateTransitionException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'gateway' => true]);
} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'gateway' => true]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'gateway' => true]);
}
