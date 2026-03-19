<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create humble_profiles table for humble-www app-specific user data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE humble_profiles (id UUID NOT NULL, user_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uq_humble_profiles_user ON humble_profiles (user_id)');
        $this->addSql('ALTER TABLE humble_profiles ADD CONSTRAINT fk_humble_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('COMMENT ON COLUMN humble_profiles.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE humble_profiles DROP CONSTRAINT fk_humble_profiles_user');
        $this->addSql('DROP TABLE humble_profiles');
    }
}
