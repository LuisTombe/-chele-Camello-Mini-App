<?php
namespace App\Domain\Entity;

class User {
    private string $id;
    private string $name;
    private string $email;
    private string $passwordHash;
    private string $role;
    private ?string $phone;
    private ?string $avatar;
    private bool $isVerified;
    private ?string $verificationToken;
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
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public static function fromArray(array $data): self {
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
            $data['created_at'] ?? null
        );
    }

    public function verify(): void {
        $this->isVerified = true;
        $this->verificationToken = null;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): string { return $this->role; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAvatar(): ?string { return $this->avatar; }
    public function isVerified(): bool { return $this->isVerified; }
    public function getVerificationToken(): ?string { return $this->verificationToken; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function verifyPassword(string $plainPassword): bool {
        return password_verify($plainPassword, $this->passwordHash);
    }

    /**
     * DTO seguro: nunca expone hashes ni tokens de recuperación
     */
    public function toSafeArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'is_verified' => $this->isVerified,
            'created_at' => $this->createdAt,
        ];
    }
}
