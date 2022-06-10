<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220610133224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE router ADD status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE router ADD CONSTRAINT FK_45D2F2256BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('CREATE INDEX IDX_45D2F2256BF700BD ON router (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE router DROP FOREIGN KEY FK_45D2F2256BF700BD');
        $this->addSql('DROP INDEX IDX_45D2F2256BF700BD ON router');
        $this->addSql('ALTER TABLE router DROP status_id');
    }
}
