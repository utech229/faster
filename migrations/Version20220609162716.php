<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220609162716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recharge ADD before_commission DOUBLE PRECISION NOT NULL, ADD commission DOUBLE PRECISION NOT NULL, ADD after_commission DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE transaction DROP before_commission, DROP commission, DROP after_commission');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recharge DROP before_commission, DROP commission, DROP after_commission');
        $this->addSql('ALTER TABLE transaction ADD before_commission DOUBLE PRECISION NOT NULL, ADD commission DOUBLE PRECISION NOT NULL, ADD after_commission DOUBLE PRECISION NOT NULL');
    }

}
