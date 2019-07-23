<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190723042035 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE experience (id INT AUTO_INCREMENT NOT NULL, employee_contact_id INT NOT NULL, title VARCHAR(255) NOT NULL, brief_description LONGTEXT NOT NULL, about LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, available_spaces INT NOT NULL, payment NUMERIC(10, 2) NOT NULL, payment_shown_is_per VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, start_date_and_time DATETIME NOT NULL, end_date_and_time DATETIME DEFAULT NULL, length INT NOT NULL, UNIQUE INDEX UNIQ_590C1037F76959E (employee_contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experience_career (experience_id INT NOT NULL, career_id INT NOT NULL, INDEX IDX_46EC8BA946E90E27 (experience_id), INDEX IDX_46EC8BA9B58CDA09 (career_id), PRIMARY KEY(experience_id, career_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experience_file (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experience_waver (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C1037F76959E FOREIGN KEY (employee_contact_id) REFERENCES professional_user (id)');
        $this->addSql('ALTER TABLE experience_career ADD CONSTRAINT FK_46EC8BA946E90E27 FOREIGN KEY (experience_id) REFERENCES experience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience_career ADD CONSTRAINT FK_46EC8BA9B58CDA09 FOREIGN KEY (career_id) REFERENCES career (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience_file ADD CONSTRAINT FK_D88FF90DBF396750 FOREIGN KEY (id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience_waver ADD CONSTRAINT FK_9BE5BCA2BF396750 FOREIGN KEY (id) REFERENCES image (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE experience_career DROP FOREIGN KEY FK_46EC8BA946E90E27');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE experience_career');
        $this->addSql('DROP TABLE experience_file');
        $this->addSql('DROP TABLE experience_waver');
    }
}
