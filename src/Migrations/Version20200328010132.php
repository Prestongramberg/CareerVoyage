<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200328010132 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback DROP FOREIGN KEY FK_143FAF633ACB2C5A');
        $this->addSql('DROP INDEX IDX_143FAF633ACB2C5A ON student_review_meet_professional_experience_feedback');
        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback CHANGE company_experience_id student_to_meet_professional_experience_id INT NOT NULL');
        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback ADD CONSTRAINT FK_143FAF63705E8C9F FOREIGN KEY (student_to_meet_professional_experience_id) REFERENCES student_to_meet_professional_experience (id)');
        $this->addSql('CREATE INDEX IDX_143FAF63705E8C9F ON student_review_meet_professional_experience_feedback (student_to_meet_professional_experience_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback DROP FOREIGN KEY FK_143FAF63705E8C9F');
        $this->addSql('DROP INDEX IDX_143FAF63705E8C9F ON student_review_meet_professional_experience_feedback');
        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback CHANGE student_to_meet_professional_experience_id company_experience_id INT NOT NULL');
        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback ADD CONSTRAINT FK_143FAF633ACB2C5A FOREIGN KEY (company_experience_id) REFERENCES company_experience (id)');
        $this->addSql('CREATE INDEX IDX_143FAF633ACB2C5A ON student_review_meet_professional_experience_feedback (company_experience_id)');
    }
}
