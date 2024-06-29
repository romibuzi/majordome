<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324202417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rule (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL)');
        $this->addSql('CREATE TABLE run (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, account_id VARCHAR(255) NOT NULL, provider VARCHAR(255) NOT NULL, region VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE TABLE violation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, run_id INTEGER DEFAULT NULL, rule_id INTEGER DEFAULT NULL, resource_id VARCHAR(255) NOT NULL, resource_type VARCHAR(255) NOT NULL, CONSTRAINT FK_E7BA44E284E3FEC4 FOREIGN KEY (run_id) REFERENCES run (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E7BA44E2744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E7BA44E284E3FEC4 ON violation (run_id)');
        $this->addSql('CREATE INDEX IDX_E7BA44E2744E0351 ON violation (rule_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE rule');
        $this->addSql('DROP TABLE run');
        $this->addSql('DROP TABLE violation');
    }
}
