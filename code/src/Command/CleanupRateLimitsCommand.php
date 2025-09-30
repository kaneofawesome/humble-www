<?php

namespace App\Command;

use App\Repository\RateLimitEntryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-rate-limits',
    description: 'Clean up expired rate limit entries older than 1 hour',
)]
class CleanupRateLimitsCommand extends Command
{
    public function __construct(
        private RateLimitEntryRepository $rateLimitRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $deletedCount = $this->rateLimitRepository->cleanupExpiredEntries();

            if ($deletedCount > 0) {
                $io->success(sprintf('Cleaned up %d expired rate limit entries.', $deletedCount));
            } else {
                $io->info('No expired rate limit entries found.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to cleanup rate limit entries: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}