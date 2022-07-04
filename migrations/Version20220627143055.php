<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220627143055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_index DROP FOREIGN KEY FK_AD59770E647145D0');
        $this->addSql('ALTER TABLE contact_index DROP FOREIGN KEY FK_AD59770EE7A1254A');
        $this->addSql('DROP INDEX IDX_AD59770E647145D0 ON contact_index');
        $this->addSql('DROP INDEX IDX_AD59770EE7A1254A ON contact_index');
        $this->addSql('ALTER TABLE contact_index DROP contact_group_id, DROP contact_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_index ADD contact_group_id INT NOT NULL, ADD contact_id INT NOT NULL');
        $this->addSql('ALTER TABLE contact_index ADD CONSTRAINT FK_AD59770E647145D0 FOREIGN KEY (contact_group_id) REFERENCES contact_group (id)');
        $this->addSql('ALTER TABLE contact_index ADD CONSTRAINT FK_AD59770EE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id)');
        $this->addSql('CREATE INDEX IDX_AD59770E647145D0 ON contact_index (contact_group_id)');
        $this->addSql('CREATE INDEX IDX_AD59770EE7A1254A ON contact_index (contact_id)');
    }
}
