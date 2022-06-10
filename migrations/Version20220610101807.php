<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220610101807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64934ECB4E6');
        $this->addSql('CREATE TABLE router (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, uid VARCHAR(25) NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(200) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_45D2F225783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE router ADD CONSTRAINT FK_45D2F225783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE route');
        $this->addSql('DROP INDEX IDX_8D93D64934ECB4E6 ON user');
        $this->addSql('ALTER TABLE user CHANGE route_id router_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649169071B9 FOREIGN KEY (router_id) REFERENCES router (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649169071B9 ON user (router_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649169071B9');
        $this->addSql('CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, uid VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2C42079783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C42079783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE router');
        $this->addSql('DROP INDEX IDX_8D93D649169071B9 ON user');
        $this->addSql('ALTER TABLE user CHANGE router_id route_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64934ECB4E6 FOREIGN KEY (route_id) REFERENCES route (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64934ECB4E6 ON user (route_id)');
    }
}
