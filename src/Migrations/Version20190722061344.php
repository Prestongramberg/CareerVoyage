<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190722061344 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE professional_user DROP FOREIGN KEY FK_9FD6EF97979B1AD6');
        $this->addSql('ALTER TABLE professional_user ADD CONSTRAINT FK_9FD6EF97979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE professional_user DROP FOREIGN KEY FK_9FD6EF97979B1AD6');
        $this->addSql('ALTER TABLE professional_user ADD CONSTRAINT FK_9FD6EF97979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }
}
