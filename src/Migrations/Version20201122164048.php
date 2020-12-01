<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201122164048 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE educator_register_educator_for_company_experience_request (id INT AUTO_INCREMENT NOT NULL, company_experience_id INT DEFAULT NULL, educator_user_id INT DEFAULT NULL, INDEX IDX_F69AD5D83ACB2C5A (company_experience_id), INDEX IDX_F69AD5D8FA508C5 (educator_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE educator_register_educator_for_company_experience_request ADD CONSTRAINT FK_F69AD5D83ACB2C5A FOREIGN KEY (company_experience_id) REFERENCES company_experience (id)');
        $this->addSql('ALTER TABLE educator_register_educator_for_company_experience_request ADD CONSTRAINT FK_F69AD5D8FA508C5 FOREIGN KEY (educator_user_id) REFERENCES educator_user (id)');
        $this->addSql('DROP TABLE become_state_coordinator_request');
        $this->addSql('DROP TABLE chat_user');
        $this->addSql('DROP TABLE company_experience_student_express_interest_request');
        $this->addSql('DROP TABLE message_read_status');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE regional_coordinator_request');
        $this->addSql('DROP TABLE school_administrator_request');
        $this->addSql('DROP TABLE single_chat');
        $this->addSql('DROP TABLE site_admin_request');
        $this->addSql('DROP TABLE state_coordinator_request');
        $this->addSql('DROP TABLE student_company_experience_request_registrations');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE become_state_coordinator_request (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE chat_user (chat_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_2B0F4B08A76ED395 (user_id), INDEX IDX_2B0F4B081A9A7125 (chat_id), PRIMARY KEY(chat_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE company_experience_student_express_interest_request (id INT NOT NULL, student_user_id INT DEFAULT NULL, company_experience_id INT DEFAULT NULL, INDEX IDX_96A6241D4A58666D (student_user_id), INDEX IDX_96A6241D3ACB2C5A (company_experience_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE message_read_status (id INT AUTO_INCREMENT NOT NULL, chat_message_id INT NOT NULL, user_id INT NOT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_9A9EAD54948B568F (chat_message_id), INDEX IDX_9A9EAD54A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, headers LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, queue_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E016BA31DB (delivered_at), INDEX IDX_75EA56E0E3BD61CE (available_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE regional_coordinator_request (id INT NOT NULL, region_id INT DEFAULT NULL, INDEX IDX_B92EAF8898260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE school_administrator_request (id INT NOT NULL, school_id INT DEFAULT NULL, INDEX IDX_4EB1BB0C32A47EE (school_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE single_chat (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE site_admin_request (id INT NOT NULL, site_id INT NOT NULL, INDEX IDX_AD0865BDF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE state_coordinator_request (id INT NOT NULL, state_id INT NOT NULL, INDEX IDX_2E7AAC255D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE student_company_experience_request_registrations (request_id INT NOT NULL, student_user_id INT NOT NULL, INDEX IDX_61534DF427EB8A5 (request_id), INDEX IDX_61534DF4A58666D (student_user_id), PRIMARY KEY(request_id, student_user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE chat_user ADD CONSTRAINT FK_2B0F4B081A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_user ADD CONSTRAINT FK_2B0F4B08A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_experience_student_express_interest_request ADD CONSTRAINT FK_96A6241D3ACB2C5A FOREIGN KEY (company_experience_id) REFERENCES company_experience (id)');
        $this->addSql('ALTER TABLE company_experience_student_express_interest_request ADD CONSTRAINT FK_96A6241D4A58666D FOREIGN KEY (student_user_id) REFERENCES student_user (id)');
        $this->addSql('ALTER TABLE company_experience_student_express_interest_request ADD CONSTRAINT FK_96A6241DBF396750 FOREIGN KEY (id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message_read_status ADD CONSTRAINT FK_9A9EAD54948B568F FOREIGN KEY (chat_message_id) REFERENCES chat_message (id)');
        $this->addSql('ALTER TABLE message_read_status ADD CONSTRAINT FK_9A9EAD54A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE regional_coordinator_request ADD CONSTRAINT FK_B92EAF8898260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE regional_coordinator_request ADD CONSTRAINT FK_B92EAF88BF396750 FOREIGN KEY (id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_administrator_request ADD CONSTRAINT FK_4EB1BB0BF396750 FOREIGN KEY (id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_administrator_request ADD CONSTRAINT FK_4EB1BB0C32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE single_chat ADD CONSTRAINT FK_858F94E4BF396750 FOREIGN KEY (id) REFERENCES chat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site_admin_request ADD CONSTRAINT FK_AD0865BDBF396750 FOREIGN KEY (id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site_admin_request ADD CONSTRAINT FK_AD0865BDF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE state_coordinator_request ADD CONSTRAINT FK_2E7AAC255D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE state_coordinator_request ADD CONSTRAINT FK_2E7AAC25BF396750 FOREIGN KEY (id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_company_experience_request_registrations ADD CONSTRAINT FK_61534DF427EB8A5 FOREIGN KEY (request_id) REFERENCES educator_register_student_for_company_experience_request (id)');
        $this->addSql('ALTER TABLE student_company_experience_request_registrations ADD CONSTRAINT FK_61534DF4A58666D FOREIGN KEY (student_user_id) REFERENCES student_user (id)');
        $this->addSql('DROP TABLE educator_register_educator_for_company_experience_request');
    }
}
