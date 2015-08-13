<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131112211015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE EGGameStats (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, statsMonth INT NOT NULL, monthVotes INT NOT NULL, monthPlays INT NOT NULL, lastUpdated DATETIME NOT NULL, INDEX IDX_F4B0DA0CE48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE EGGameStats ADD CONSTRAINT FK_F4B0DA0CE48FD905 FOREIGN KEY (game_id) REFERENCES Games (id)");
        $this->addSql("ALTER TABLE Games ADD totalVotes INT NOT NULL, ADD totalPlays INT NOT NULL, ADD totalRatedFeedbacks INT NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE EGGameStats");
        $this->addSql("ALTER TABLE Games DROP totalVotes, DROP totalPlays, DROP totalRatedFeedbacks");
    }
}
