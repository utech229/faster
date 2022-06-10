<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220610074635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authorization (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, permission_id INT DEFAULT NULL, status_id INT NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7A6D8BEFD60322AC (role_id), INDEX IDX_7A6D8BEFFED90CCA (permission_id), INDEX IDX_7A6D8BEF6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, validator_id INT DEFAULT NULL, status_id INT NOT NULL, uid VARCHAR(25) NOT NULL, name VARCHAR(50) NOT NULL, site_url VARCHAR(100) NOT NULL, logo VARCHAR(100) NOT NULL, favicon VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, noreply_email VARCHAR(100) NOT NULL, is_default TINYINT(1) NOT NULL, phone VARCHAR(200) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', commission DOUBLE PRECISION NOT NULL, INDEX IDX_1C52F958783E3463 (manager_id), INDEX IDX_1C52F958B0644AEC (validator_id), INDEX IDX_1C52F9586BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, manager_id INT DEFAULT NULL, uid VARCHAR(25) NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', email VARCHAR(100) NOT NULL, ifu VARCHAR(25) NOT NULL, rccm VARCHAR(25) NOT NULL, address LONGTEXT NOT NULL, phone VARCHAR(25) NOT NULL, INDEX IDX_4FBF094F6BF700BD (status_id), UNIQUE INDEX UNIQ_4FBF094F783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(25) NOT NULL, phone VARCHAR(20) NOT NULL, is_imported TINYINT(1) NOT NULL, phone_country LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_group (id INT AUTO_INCREMENT NOT NULL, manager_id INT NOT NULL, uid VARCHAR(25) NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_40EA54CA783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_group_field (id INT AUTO_INCREMENT NOT NULL, contact_group_id INT NOT NULL, uid VARCHAR(25) NOT NULL, field1 VARCHAR(50) DEFAULT NULL, field2 VARCHAR(50) DEFAULT NULL, field3 VARCHAR(50) DEFAULT NULL, field4 VARCHAR(50) DEFAULT NULL, field5 VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_6575D181647145D0 (contact_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_index (id INT AUTO_INCREMENT NOT NULL, contact_group_id INT NOT NULL, contact_id INT NOT NULL, uid VARCHAR(25) NOT NULL, field1 VARCHAR(50) DEFAULT NULL, field2 VARCHAR(50) DEFAULT NULL, field3 VARCHAR(50) DEFAULT NULL, field4 VARCHAR(50) DEFAULT NULL, field5 VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AD59770E647145D0 (contact_group_id), INDEX IDX_AD59770EE7A1254A (contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extra_authorization (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, permission_id INT NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E10EB766A76ED395 (user_id), INDEX IDX_E10EB766FED90CCA (permission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, status_id INT DEFAULT NULL, ip VARCHAR(50) NOT NULL, agent LONGTEXT NOT NULL, task LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8F3F68C5A76ED395 (user_id), INDEX IDX_8F3F68C56BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, code VARCHAR(5) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E04992AA6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recharge (id INT AUTO_INCREMENT NOT NULL, transaction_id INT NOT NULL, user_id INT NOT NULL, recharge_by_id INT DEFAULT NULL, status_id INT NOT NULL, uid VARCHAR(25) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', before_commission DOUBLE PRECISION NOT NULL, commission DOUBLE PRECISION NOT NULL, after_commission DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_B6702F512FC0CB0F (transaction_id), INDEX IDX_B6702F51A76ED395 (user_id), INDEX IDX_B6702F51E708D21 (recharge_by_id), INDEX IDX_B6702F516BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reseller_request (id INT AUTO_INCREMENT NOT NULL, brand_id INT NOT NULL, user_id INT NOT NULL, uid VARCHAR(25) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_F4E6690E44F5D008 (brand_id), INDEX IDX_F4E6690EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, status_id INT NOT NULL, code VARCHAR(5) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, level INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_57698A6A783E3463 (manager_id), INDEX IDX_57698A6A6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, uid VARCHAR(25) NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(200) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2C42079783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sender (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, status_id INT NOT NULL, uid VARCHAR(25) NOT NULL, name VARCHAR(11) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', observation LONGTEXT DEFAULT NULL, INDEX IDX_5F004ACF783E3463 (manager_id), INDEX IDX_5F004ACF6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE smscampaign (id INT AUTO_INCREMENT NOT NULL, manager_id INT NOT NULL, sending_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', message LONGTEXT NOT NULL, campaign_amount DOUBLE PRECISION NOT NULL, sms_type TINYINT(1) NOT NULL, is_parameterized TINYINT(1) NOT NULL, timezone VARCHAR(10) NOT NULL, INDEX IDX_9B47387A783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE smsmessage (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, campaign_id INT DEFAULT NULL, status_id INT NOT NULL, sending_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', message LONGTEXT NOT NULL, campaign_amount DOUBLE PRECISION NOT NULL, sms_type TINYINT(1) NOT NULL, is_parameterized TINYINT(1) NOT NULL, timezone VARCHAR(10) NOT NULL, INDEX IDX_5AD6496C783E3463 (manager_id), INDEX IDX_5AD6496CF639F774 (campaign_id), INDEX IDX_5AD6496C6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE smsmessage_file (id INT AUTO_INCREMENT NOT NULL, campaign_id INT NOT NULL, name VARCHAR(150) DEFAULT NULL, url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_A23437ACF639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solde_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, status_id INT NOT NULL, uid VARCHAR(25) NOT NULL, min_solde DOUBLE PRECISION NOT NULL, email1 VARCHAR(100) DEFAULT NULL, email2 VARCHAR(100) DEFAULT NULL, email3 VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_45B62FD6A76ED395 (user_id), INDEX IDX_45B62FD66BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, code INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, status_id INT NOT NULL, transaction_id VARCHAR(150) NOT NULL, reference VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', before_balance DOUBLE PRECISION DEFAULT NULL, after_balance DOUBLE PRECISION NOT NULL, INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D16BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, role_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, brand_id INT DEFAULT NULL, status_id INT NOT NULL, route_id INT NOT NULL, default_sender_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, uid VARCHAR(100) NOT NULL, phone VARCHAR(20) DEFAULT NULL, balance DOUBLE PRECISION DEFAULT NULL, profile_photo VARCHAR(255) DEFAULT NULL, apikey VARCHAR(255) NOT NULL, country LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', is_dlr TINYINT(1) DEFAULT NULL, price LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', post_pay TINYINT(1) DEFAULT NULL, active_code VARCHAR(10) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649D60322AC (role_id), INDEX IDX_8D93D649642B8210 (admin_id), INDEX IDX_8D93D64944F5D008 (brand_id), INDEX IDX_8D93D6496BF700BD (status_id), INDEX IDX_8D93D64934ECB4E6 (route_id), INDEX IDX_8D93D64967469B3A (default_sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usetting (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, uid VARCHAR(100) NOT NULL, currency LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', language LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', timezone VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_838A277BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE authorization ADD CONSTRAINT FK_7A6D8BEFD60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE authorization ADD CONSTRAINT FK_7A6D8BEFFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id)');
        $this->addSql('ALTER TABLE authorization ADD CONSTRAINT FK_7A6D8BEF6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958B0644AEC FOREIGN KEY (validator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F9586BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact_group ADD CONSTRAINT FK_40EA54CA783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact_group_field ADD CONSTRAINT FK_6575D181647145D0 FOREIGN KEY (contact_group_id) REFERENCES contact_group (id)');
        $this->addSql('ALTER TABLE contact_index ADD CONSTRAINT FK_AD59770E647145D0 FOREIGN KEY (contact_group_id) REFERENCES contact_group (id)');
        $this->addSql('ALTER TABLE contact_index ADD CONSTRAINT FK_AD59770EE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id)');
        $this->addSql('ALTER TABLE extra_authorization ADD CONSTRAINT FK_E10EB766A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE extra_authorization ADD CONSTRAINT FK_E10EB766FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C56BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AA6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE recharge ADD CONSTRAINT FK_B6702F512FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE recharge ADD CONSTRAINT FK_B6702F51A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recharge ADD CONSTRAINT FK_B6702F51E708D21 FOREIGN KEY (recharge_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recharge ADD CONSTRAINT FK_B6702F516BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE reseller_request ADD CONSTRAINT FK_F4E6690E44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE reseller_request ADD CONSTRAINT FK_F4E6690EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C42079783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sender ADD CONSTRAINT FK_5F004ACF783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sender ADD CONSTRAINT FK_5F004ACF6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE smscampaign ADD CONSTRAINT FK_9B47387A783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE smsmessage ADD CONSTRAINT FK_5AD6496C783E3463 FOREIGN KEY (manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE smsmessage ADD CONSTRAINT FK_5AD6496CF639F774 FOREIGN KEY (campaign_id) REFERENCES smscampaign (id)');
        $this->addSql('ALTER TABLE smsmessage ADD CONSTRAINT FK_5AD6496C6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE smsmessage_file ADD CONSTRAINT FK_A23437ACF639F774 FOREIGN KEY (campaign_id) REFERENCES smscampaign (id)');
        $this->addSql('ALTER TABLE solde_notification ADD CONSTRAINT FK_45B62FD6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE solde_notification ADD CONSTRAINT FK_45B62FD66BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D16BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64944F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64934ECB4E6 FOREIGN KEY (route_id) REFERENCES route (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64967469B3A FOREIGN KEY (default_sender_id) REFERENCES sender (id)');
        $this->addSql('ALTER TABLE usetting ADD CONSTRAINT FK_838A277BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reseller_request DROP FOREIGN KEY FK_F4E6690E44F5D008');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64944F5D008');
        $this->addSql('ALTER TABLE contact_index DROP FOREIGN KEY FK_AD59770EE7A1254A');
        $this->addSql('ALTER TABLE contact_group_field DROP FOREIGN KEY FK_6575D181647145D0');
        $this->addSql('ALTER TABLE contact_index DROP FOREIGN KEY FK_AD59770E647145D0');
        $this->addSql('ALTER TABLE authorization DROP FOREIGN KEY FK_7A6D8BEFFED90CCA');
        $this->addSql('ALTER TABLE extra_authorization DROP FOREIGN KEY FK_E10EB766FED90CCA');
        $this->addSql('ALTER TABLE authorization DROP FOREIGN KEY FK_7A6D8BEFD60322AC');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64934ECB4E6');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64967469B3A');
        $this->addSql('ALTER TABLE smsmessage DROP FOREIGN KEY FK_5AD6496CF639F774');
        $this->addSql('ALTER TABLE smsmessage_file DROP FOREIGN KEY FK_A23437ACF639F774');
        $this->addSql('ALTER TABLE authorization DROP FOREIGN KEY FK_7A6D8BEF6BF700BD');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F9586BF700BD');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F6BF700BD');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C56BF700BD');
        $this->addSql('ALTER TABLE permission DROP FOREIGN KEY FK_E04992AA6BF700BD');
        $this->addSql('ALTER TABLE recharge DROP FOREIGN KEY FK_B6702F516BF700BD');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6A6BF700BD');
        $this->addSql('ALTER TABLE sender DROP FOREIGN KEY FK_5F004ACF6BF700BD');
        $this->addSql('ALTER TABLE smsmessage DROP FOREIGN KEY FK_5AD6496C6BF700BD');
        $this->addSql('ALTER TABLE solde_notification DROP FOREIGN KEY FK_45B62FD66BF700BD');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D16BF700BD');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496BF700BD');
        $this->addSql('ALTER TABLE recharge DROP FOREIGN KEY FK_B6702F512FC0CB0F');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F958783E3463');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F958B0644AEC');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F783E3463');
        $this->addSql('ALTER TABLE contact_group DROP FOREIGN KEY FK_40EA54CA783E3463');
        $this->addSql('ALTER TABLE extra_authorization DROP FOREIGN KEY FK_E10EB766A76ED395');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE recharge DROP FOREIGN KEY FK_B6702F51A76ED395');
        $this->addSql('ALTER TABLE recharge DROP FOREIGN KEY FK_B6702F51E708D21');
        $this->addSql('ALTER TABLE reseller_request DROP FOREIGN KEY FK_F4E6690EA76ED395');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6A783E3463');
        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C42079783E3463');
        $this->addSql('ALTER TABLE sender DROP FOREIGN KEY FK_5F004ACF783E3463');
        $this->addSql('ALTER TABLE smscampaign DROP FOREIGN KEY FK_9B47387A783E3463');
        $this->addSql('ALTER TABLE smsmessage DROP FOREIGN KEY FK_5AD6496C783E3463');
        $this->addSql('ALTER TABLE solde_notification DROP FOREIGN KEY FK_45B62FD6A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649642B8210');
        $this->addSql('ALTER TABLE usetting DROP FOREIGN KEY FK_838A277BA76ED395');
        $this->addSql('DROP TABLE authorization');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE contact_group');
        $this->addSql('DROP TABLE contact_group_field');
        $this->addSql('DROP TABLE contact_index');
        $this->addSql('DROP TABLE extra_authorization');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE recharge');
        $this->addSql('DROP TABLE reseller_request');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE route');
        $this->addSql('DROP TABLE sender');
        $this->addSql('DROP TABLE smscampaign');
        $this->addSql('DROP TABLE smsmessage');
        $this->addSql('DROP TABLE smsmessage_file');
        $this->addSql('DROP TABLE solde_notification');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE usetting');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
