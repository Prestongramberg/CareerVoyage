<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801033457 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfil DROP FOREIGN KEY FK_BC0DBEE9EF28394E');
        $this->addSql('CREATE TABLE professional_user_roles_willing_to_fulfill (professional_user_id INT NOT NULL, roles_willing_to_fulfill_id INT NOT NULL, INDEX IDX_4F6C959C284BF318 (professional_user_id), INDEX IDX_4F6C959C7E80BE42 (roles_willing_to_fulfill_id), PRIMARY KEY(professional_user_id, roles_willing_to_fulfill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles_willing_to_fulfill (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfill ADD CONSTRAINT FK_4F6C959C284BF318 FOREIGN KEY (professional_user_id) REFERENCES professional_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfill ADD CONSTRAINT FK_4F6C959C7E80BE42 FOREIGN KEY (roles_willing_to_fulfill_id) REFERENCES roles_willing_to_fulfill (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE professional_user_roles_willing_to_fulfil');
        $this->addSql('DROP TABLE roles_willing_to_fulfil');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfill DROP FOREIGN KEY FK_4F6C959C7E80BE42');
        $this->addSql('CREATE TABLE professional_user_roles_willing_to_fulfil (professional_user_id INT NOT NULL, roles_willing_to_fulfil_id INT NOT NULL, INDEX IDX_BC0DBEE9284BF318 (professional_user_id), INDEX IDX_BC0DBEE9EF28394E (roles_willing_to_fulfil_id), PRIMARY KEY(professional_user_id, roles_willing_to_fulfil_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE roles_willing_to_fulfil (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfil ADD CONSTRAINT FK_BC0DBEE9284BF318 FOREIGN KEY (professional_user_id) REFERENCES professional_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfil ADD CONSTRAINT FK_BC0DBEE9EF28394E FOREIGN KEY (roles_willing_to_fulfil_id) REFERENCES roles_willing_to_fulfil (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE professional_user_roles_willing_to_fulfill');
        $this->addSql('DROP TABLE roles_willing_to_fulfill');
    }
}
