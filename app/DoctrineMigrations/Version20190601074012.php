<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190601074012 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE org CHANGE website website VARCHAR(255) DEFAULT NULL, CHANGE lends lends VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD created_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_64D4A0ACDE12AB56 FOREIGN KEY (created_by) REFERENCES _core.contact (id)');
        $this->addSql('CREATE INDEX IDX_64D4A0ACDE12AB56 ON contact (created_by)');
        $this->addSql('ALTER TABLE org ADD created_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE org ADD CONSTRAINT FK_B2F9EE35DE12AB56 FOREIGN KEY (created_by) REFERENCES _core.contact (id)');
        $this->addSql('CREATE INDEX IDX_B2F9EE35DE12AB56 ON org (created_by)');

        $this->addSql('update contact set created_by = id where enabled = true');
        $this->addSql('update contact set created_by = 1 where created_by IS NULL');
        $this->addSql('update org set created_by = 1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
