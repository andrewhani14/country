<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250204120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create country table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE country (
            uuid VARCHAR(2) NOT NULL,
            name VARCHAR(255) NOT NULL,
            region VARCHAR(100) NOT NULL,
            sub_region VARCHAR(100) NOT NULL,
            demonym VARCHAR(100) NOT NULL,
            population BIGINT NOT NULL,
            independent TINYINT(1) NOT NULL,
            flag VARCHAR(500) NOT NULL,
            currency_name VARCHAR(255) DEFAULT NULL,
            currency_symbol VARCHAR(10) DEFAULT NULL,
            PRIMARY KEY(uuid)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE country');
    }
}
