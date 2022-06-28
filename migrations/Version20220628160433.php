<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220628160433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment CHANGE status status_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D6BF700BD FOREIGN KEY (status_id) REFERENCES `status` (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D6BF700BD ON payment (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D6BF700BD');
        $this->addSql('DROP INDEX IDX_6D28840D6BF700BD ON payment');
        $this->addSql('ALTER TABLE payment CHANGE status_id status INT NOT NULL');
    }
}
