<?php
namespace App\Domain\Entity;

use App\Domain\State\User\UserStateInterface;
use App\Domain\State\User\ActiveUserState;
use App\Domain\State\User\SuspendedUserState;
use App\Domain\State\User\BlockedUserState;

class User {
    private string $id;
    private string $name;
    private string $email;
    private string $passwordHash;
    private string $role;
    private UserStateInterface $state;
    private ?string $statusReason;
    private ?string $phone;
    private ?string $avatar;
    private bool $isVerified;
    private ?string $verificationToken;
    private ?string $resetToken;
    private ?string $resetExpires;
    private string $createdAt;

    public function __construct(
        string $id,
        string $name,
        string $email,
        string $passwordHash,
        string $role = 'CLIENTE',
        ?string $phone = null,
        ?string $avatar = null,
        bool $isVerified = false,
        ?string $verificationToken = null,
        ?UserStateInterface $state = null,
        ?string $statusReason = null,
        ?string $resetToken = null,
        ?string $resetExpires = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->phone = $phone;
        $this->avatar = $avatar;
        $this->isVerified = $isVerified;
        $this->verificationToken = $verificationToken;
        $this->state = $state ?? new ActiveUserState();
        $this->statusReason = $statusReason;
        $this->resetToken = $resetToken;
        $this->resetExpires = $resetExpires;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function stateFromStatus(string $status): UserStateInterface {
        return match (strtoupper($status)) {
            'SUSPENDIDO' => new SuspendedUserState(),
            'BLOQUEADO' => new BlockedUserState(),
            default => new ActiveUserState(),
        };
    }

    public static function fromArray(array $data): self {
        $stateObj = self::stateFromStatus($data['status'] ?? 'ACTIVO');

        return new self(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['password_hash'] ?? '',
            $data['role'] ?? 'CLIENTE',
            $data['phone'] ?? null,
            $data['avatar'] ?? null,
            !empty($data['is_verified']),
            $data['verification_token'] ?? null,
            $stateObj,
            $data['status_reason'] ?? null,
            $data['reset_token'] ?? null,
            $data['reset_expires'] ?? null,
            $data['created_at'] ?? null
        );
    }

    public function verify(): void {
        $this->isVerified = true;
        $this->verificationToken = null;
    }

    public function transitionToState(UserStateInterface $newState, ?string $reason = null): void {
        $this->state = $newState;
        $this->statusReason = $reason;
    }

    public function suspend(string $actorRole, string $reason): void {
        $this->state->suspend($this, $actorRole, $reason);
    }

    public function block(string $actorRole, string $reason): void {
        $this->state->block($this, $actorRole, $reason);
    }

    public function activate(string $actorRole): void {
        $this->state->activate($this, $actorRole);
    }

    public function setRole(string $newRole): void {
        $this->role = $newRole;
    }

    public function setResetToken(?string $token, ?string $expires): void {
        $this->resetToken = $token;
        $this->resetExpires = $expires;
    }

    public function setPasswordHash(string $newHash): void {
        $this->passwordHash = $newHash;
        $this->resetToken = null;
        $this->resetExpires = null;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): string { return $this->role; }
    public function getState(): UserStateInterface { return $this->state; }
    public function getStatus(): string { return $this->state->getStatus(); }
    public function getStatusReason(): ?string { return $this->statusReason; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAvatar(): ?string { return $this->avatar; }
    public function isVerified(): bool { return $this->isVerified; }
    public function getVerificationToken(): ?string { return $this->verificationToken; }
    public function getResetToken(): ?string { return $this->resetToken; }
    public function getResetExpires(): ?string { return $this->resetExpires; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function verifyPassword(string $plainPassword): bool {
        return password_verify($plainPassword, $this->passwordHash);
    }

    /**
     * DTO seguro: nunca expone hashes ni tokens de recuperación o verificación
     */
    public function toSafeArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->getStatus(),
            'status_reason' => $this->statusReason,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'is_verified' => $this->isVerified,
            'created_at' => $this->createdAt,
        ];
    }
}
