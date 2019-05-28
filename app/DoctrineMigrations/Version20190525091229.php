<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190525091229 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_55801030F6BD1646');
        $this->addSql('CREATE TABLE _core.org (id INT AUTO_INCREMENT NOT NULL, owner INT DEFAULT NULL, name VARCHAR(255) NOT NULL, website VARCHAR(255) NOT NULL, facebook VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, description VARCHAR(1024) DEFAULT NULL, lends VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_B2F9EE355E237E06 (name), UNIQUE INDEX UNIQ_B2F9EE35CF60E67C (owner), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE _core.org_site (id INT AUTO_INCREMENT NOT NULL, org INT DEFAULT NULL, name VARCHAR(128) NOT NULL, description VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, country VARCHAR(2) NOT NULL, latitude VARCHAR(32) DEFAULT NULL, longitude VARCHAR(32) DEFAULT NULL, postcode VARCHAR(32) NOT NULL, status VARCHAR(16) NOT NULL, added_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7E1FC677215BA80 (org), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE _core.contact (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', first_name VARCHAR(32) DEFAULT NULL, last_name VARCHAR(32) DEFAULT NULL, is_active TINYINT(1) DEFAULT \'1\' NOT NULL, created_at DATETIME DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, email_canonical VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, username_canonical VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_64D4A0ACC05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE _core.org ADD CONSTRAINT FK_B2F9EE35CF60E67C FOREIGN KEY (owner) REFERENCES _core.contact (id)');
        $this->addSql('ALTER TABLE _core.org_site ADD CONSTRAINT FK_7E1FC677215BA80 FOREIGN KEY (org) REFERENCES _core.org (id)');
        $this->addSql('DROP TABLE tenant_site');
        $this->addSql('DROP INDEX UNIQ_55801030F6BD1646 ON account');
        $this->addSql('ALTER TABLE account DROP site_id, CHANGE schema_version schema_version VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tenant_site (id INT AUTO_INCREMENT NOT NULL, tenant INT DEFAULT NULL, unique_id VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(128) NOT NULL COLLATE utf8_unicode_ci, description VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, address VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci, postcode VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, status VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci, added_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, latitude VARCHAR(32) DEFAULT NULL COLLATE utf8_unicode_ci, longitude VARCHAR(32) DEFAULT NULL COLLATE utf8_unicode_ci, INDEX IDX_E23375724E59C462 (tenant), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tenant_site ADD CONSTRAINT FK_E23375724E59C462 FOREIGN KEY (tenant) REFERENCES account (id)');
        $this->addSql('DROP TABLE _core.org');
        $this->addSql('DROP TABLE _core.org_site');
        $this->addSql('DROP TABLE _core.contact');
        $this->addSql('ALTER TABLE _core.account ADD site_id INT DEFAULT NULL, CHANGE schema_version schema_version VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE _core.account ADD CONSTRAINT FK_55801030F6BD1646 FOREIGN KEY (site_id) REFERENCES tenant_site (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55801030F6BD1646 ON _core.account (site_id)');
    }
}
