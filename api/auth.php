<?php
/**
 * API REST: Autenticación y Registro de Usuarios
 * Implementa Repository, DTO y Adapter SMTP / Mail
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\RegisterUserDTO;
use App\Application\DTO\LoginDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnauthorizedActionException;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $container = getAppContainer();
    $authService = $container->getAuthService();

    if ($method === 'POST' && $action === 'register') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new RegisterUserDTO($input);
        $res = $authService->register($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    if ($method === 'POST' && $action === 'login') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new LoginDTO($input);
        $res = $authService->login($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    if ($method === 'GET' && $action === 'verify') {
        $token = $_GET['token'] ?? '';
        $res = $authService->verifyAccount($token);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Acción o método no soportado']);

} catch (ValidationException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()]);
} catch (UnauthorizedActionException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
