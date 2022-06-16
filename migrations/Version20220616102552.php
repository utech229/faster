<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220616102552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand ADD creator_id INT NOT NULL');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F95861220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1C52F95861220EA6 ON brand (creator_id)');
        $this->addSql('ALTER TABLE user CHANGE is_dlr is_dlr TINYINT(1) NOT NULL, CHANGE post_pay post_pay TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F95861220EA6');
        $this->addSql('DROP INDEX IDX_1C52F95861220EA6 ON brand');
        $this->addSql('ALTER TABLE brand DROP creator_id');
        $this->addSql('ALTER TABLE user CHANGE is_dlr is_dlr TINYINT(1) DEFAULT NULL, CHANGE post_pay post_pay TINYINT(1) DEFAULT NULL');
    }
}
