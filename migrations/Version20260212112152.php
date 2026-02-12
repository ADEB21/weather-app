<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212112152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, added_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX unique_location ON favorite (latitude, longitude)');
        $this->addSql('CREATE TABLE search_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, searched_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX idx_location_date ON search_history (latitude, longitude, searched_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE favorite');
        $this->addSql('DROP TABLE search_history');
    }
}
