<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613095455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C56BF700BD');
        $this->addSql('DROP INDEX IDX_8F3F68C56BF700BD ON log');
        $this->addSql('ALTER TABLE log ADD status VARCHAR(10) NOT NULL, DROP status_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log ADD status_id INT DEFAULT NULL, DROP status');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C56BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C56BF700BD ON log (status_id)');
    }
}
