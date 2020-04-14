<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200402031159 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE company ADD company_facebook_page VARCHAR(255) DEFAULT NULL, ADD company_instagram_page VARCHAR(255) DEFAULT NULL, ADD company_twitter_page VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE school ADD school_facebook_page VARCHAR(255) DEFAULT NULL, ADD school_instagram_page VARCHAR(255) DEFAULT NULL, ADD school_twitter_page VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE company DROP company_facebook_page, DROP company_instagram_page, DROP company_twitter_page');
        $this->addSql('ALTER TABLE school DROP school_facebook_page, DROP school_instagram_page, DROP school_twitter_page');
    }
}
