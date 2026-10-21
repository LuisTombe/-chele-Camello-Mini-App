<?php
/**
 * API REST: Autenticación, Identidad y Gestión de Usuarios
 * Implementa Repository, DTO, State (Estado de cuenta) y Adapter SMTP / Mail
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/bootstrap.php';

use App\Application\DTO\RegisterUserDTO;
use App\Application\DTO\LoginDTO;
use App\Application\DTO\PasswordRecoveryDTO;
use App\Application\DTO\ResetPasswordDTO;
use App\Application\DTO\UpdateUserStatusDTO;
use App\Application\DTO\AssignRoleDTO;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\EntityNotFoundException;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $container = getAppContainer();
    $authService = $container->getAuthService();

    // 1. Registro de usuario
    if ($method === 'POST' && $action === 'register') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new RegisterUserDTO($input);
        $res = $authService->register($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    // 2. Inicio de sesión con validación de estado de cuenta (State)
    if ($method === 'POST' && $action === 'login') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new LoginDTO($input);
        $res = $authService->login($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    // 3. Verificación de cuenta por token
    if ($method === 'GET' && $action === 'verify') {
        $token = $_GET['token'] ?? '';
        $res = $authService->verifyAccount($token);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    // 4. Solicitar recuperación de contraseña (Adapter SMTP)
    if ($method === 'POST' && $action === 'recovery') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new PasswordRecoveryDTO($input);
        $res = $authService->sendPasswordRecovery($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    // 5. Restablecer contraseña con token de recuperación
    if ($method === 'POST' && $action === 'reset_password') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new ResetPasswordDTO($input);
        $res = $authService->resetPassword($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    // 6. Cambiar estado de cuenta (Suspender / Bloquear / Activar)
    if ($method === 'POST' && $action === 'change_status') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new UpdateUserStatusDTO($input);
        $res = $authService->changeUserStatus($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    // 7. Asignar rol por administrador
    if ($method === 'POST' && $action === 'assign_role') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $dto = new AssignRoleDTO($input);
        $res = $authService->assignRole($dto);

        echo json_encode(array_merge(['success' => true], $res));
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Acción o método no soportado']);

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
