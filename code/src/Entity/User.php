<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'An account already exists with this email address.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_WARNED = 'warned';
    public const STATUS_LOCKED = 'locked';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    /** @var array<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', length: 20)]
    private string $accountStatus = self::STATUS_ACTIVE;

    #[ORM\Column(type: 'integer')]
    private int $warningCount = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lockedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lockReason = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: HumbleProfile::class, cascade: ['persist', 'remove'])]
    private ?HumbleProfile $humbleProfile = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @return array<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    public function setAccountStatus(string $accountStatus): self
    {
        $this->accountStatus = $accountStatus;
        return $this;
    }

    public function getWarningCount(): int
    {
        return $this->warningCount;
    }

    public function incrementWarningCount(): int
    {
        return ++$this->warningCount;
    }

    public function isLocked(): bool
    {
        return $this->accountStatus === self::STATUS_LOCKED;
    }

    public function lock(string $reason): self
    {
        $this->accountStatus = self::STATUS_LOCKED;
        $this->lockedAt = new \DateTimeImmutable();
        $this->lockReason = $reason;
        return $this;
    }

    public function unlock(): self
    {
        $this->accountStatus = self::STATUS_ACTIVE;
        $this->lockedAt = null;
        $this->lockReason = null;
        $this->warningCount = 0;
        return $this;
    }

    public function getLockedAt(): ?\DateTimeImmutable
    {
        return $this->lockedAt;
    }

    public function getLockReason(): ?string
    {
        return $this->lockReason;
    }

    public function getHumbleProfile(): ?HumbleProfile
    {
        return $this->humbleProfile;
    }

    public function setHumbleProfile(?HumbleProfile $humbleProfile): self
    {
        if ($humbleProfile !== null && $humbleProfile->getUser() !== $this) {
            $humbleProfile->setUser($this);
        }
        $this->humbleProfile = $humbleProfile;
        return $this;
    }
}
