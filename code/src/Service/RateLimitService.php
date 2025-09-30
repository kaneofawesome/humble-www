<?php

namespace App\Service;

use App\Entity\RateLimitEntry;
use App\Repository\RateLimitEntryRepository;
use Psr\Log\LoggerInterface;

class RateLimitService
{
    private bool $isAvailable = true;

    public function __construct(
        private RateLimitEntryRepository $rateLimitRepository,
        private LoggerInterface $logger
    ) {
    }

    public function isRateLimited(string $ipAddress): bool
    {
        try {
            $entry = $this->rateLimitRepository->findByIpAddress($ipAddress);

            if (!$entry) {
                // No previous submissions from this IP
                return false;
            }

            if ($entry->isExpired()) {
                // Entry exists but is expired, clean it up
                $this->rateLimitRepository->remove($entry, true);
                $this->logger->info('Removed expired rate limit entry', [
                    'ip_address' => $ipAddress
                ]);
                return false;
            }

            $isLimited = $entry->getSubmissionCount() >= 5;

            if ($isLimited) {
                $this->logger->warning('Rate limit exceeded', [
                    'ip_address' => $ipAddress,
                    'submission_count' => $entry->getSubmissionCount(),
                    'first_submission' => $entry->getFirstSubmissionAt()->format('Y-m-d H:i:s'),
                    'last_submission' => $entry->getLastSubmissionAt()->format('Y-m-d H:i:s')
                ]);
            }

            return $isLimited;

        } catch (\Exception $e) {
            $this->isAvailable = false;
            $this->logger->error('Error checking rate limit', [
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ]);

            return false;
        }
    }

    public function updateSubmissionCount(string $ipAddress): void
    {
        try {
            $entry = $this->rateLimitRepository->findByIpAddress($ipAddress);

            if (!$entry) {
                // First submission from this IP
                $entry = new RateLimitEntry();
                $entry->setIpAddress($ipAddress);
                $entry->setFirstSubmissionAt(new \DateTimeImmutable());
                $entry->setLastSubmissionAt(new \DateTimeImmutable());
                $entry->setSubmissionCount(1);

                $this->rateLimitRepository->save($entry, true);

                $this->logger->info('Created new rate limit entry', [
                    'ip_address' => $ipAddress,
                    'submission_count' => 1
                ]);

            } else {
                // Update existing entry
                $now = new \DateTimeImmutable();

                if ($entry->isWithinHourWindow()) {
                    // Within the hour window, increment count
                    $entry->incrementSubmissionCount();
                    $entry->setLastSubmissionAt($now);
                } else {
                    // Hour window expired, reset the counter
                    $entry->setSubmissionCount(1);
                    $entry->setFirstSubmissionAt($now);
                    $entry->setLastSubmissionAt($now);
                }

                $this->rateLimitRepository->save($entry, true);

                $this->logger->info('Updated rate limit entry', [
                    'ip_address' => $ipAddress,
                    'submission_count' => $entry->getSubmissionCount(),
                    'within_window' => $entry->isWithinHourWindow()
                ]);
            }

        } catch (\Exception $e) {
            $this->isAvailable = false;
            $this->logger->error('Error updating rate limit', [
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ]);
        }
    }

    public function getRemainingSubmissions(string $ipAddress): int
    {
        try {
            $entry = $this->rateLimitRepository->findByIpAddress($ipAddress);

            if (!$entry || $entry->isExpired()) {
                return 5; // Full limit available
            }

            return max(0, 5 - $entry->getSubmissionCount());

        } catch (\Exception $e) {
            $this->logger->error('Error getting remaining submissions', [
                'ip_address' => $ipAddress,
                'error' => $e->getMessage()
            ]);

            return 5; // Assume full limit available on error
        }
    }

    public function getTimeUntilReset(string $ipAddress): ?\DateTimeInterface
    {
        try {
            $entry = $this->rateLimitRepository->findByIpAddress($ipAddress);

            if (!$entry || $entry->isExpired()) {
                return null; // No reset needed
            }

            // Rate limit resets 1 hour after first submission
            return $entry->getFirstSubmissionAt()->add(new \DateInterval('PT1H'));

        } catch (\Exception $e) {
            $this->logger->error('Error getting reset time', [
                'ip_address' => $ipAddress,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    public function cleanupExpiredEntries(): int
    {
        try {
            $deletedCount = $this->rateLimitRepository->cleanupExpiredEntries();

            $this->logger->info('Cleaned up expired rate limit entries', [
                'deleted_count' => $deletedCount
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            $this->logger->error('Error cleaning up expired entries', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Check if the rate limiting service is currently available
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Get a user-friendly status message about rate limiting availability
     */
    public function getStatusMessage(): string
    {
        if ($this->isAvailable) {
            return 'Rate limiting is active';
        }

        return 'Rate limiting temporarily unavailable (database connection issue)';
    }
}
