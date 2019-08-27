<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190822172636 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE grade ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE lesson ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE course ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE lesson_favorite ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE company_favorite ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE lesson_teachable ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE chat ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE company ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE region ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE school ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE experience ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE image ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE video ADD created_at DATETIME, ADD updated_at DATETIME');
        $this->addSql('ALTER TABLE site ADD created_at DATETIME, ADD updated_at DATETIME');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE chat DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE company DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE company_favorite DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE course DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE experience DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE grade DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE image DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE lesson DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE lesson_favorite DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE lesson_teachable DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE region DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE school DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE site DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE video DROP created_at, DROP updated_at');
    }
}
