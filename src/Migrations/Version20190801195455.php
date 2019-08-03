<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190801195455 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE school_admin_user CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE school_admin_user ADD CONSTRAINT FK_182BD45BBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_multi_site_admin CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE school_multi_site_admin ADD CONSTRAINT FK_1B9368DFBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_regional_user CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE school_regional_user ADD CONSTRAINT FK_1FFAB1A9BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_state_user CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE school_state_user ADD CONSTRAINT FK_ACD1DF7DBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE school_admin_user DROP FOREIGN KEY FK_182BD45BBF396750');
        $this->addSql('ALTER TABLE school_admin_user CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE school_multi_site_admin DROP FOREIGN KEY FK_1B9368DFBF396750');
        $this->addSql('ALTER TABLE school_multi_site_admin CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE school_regional_user DROP FOREIGN KEY FK_1FFAB1A9BF396750');
        $this->addSql('ALTER TABLE school_regional_user CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE school_state_user DROP FOREIGN KEY FK_ACD1DF7DBF396750');
        $this->addSql('ALTER TABLE school_state_user CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
