<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190728155759 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE school (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_school (company_id INT NOT NULL, school_id INT NOT NULL, INDEX IDX_C401BECE979B1AD6 (company_id), INDEX IDX_C401BECEC32A47EE (school_id), PRIMARY KEY(company_id, school_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE professional_user_school (professional_user_id INT NOT NULL, school_id INT NOT NULL, INDEX IDX_2E46A7D6284BF318 (professional_user_id), INDEX IDX_2E46A7D6C32A47EE (school_id), PRIMARY KEY(professional_user_id, school_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_school ADD CONSTRAINT FK_C401BECE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_school ADD CONSTRAINT FK_C401BECEC32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user_school ADD CONSTRAINT FK_2E46A7D6284BF318 FOREIGN KEY (professional_user_id) REFERENCES professional_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user_school ADD CONSTRAINT FK_2E46A7D6C32A47EE FOREIGN KEY (school_id) REFERENCES school (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE company_school DROP FOREIGN KEY FK_C401BECEC32A47EE');
        $this->addSql('ALTER TABLE professional_user_school DROP FOREIGN KEY FK_2E46A7D6C32A47EE');
        $this->addSql('DROP TABLE school');
        $this->addSql('DROP TABLE company_school');
        $this->addSql('DROP TABLE professional_user_school');
    }
}
