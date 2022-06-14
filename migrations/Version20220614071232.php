<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614071232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD affiliate_manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64949B17C9E FOREIGN KEY (affiliate_manager_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64949B17C9E ON user (affiliate_manager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64949B17C9E');
        $this->addSql('DROP INDEX IDX_8D93D64949B17C9E ON user');
        $this->addSql('ALTER TABLE user DROP affiliate_manager_id');
    }
}
