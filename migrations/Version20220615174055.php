<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615174055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smscampaign ADD status_id INT NOT NULL, ADD uid VARCHAR(25) NOT NULL');
        $this->addSql('ALTER TABLE smscampaign ADD CONSTRAINT FK_9B47387A6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('CREATE INDEX IDX_9B47387A6BF700BD ON smscampaign (status_id)');
        $this->addSql('ALTER TABLE smsmessage ADD phone VARCHAR(20) NOT NULL, ADD phone_country LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', ADD uid VARCHAR(25) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smscampaign DROP FOREIGN KEY FK_9B47387A6BF700BD');
        $this->addSql('DROP INDEX IDX_9B47387A6BF700BD ON smscampaign');
        $this->addSql('ALTER TABLE smscampaign DROP status_id, DROP uid');
        $this->addSql('ALTER TABLE smsmessage DROP phone, DROP phone_country, DROP uid');
    }
}
