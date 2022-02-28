<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220228143358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checkpoint ADD is_trashed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE enigma ADD is_trashed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE game ADD is_trashed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE instance ADD is_trashed TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checkpoint DROP is_trashed');
        $this->addSql('ALTER TABLE enigma DROP is_trashed');
        $this->addSql('ALTER TABLE game DROP is_trashed');
        $this->addSql('ALTER TABLE instance DROP is_trashed');
    }
}
