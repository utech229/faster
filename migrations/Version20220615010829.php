<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615010829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sender ADD create_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sender ADD CONSTRAINT FK_5F004ACF9E085865 FOREIGN KEY (create_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5F004ACF9E085865 ON sender (create_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sender DROP FOREIGN KEY FK_5F004ACF9E085865');
        $this->addSql('DROP INDEX IDX_5F004ACF9E085865 ON sender');
        $this->addSql('ALTER TABLE sender DROP create_by_id');
    }
}
