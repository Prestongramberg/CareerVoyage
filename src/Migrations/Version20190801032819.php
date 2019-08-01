<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801032819 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE professional_user_roles_willing_to_fulfil (professional_user_id INT NOT NULL, roles_willing_to_fulfil_id INT NOT NULL, INDEX IDX_BC0DBEE9284BF318 (professional_user_id), INDEX IDX_BC0DBEE9EF28394E (roles_willing_to_fulfil_id), PRIMARY KEY(professional_user_id, roles_willing_to_fulfil_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfil ADD CONSTRAINT FK_BC0DBEE9284BF318 FOREIGN KEY (professional_user_id) REFERENCES professional_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user_roles_willing_to_fulfil ADD CONSTRAINT FK_BC0DBEE9EF28394E FOREIGN KEY (roles_willing_to_fulfil_id) REFERENCES roles_willing_to_fulfil (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user DROP roles_willing_to_fulfill');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE professional_user_roles_willing_to_fulfil');
        $this->addSql('ALTER TABLE professional_user ADD roles_willing_to_fulfill LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\'');
    }
}
