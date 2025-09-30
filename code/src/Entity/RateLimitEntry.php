<?php

namespace App\Entity;

use App\Repository\RateLimitEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RateLimitEntryRepository::class)]
#[ORM\Table(name: 'rate_limit_entries')]
#[ORM\Index(name: 'idx_rate_limit_ip', columns: ['ip_address'])]
#[ORM\Index(name: 'idx_rate_limit_cleanup', columns: ['first_submission_at'])]
class RateLimitEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'ip_address', length: 45, unique: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'submission_count', type: Types::INTEGER, options: ['default' => 1])]
    private int $submissionCount = 1;

    #[ORM\Column(name: 'first_submission_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $firstSubmissionAt = null;

    #[ORM\Column(name: 'last_submission_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $lastSubmissionAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getSubmissionCount(): int
    {
        return $this->submissionCount;
    }

    public function setSubmissionCount(int $submissionCount): static
    {
        $this->submissionCount = $submissionCount;
        return $this;
    }

    public function incrementSubmissionCount(): static
    {
        $this->submissionCount++;
        $this->setUpdatedAt(new \DateTimeImmutable());
        return $this;
    }

    public function getFirstSubmissionAt(): ?\DateTimeImmutable
    {
        return $this->firstSubmissionAt;
    }

    public function setFirstSubmissionAt(\DateTimeImmutable $firstSubmissionAt): static
    {
        $this->firstSubmissionAt = $firstSubmissionAt;
        return $this;
    }

    public function getLastSubmissionAt(): ?\DateTimeImmutable
    {
        return $this->lastSubmissionAt;
    }

    public function setLastSubmissionAt(\DateTimeImmutable $lastSubmissionAt): static
    {
        $this->lastSubmissionAt = $lastSubmissionAt;
        $this->setUpdatedAt(new \DateTimeImmutable());
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isWithinHourWindow(): bool
    {
        if (!$this->firstSubmissionAt) {
            return false;
        }

        $hourAgo = new \DateTimeImmutable('-1 hour');
        return $this->firstSubmissionAt > $hourAgo;
    }

    public function canAcceptSubmission(): bool
    {
        return $this->submissionCount < 5 && $this->isWithinHourWindow();
    }

    public function isExpired(): bool
    {
        return !$this->isWithinHourWindow();
    }
}