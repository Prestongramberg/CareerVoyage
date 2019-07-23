<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190723045431 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE experience CHANGE brief_description brief_description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE experience_file ADD experience_id INT NOT NULL');
        $this->addSql('ALTER TABLE experience_file ADD CONSTRAINT FK_D88FF90D46E90E27 FOREIGN KEY (experience_id) REFERENCES experience (id)');
        $this->addSql('CREATE INDEX IDX_D88FF90D46E90E27 ON experience_file (experience_id)');
        $this->addSql('ALTER TABLE experience_waver ADD experience_id INT NOT NULL');
        $this->addSql('ALTER TABLE experience_waver ADD CONSTRAINT FK_9BE5BCA246E90E27 FOREIGN KEY (experience_id) REFERENCES experience (id)');
        $this->addSql('CREATE INDEX IDX_9BE5BCA246E90E27 ON experience_waver (experience_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE experience CHANGE brief_description brief_description LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE experience_file DROP FOREIGN KEY FK_D88FF90D46E90E27');
        $this->addSql('DROP INDEX IDX_D88FF90D46E90E27 ON experience_file');
        $this->addSql('ALTER TABLE experience_file DROP experience_id');
        $this->addSql('ALTER TABLE experience_waver DROP FOREIGN KEY FK_9BE5BCA246E90E27');
        $this->addSql('DROP INDEX IDX_9BE5BCA246E90E27 ON experience_waver');
        $this->addSql('ALTER TABLE experience_waver DROP experience_id');
    }
}
