<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200331015646 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE student_review_meet_professional_experience_feedback');
        $this->addSql('ALTER TABLE student_user ADD career_statement VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE student_review_meet_professional_experience_feedback (id INT NOT NULL, student_id INT NOT NULL, student_to_meet_professional_experience_id INT NOT NULL, interest_in_working_for_company INT NOT NULL, INDEX IDX_143FAF63CB944F1A (student_id), INDEX IDX_143FAF63705E8C9F (student_to_meet_professional_experience_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback ADD CONSTRAINT FK_143FAF63705E8C9F FOREIGN KEY (student_to_meet_professional_experience_id) REFERENCES student_to_meet_professional_experience (id)');
        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback ADD CONSTRAINT FK_143FAF63BF396750 FOREIGN KEY (id) REFERENCES feedback (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_review_meet_professional_experience_feedback ADD CONSTRAINT FK_143FAF63CB944F1A FOREIGN KEY (student_id) REFERENCES student_user (id)');
        $this->addSql('ALTER TABLE student_user DROP career_statement');
    }
}
