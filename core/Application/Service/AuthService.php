<?php
namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\EntityNotFoundException;
use App\Application\DTO\RegisterUserDTO;
use App\Application\DTO\LoginDTO;
use App\Application\DTO\PasswordRecoveryDTO;
use App\Application\DTO\ResetPasswordDTO;
use App\Application\DTO\UpdateUserStatusDTO;
use App\Application\DTO\AssignRoleDTO;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\Adapter\Mail\MailAdapterInterface;

class AuthService {
    private UserRepository $userRepo;
    private MailAdapterInterface $mailer;

    public function __construct(UserRepository $userRepo, MailAdapterInterface $mailer) {
        $this->userRepo = $userRepo;
        $this->mailer = $mailer;
    }

    public function register(RegisterUserDTO $dto): array {
        // 1. Validar unicidad de correo
        $existing = $this->userRepo->findByEmail($dto->email);
        if ($existing) {
            throw new ValidationException(["El correo electrónico '{$dto->email}' ya está registrado."]);
        }

        // 2. Hash seguro de contraseña con BCRYPT
        $hash = password_hash($dto->password, PASSWORD_BCRYPT);
        $userId = 'usr-' . round(microtime(true) * 1000) . '-' . bin2hex(random_bytes(2));
        $token = bin2hex(random_bytes(24));

        $user = new User(
            $userId,
            $dto->name,
            $dto->email,
            $hash,
            $dto->role,
            $dto->phone,
            $dto->avatar,
            false,
            $token
        );

        $this->userRepo->save($user);

        // 3. Enviar correo de verificación mediante el Adapter de correo
        $verifyLink = "http://localhost/echele-camello/api/auth.php?action=verify&token={$token}";
        $body = "<p>¡Hola {$user->getName()}!</p><p>Gracias por unirte a <strong>Échele Camello</strong>.</p><p>Por favor confirma tu cuenta haciendo clic en el siguiente enlace:</p><p><a href='{$verifyLink}'>Verificar mi cuenta</a></p>";
        $this->mailer->send($user->getEmail(), 'Verifica tu cuenta — Échele Camello', $body, true);

        return [
            'user' => $user->toSafeArray(),
            'message' => 'Usuario registrado exitosamente. Se ha enviado un correo de verificación.'
        ];
    }

    public function login(LoginDTO $dto): array {
        $user = $this->userRepo->findByEmail($dto->email);
        if (!$user) {
            throw new UnauthorizedActionException('Credenciales inválidas. Correo o contraseña incorrectos.');
        }

        if (!$user->verifyPassword($dto->password)) {
            throw new UnauthorizedActionException('Credenciales inválidas. Correo o contraseña incorrectos.');
        }

        // Validación de Estado de Cuenta mediante Patrón State
        if (!$user->getState()->canLogin()) {
            $reason = $user->getStatusReason() ? " Motivo: {$user->getStatusReason()}" : "";
            if ($user->getStatus() === 'SUSPENDIDO') {
                throw new UnauthorizedActionException("Tu cuenta se encuentra suspendida temporalmente por moderación.{$reason}");
            }
            throw new UnauthorizedActionException("Tu cuenta se encuentra bloqueada por seguridad.{$reason}");
        }

        // Generar token de sesión seguro
        $sessionToken = 'ey-token-' . bin2hex(random_bytes(24));

        return [
            'user' => $user->toSafeArray(),
            'token' => $sessionToken
        ];
    }

    public function verifyAccount(string $token): array {
        $user = $this->userRepo->findByVerificationToken($token);
        if (!$user) {
            throw new ValidationException(['Token de verificación inválido o expirado.']);
        }

        $user->verify();
        $this->userRepo->verifyAccount($user->getId());

        return [
            'success' => true,
            'message' => 'Cuenta verificada exitosamente. Ya puedes iniciar sesión con normalidad.',
            'user' => $user->toSafeArray()
        ];
    }

    public function sendPasswordRecovery(PasswordRecoveryDTO $dto): array {
        $user = $this->userRepo->findByEmail($dto->email);
        if (!$user) {
            // Por seguridad, se emite confirmación neutra
            return [
                'success' => true,
                'message' => 'Si el correo existe en el sistema, se ha enviado un enlace de recuperación.'
            ];
        }

        $token = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $user->setResetToken($token, $expires);
        $this->userRepo->updateResetToken($user->getId(), $token, $expires);

        $resetLink = "http://localhost/echele-camello/index.php?reset_token={$token}";
        $body = "<p>Hola {$user->getName()},</p><p>Has solicitado restablecer tu contraseña en <strong>Échele Camello</strong>.</p><p>Haz clic en el siguiente enlace para continuar (válido por 1 hora):</p><p><a href='{$resetLink}'>Restablecer Contraseña</a></p>";
        $this->mailer->send($user->getEmail(), 'Recuperación de contraseña — Échele Camello', $body, true);

        return [
            'success' => true,
            'message' => 'Se ha enviado un enlace de recuperación a tu correo electrónico.',
            'recoveryToken' => $token // Para pruebas o entornos locales
        ];
    }

    public function resetPassword(ResetPasswordDTO $dto): array {
        $user = $this->userRepo->findByResetToken($dto->token);
        if (!$user) {
            throw new ValidationException(['El token de recuperación no es válido o ya fue utilizado.']);
        }

        if ($user->getResetExpires() && strtotime($user->getResetExpires()) < time()) {
            throw new ValidationException(['El token de recuperación ha expirado. Por favor solicita uno nuevo.']);
        }

        $newHash = password_hash($dto->newPassword, PASSWORD_BCRYPT);
        $user->setPasswordHash($newHash);
        $this->userRepo->updatePassword($user->getId(), $newHash);

        return [
            'success' => true,
            'message' => 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.'
        ];
    }

    public function changeUserStatus(UpdateUserStatusDTO $dto): array {
        $user = $this->userRepo->findById($dto->userId);
        if (!$user) {
            throw new EntityNotFoundException('Usuario', $dto->userId);
        }

        switch ($dto->targetStatus) {
            case 'SUSPENDIDO':
                $user->suspend($dto->actorRole, $dto->reason ?? '');
                break;
            case 'BLOQUEADO':
                $user->block($dto->actorRole, $dto->reason ?? '');
                break;
            case 'ACTIVO':
                $user->activate($dto->actorRole);
                break;
        }

        $this->userRepo->updateStatus($user->getId(), $user->getStatus(), $user->getStatusReason());

        return [
            'success' => true,
            'message' => "Estado de la cuenta actualizado a {$user->getStatus()}",
            'user' => $user->toSafeArray()
        ];
    }

    public function assignRole(AssignRoleDTO $dto): array {
        if ($dto->actorRole !== 'ADMINISTRADOR') {
            throw new UnauthorizedActionException('Solo un administrador puede asignar roles a otros usuarios.');
        }

        $user = $this->userRepo->findById($dto->userId);
        if (!$user) {
            throw new EntityNotFoundException('Usuario', $dto->userId);
        }

        $user->setRole($dto->newRole);
        $this->userRepo->updateRole($user->getId(), $dto->newRole);

        return [
            'success' => true,
            'message' => "Rol actualizado a {$dto->newRole} para el usuario {$user->getName()}",
            'user' => $user->toSafeArray()
        ];
    }

    public function suspendUser(string $userId, string $actorRole, string $reason): void {
        $this->changeUserStatus(new UpdateUserStatusDTO([
            'userId' => $userId,
            'targetStatus' => 'SUSPENDIDO',
            'actorRole' => $actorRole,
            'reason' => $reason
        ]));
    }
}
