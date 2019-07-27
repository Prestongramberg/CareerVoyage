<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190727211016 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lesson_resource DROP FOREIGN KEY FK_F4D6BE0FCDF80196');
        $this->addSql('ALTER TABLE lesson_resource CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE lesson_resource ADD CONSTRAINT FK_F4D6BE0FBF396750 FOREIGN KEY (id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_resource ADD CONSTRAINT FK_F4D6BE0FCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lesson_resource DROP FOREIGN KEY FK_F4D6BE0FBF396750');
        $this->addSql('ALTER TABLE lesson_resource DROP FOREIGN KEY FK_F4D6BE0FCDF80196');
        $this->addSql('ALTER TABLE lesson_resource CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE lesson_resource ADD CONSTRAINT FK_F4D6BE0FCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
    }
}
