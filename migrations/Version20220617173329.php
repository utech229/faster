<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220617173329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smscampaign ADD create_by_id INT NOT NULL, ADD name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE smscampaign ADD CONSTRAINT FK_9B47387A9E085865 FOREIGN KEY (create_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9B47387A9E085865 ON smscampaign (create_by_id)');
        $this->addSql('ALTER TABLE smsmessage ADD create_by VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smscampaign DROP FOREIGN KEY FK_9B47387A9E085865');
        $this->addSql('DROP INDEX IDX_9B47387A9E085865 ON smscampaign');
        $this->addSql('ALTER TABLE smscampaign DROP create_by_id, DROP name');
        $this->addSql('ALTER TABLE smsmessage DROP create_by');
    }
}
