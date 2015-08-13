<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131227154431 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE EGGameResults (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, user_id INT DEFAULT NULL, statsMonth VARCHAR(10) NOT NULL, wins SMALLINT NOT NULL, losses SMALLINT NOT NULL, plays SMALLINT NOT NULL, INDEX IDX_D22E93BDE48FD905 (game_id), INDEX IDX_D22E93BDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE EGGameResults ADD CONSTRAINT FK_D22E93BDE48FD905 FOREIGN KEY (game_id) REFERENCES Games (id)");
        $this->addSql("ALTER TABLE EGGameResults ADD CONSTRAINT FK_D22E93BDA76ED395 FOREIGN KEY (user_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE User ADD totalWins INT NOT NULL, ADD totalLosses INT NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE EGGameResults");
        $this->addSql("ALTER TABLE User DROP totalWins, DROP totalLosses");
    }
}
