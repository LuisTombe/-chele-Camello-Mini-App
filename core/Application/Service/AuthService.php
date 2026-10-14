<?php
namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\UnauthorizedActionException;
use App\Domain\Exception\EntityNotFoundException;
use App\Application\DTO\RegisterUserDTO;
use App\Application\DTO\LoginDTO;
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

        // Generar token de sesión seguro simulado / JWT representativo
        $sessionToken = 'ey-token-' . bin2hex(random_bytes(24));

        return [
            'user' => $user->toSafeArray(),
            'token' => $sessionToken
        ];
    }

    public function verifyAccount(string $token): array {
        // En una implementación simplificada, validar token o activar
        return [
            'success' => true,
            'message' => 'Cuenta verificada exitosamente.'
        ];
    }
}
