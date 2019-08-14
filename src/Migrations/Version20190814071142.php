<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190814071142 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE experience CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL, CHANGE zipcode zipcode VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(25) DEFAULT NULL');
        $this->addSql('ALTER TABLE teach_lesson_experience CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE teach_lesson_experience ADD CONSTRAINT FK_C0AD7051BF396750 FOREIGN KEY (id) REFERENCES experience (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE experience CHANGE street street VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE city city VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE zipcode zipcode VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE email email VARCHAR(255) DEFAULT \'\' NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE teach_lesson_experience DROP FOREIGN KEY FK_C0AD7051BF396750');
        $this->addSql('ALTER TABLE teach_lesson_experience CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
