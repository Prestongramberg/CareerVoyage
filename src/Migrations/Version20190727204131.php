<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190727204131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE company_photo DROP FOREIGN KEY FK_5346267D979B1AD6');
        $this->addSql('ALTER TABLE company_photo ADD CONSTRAINT FK_5346267D979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_resource DROP FOREIGN KEY FK_A406E17B979B1AD6');
        $this->addSql('ALTER TABLE company_resource ADD CONSTRAINT FK_A406E17B979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE company_photo DROP FOREIGN KEY FK_5346267D979B1AD6');
        $this->addSql('ALTER TABLE company_photo ADD CONSTRAINT FK_5346267D979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company_resource DROP FOREIGN KEY FK_A406E17B979B1AD6');
        $this->addSql('ALTER TABLE company_resource ADD CONSTRAINT FK_A406E17B979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }
}
