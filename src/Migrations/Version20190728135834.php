<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190728135834 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lesson_secondary_industry (lesson_id INT NOT NULL, secondary_industry_id INT NOT NULL, INDEX IDX_92E063B7CDF80196 (lesson_id), INDEX IDX_92E063B7D3524FB0 (secondary_industry_id), PRIMARY KEY(lesson_id, secondary_industry_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE professional_user_secondary_industry (professional_user_id INT NOT NULL, secondary_industry_id INT NOT NULL, INDEX IDX_E9B021B9284BF318 (professional_user_id), INDEX IDX_E9B021B9D3524FB0 (secondary_industry_id), PRIMARY KEY(professional_user_id, secondary_industry_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lesson_secondary_industry ADD CONSTRAINT FK_92E063B7CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_secondary_industry ADD CONSTRAINT FK_92E063B7D3524FB0 FOREIGN KEY (secondary_industry_id) REFERENCES secondary_industry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user_secondary_industry ADD CONSTRAINT FK_E9B021B9284BF318 FOREIGN KEY (professional_user_id) REFERENCES professional_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professional_user_secondary_industry ADD CONSTRAINT FK_E9B021B9D3524FB0 FOREIGN KEY (secondary_industry_id) REFERENCES secondary_industry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson ADD primary_industry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F38FF6C7B5 FOREIGN KEY (primary_industry_id) REFERENCES industry (id)');
        $this->addSql('CREATE INDEX IDX_F87474F38FF6C7B5 ON lesson (primary_industry_id)');
        $this->addSql('ALTER TABLE professional_user ADD primary_industry_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE professional_user ADD CONSTRAINT FK_9FD6EF978FF6C7B5 FOREIGN KEY (primary_industry_id) REFERENCES industry (id)');
        $this->addSql('CREATE INDEX IDX_9FD6EF978FF6C7B5 ON professional_user (primary_industry_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE lesson_secondary_industry');
        $this->addSql('DROP TABLE professional_user_secondary_industry');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F38FF6C7B5');
        $this->addSql('DROP INDEX IDX_F87474F38FF6C7B5 ON lesson');
        $this->addSql('ALTER TABLE lesson DROP primary_industry_id');
        $this->addSql('ALTER TABLE professional_user DROP FOREIGN KEY FK_9FD6EF978FF6C7B5');
        $this->addSql('DROP INDEX IDX_9FD6EF978FF6C7B5 ON professional_user');
        $this->addSql('ALTER TABLE professional_user DROP primary_industry_id');
    }
}
