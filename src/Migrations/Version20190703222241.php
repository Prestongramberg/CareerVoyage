<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190703222241 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professional_user ADD photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE professional_user ADD CONSTRAINT FK_9FD6EF977E9E4C8C FOREIGN KEY (photo_id) REFERENCES file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9FD6EF977E9E4C8C ON professional_user (photo_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE professional_user DROP FOREIGN KEY FK_9FD6EF977E9E4C8C');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP INDEX UNIQ_9FD6EF977E9E4C8C ON professional_user');
        $this->addSql('ALTER TABLE professional_user DROP photo_id');
    }
}
