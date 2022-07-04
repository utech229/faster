<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220703210634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand DROP observations');
        $this->addSql('ALTER TABLE contact_group ADD admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_group ADD CONSTRAINT FK_40EA54CA642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_40EA54CA642B8210 ON contact_group (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand ADD observations LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_group DROP FOREIGN KEY FK_40EA54CA642B8210');
        $this->addSql('DROP INDEX IDX_40EA54CA642B8210 ON contact_group');
        $this->addSql('ALTER TABLE contact_group DROP admin_id');
    }
}
