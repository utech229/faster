<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220623075541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operator ADD status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE operator ADD CONSTRAINT FK_D7A6A7816BF700BD FOREIGN KEY (status_id) REFERENCES `status` (id)');
        $this->addSql('CREATE INDEX IDX_D7A6A7816BF700BD ON operator (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operator DROP FOREIGN KEY FK_D7A6A7816BF700BD');
        $this->addSql('DROP INDEX IDX_D7A6A7816BF700BD ON operator');
        $this->addSql('ALTER TABLE operator DROP status_id');
    }
}
