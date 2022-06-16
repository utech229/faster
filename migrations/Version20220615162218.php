<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615162218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smscampaign ADD sender VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE smsmessage ADD sender VARCHAR(15) NOT NULL, CHANGE campaign_amount message_amount DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smscampaign DROP sender');
        $this->addSql('ALTER TABLE smsmessage DROP sender, CHANGE message_amount campaign_amount DOUBLE PRECISION NOT NULL');
    }
}
