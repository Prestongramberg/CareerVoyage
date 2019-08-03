<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801171832 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE educator_user_secondary_industry (educator_user_id INT NOT NULL, secondary_industry_id INT NOT NULL, INDEX IDX_E849B27FFA508C5 (educator_user_id), INDEX IDX_E849B27FD3524FB0 (secondary_industry_id), PRIMARY KEY(educator_user_id, secondary_industry_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE educator_user_secondary_industry ADD CONSTRAINT FK_E849B27FFA508C5 FOREIGN KEY (educator_user_id) REFERENCES educator_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE educator_user_secondary_industry ADD CONSTRAINT FK_E849B27FD3524FB0 FOREIGN KEY (secondary_industry_id) REFERENCES secondary_industry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE educator_user ADD school_id INT NOT NULL, ADD phone VARCHAR(255) DEFAULT NULL, ADD brief_bio LONGTEXT DEFAULT NULL, ADD linkedin_profile VARCHAR(255) DEFAULT NULL, ADD interests LONGTEXT DEFAULT NULL, ADD display_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE educator_user ADD CONSTRAINT FK_CCDBF27EC32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('CREATE INDEX IDX_CCDBF27EC32A47EE ON educator_user (school_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE educator_user_secondary_industry');
        $this->addSql('ALTER TABLE educator_user DROP FOREIGN KEY FK_CCDBF27EC32A47EE');
        $this->addSql('DROP INDEX IDX_CCDBF27EC32A47EE ON educator_user');
        $this->addSql('ALTER TABLE educator_user DROP school_id, DROP phone, DROP brief_bio, DROP linkedin_profile, DROP interests, DROP display_name');
    }
}
