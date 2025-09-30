<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926122040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create rate_limit_entries table for tracking form submission rate limiting';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rate_limit_entries (
            id SERIAL PRIMARY KEY,
            ip_address VARCHAR(45) UNIQUE NOT NULL,
            submission_count INTEGER DEFAULT 1 CHECK (submission_count <= 5),
            first_submission_at TIMESTAMP WITH TIME ZONE NOT NULL,
            last_submission_at TIMESTAMP WITH TIME ZONE NOT NULL,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
            updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        )');

        $this->addSql('CREATE INDEX idx_rate_limit_ip ON rate_limit_entries (ip_address)');
        $this->addSql('CREATE INDEX idx_rate_limit_cleanup ON rate_limit_entries (first_submission_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rate_limit_entries');
    }
}
