<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220616150050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand ADD default_sender_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F95867469B3A FOREIGN KEY (default_sender_id) REFERENCES sender (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F95867469B3A ON brand (default_sender_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F95867469B3A');
        $this->addSql('DROP INDEX UNIQ_1C52F95867469B3A ON brand');
        $this->addSql('ALTER TABLE brand DROP default_sender_id');
    }
}
