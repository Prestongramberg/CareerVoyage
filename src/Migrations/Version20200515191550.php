<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200515191550 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE professional_review_student_to_meet_professional_feedback DROP FOREIGN KEY FK_70C64DC8CB944F1A');
        $this->addSql('DROP INDEX IDX_70C64DC8CB944F1A ON professional_review_student_to_meet_professional_feedback');
        $this->addSql('ALTER TABLE professional_review_student_to_meet_professional_feedback DROP student_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE professional_review_student_to_meet_professional_feedback ADD student_id INT NOT NULL');
        $this->addSql('ALTER TABLE professional_review_student_to_meet_professional_feedback ADD CONSTRAINT FK_70C64DC8CB944F1A FOREIGN KEY (student_id) REFERENCES student_user (id)');
        $this->addSql('CREATE INDEX IDX_70C64DC8CB944F1A ON professional_review_student_to_meet_professional_feedback (student_id)');
    }
}
