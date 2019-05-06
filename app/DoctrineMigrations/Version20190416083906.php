<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190416083906 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item_type DROP FOREIGN KEY FK_44EE13D2727ACA70');
        $this->addSql('DROP TABLE item_type');
        $this->addSql('ALTER TABLE tenant_note RENAME INDEX idx_85d2a0c74e59c462 TO IDX_44CD86824E59C462');
        $this->addSql('ALTER TABLE loan RENAME INDEX idx_c5d30d03a18098bc TO IDX_7E18A654A18098BC');
        $this->addSql('DROP INDEX UNIQ_7D3656A4FAC29D25 ON account');
        $this->addSql('ALTER TABLE account ADD site_id INT DEFAULT NULL, CHANGE server_name server_name VARCHAR(255) NOT NULL, CHANGE schema_version schema_version VARCHAR(255) NOT NULL, CHANGE time_zone time_zone VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_55801030F6BD1646 FOREIGN KEY (site_id) REFERENCES _core.tenant_site (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55801030F6BD1646 ON account (site_id)');
        $this->addSql('ALTER TABLE tenant_site RENAME INDEX idx_d288a2b3a18098bc TO IDX_E2337572A18098BC');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE item_type (id INT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(128) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_44EE13D2727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item_type ADD CONSTRAINT FK_44EE13D2727ACA70 FOREIGN KEY (parent_id) REFERENCES item_type (id)');
        $this->addSql('ALTER TABLE _core.account DROP FOREIGN KEY FK_55801030F6BD1646');
        $this->addSql('DROP INDEX UNIQ_55801030F6BD1646 ON _core.account');
        $this->addSql('ALTER TABLE _core.account DROP site_id, CHANGE server_name server_name VARCHAR(255) DEFAULT \'dev\' NOT NULL COLLATE utf8mb4_general_ci, CHANGE time_zone time_zone VARCHAR(255) DEFAULT \'Europe/London\' NOT NULL COLLATE utf8mb4_general_ci, CHANGE schema_version schema_version VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A4FAC29D25 ON _core.account (stub)');
        $this->addSql('ALTER TABLE _core.loan RENAME INDEX idx_7e18a654a18098bc TO IDX_C5D30D03A18098BC');
        $this->addSql('ALTER TABLE _core.tenant_note RENAME INDEX idx_44cd86824e59c462 TO IDX_85D2A0C74E59C462');
        $this->addSql('ALTER TABLE _core.tenant_site RENAME INDEX idx_e2337572a18098bc TO IDX_D288A2B3A18098BC');
    }
}
