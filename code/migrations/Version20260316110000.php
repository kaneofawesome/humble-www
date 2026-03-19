<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table for authentication and account management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, display_name VARCHAR(100) DEFAULT NULL, is_verified BOOLEAN NOT NULL DEFAULT FALSE, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, account_status VARCHAR(20) NOT NULL DEFAULT \'active\', warning_count INTEGER NOT NULL DEFAULT 0, locked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, lock_reason TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uq_users_email ON users (email)');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.locked_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
