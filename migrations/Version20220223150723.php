<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220223150723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ADD enigma_id INT NOT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25457B6BA0 FOREIGN KEY (enigma_id) REFERENCES enigma (id)');
        $this->addSql('CREATE INDEX IDX_DADD4A25457B6BA0 ON answer (enigma_id)');
        $this->addSql('ALTER TABLE checkpoint ADD game_id INT NOT NULL');
        $this->addSql('ALTER TABLE checkpoint ADD CONSTRAINT FK_F00F7BEE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_F00F7BEE48FD905 ON checkpoint (game_id)');
        $this->addSql('ALTER TABLE enigma ADD checkpoint_id INT NOT NULL');
        $this->addSql('ALTER TABLE enigma ADD CONSTRAINT FK_2EA9D76EF27C615F FOREIGN KEY (checkpoint_id) REFERENCES checkpoint (id)');
        $this->addSql('CREATE INDEX IDX_2EA9D76EF27C615F ON enigma (checkpoint_id)');
        $this->addSql('ALTER TABLE game ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_232B318CA76ED395 ON game (user_id)');
        $this->addSql('ALTER TABLE instance ADD game_id INT NOT NULL');
        $this->addSql('ALTER TABLE instance ADD CONSTRAINT FK_4230B1DEE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_4230B1DEE48FD905 ON instance (game_id)');
        $this->addSql('ALTER TABLE round ADD instance_id INT NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA343A51721D FOREIGN KEY (instance_id) REFERENCES instance (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA34A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_C5EEEA343A51721D ON round (instance_id)');
        $this->addSql('CREATE INDEX IDX_C5EEEA34A76ED395 ON round (user_id)');
        $this->addSql('ALTER TABLE scan_qr ADD checkpoint_id INT NOT NULL, ADD round_id INT NOT NULL');
        $this->addSql('ALTER TABLE scan_qr ADD CONSTRAINT FK_5EE887DBF27C615F FOREIGN KEY (checkpoint_id) REFERENCES checkpoint (id)');
        $this->addSql('ALTER TABLE scan_qr ADD CONSTRAINT FK_5EE887DBA6005CA0 FOREIGN KEY (round_id) REFERENCES round (id)');
        $this->addSql('CREATE INDEX IDX_5EE887DBF27C615F ON scan_qr (checkpoint_id)');
        $this->addSql('CREATE INDEX IDX_5EE887DBA6005CA0 ON scan_qr (round_id)');
        $this->addSql('ALTER TABLE user ADD role_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D60322AC ON user (role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25457B6BA0');
        $this->addSql('DROP INDEX IDX_DADD4A25457B6BA0 ON answer');
        $this->addSql('ALTER TABLE answer DROP enigma_id');
        $this->addSql('ALTER TABLE checkpoint DROP FOREIGN KEY FK_F00F7BEE48FD905');
        $this->addSql('DROP INDEX IDX_F00F7BEE48FD905 ON checkpoint');
        $this->addSql('ALTER TABLE checkpoint DROP game_id');
        $this->addSql('ALTER TABLE enigma DROP FOREIGN KEY FK_2EA9D76EF27C615F');
        $this->addSql('DROP INDEX IDX_2EA9D76EF27C615F ON enigma');
        $this->addSql('ALTER TABLE enigma DROP checkpoint_id');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CA76ED395');
        $this->addSql('DROP INDEX IDX_232B318CA76ED395 ON game');
        $this->addSql('ALTER TABLE game DROP user_id');
        $this->addSql('ALTER TABLE instance DROP FOREIGN KEY FK_4230B1DEE48FD905');
        $this->addSql('DROP INDEX IDX_4230B1DEE48FD905 ON instance');
        $this->addSql('ALTER TABLE instance DROP game_id');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA343A51721D');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA34A76ED395');
        $this->addSql('DROP INDEX IDX_C5EEEA343A51721D ON round');
        $this->addSql('DROP INDEX IDX_C5EEEA34A76ED395 ON round');
        $this->addSql('ALTER TABLE round DROP instance_id, DROP user_id');
        $this->addSql('ALTER TABLE scan_qr DROP FOREIGN KEY FK_5EE887DBF27C615F');
        $this->addSql('ALTER TABLE scan_qr DROP FOREIGN KEY FK_5EE887DBA6005CA0');
        $this->addSql('DROP INDEX IDX_5EE887DBF27C615F ON scan_qr');
        $this->addSql('DROP INDEX IDX_5EE887DBA6005CA0 ON scan_qr');
        $this->addSql('ALTER TABLE scan_qr DROP checkpoint_id, DROP round_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('DROP INDEX IDX_8D93D649D60322AC ON user');
        $this->addSql('ALTER TABLE user DROP role_id');
    }
}
