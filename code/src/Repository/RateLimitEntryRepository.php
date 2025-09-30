<?php

namespace App\Repository;

use App\Entity\RateLimitEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RateLimitEntry>
 */
class RateLimitEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RateLimitEntry::class);
    }

    public function findByIpAddress(string $ipAddress): ?RateLimitEntry
    {
        return $this->findOneBy(['ipAddress' => $ipAddress]);
    }

    public function findOrCreateByIpAddress(string $ipAddress): RateLimitEntry
    {
        $entry = $this->findByIpAddress($ipAddress);

        if (!$entry) {
            $entry = new RateLimitEntry();
            $entry->setIpAddress($ipAddress);
            $entry->setFirstSubmissionAt(new \DateTimeImmutable());
            $entry->setLastSubmissionAt(new \DateTimeImmutable());

            $this->getEntityManager()->persist($entry);
            $this->getEntityManager()->flush();
        }

        return $entry;
    }

    public function updateSubmissionCount(RateLimitEntry $entry): void
    {
        $now = new \DateTimeImmutable();

        if ($entry->isWithinHourWindow()) {
            $entry->incrementSubmissionCount();
            $entry->setLastSubmissionAt($now);
        } else {
            $entry->setSubmissionCount(1);
            $entry->setFirstSubmissionAt($now);
            $entry->setLastSubmissionAt($now);
        }

        $this->getEntityManager()->flush();
    }

    public function isRateLimited(string $ipAddress): bool
    {
        $entry = $this->findByIpAddress($ipAddress);

        if (!$entry) {
            return false;
        }

        if ($entry->isExpired()) {
            return false;
        }

        return $entry->getSubmissionCount() >= 5;
    }

    public function cleanupExpiredEntries(): int
    {
        $oneHourAgo = new \DateTimeImmutable('-1 hour');

        $qb = $this->createQueryBuilder('r');
        $qb->delete()
           ->where('r.firstSubmissionAt < :oneHourAgo')
           ->setParameter('oneHourAgo', $oneHourAgo);

        return $qb->getQuery()->execute();
    }

    public function save(RateLimitEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RateLimitEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}