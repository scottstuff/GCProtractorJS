<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131106200549 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE EGFeedback (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, user_id INT DEFAULT NULL, feedbackContent VARCHAR(500) NOT NULL, feedbackPros VARCHAR(500) NOT NULL, feedbackCons VARCHAR(500) NOT NULL, developerRating SMALLINT NOT NULL, createdDate DATETIME NOT NULL, ratedDate DATETIME DEFAULT NULL, INDEX IDX_6F97333BE48FD905 (game_id), INDEX IDX_6F97333BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Games (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, scholarship_id INT DEFAULT NULL, guid VARCHAR(40) NOT NULL, studioName VARCHAR(100) NOT NULL, studioProfile VARCHAR(500) NOT NULL, gameName VARCHAR(100) NOT NULL, gameSynopsis VARCHAR(500) NOT NULL, screenshotFile VARCHAR(255) NOT NULL, swfFile VARCHAR(255) NOT NULL, isInChampionship TINYINT(1) NOT NULL, isInQualifier TINYINT(1) NOT NULL, isInContest TINYINT(1) NOT NULL, createdDate DATETIME NOT NULL, lastUpdated DATETIME NOT NULL, INDEX IDX_3EE20435A76ED395 (user_id), INDEX IDX_3EE2043528722836 (scholarship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE EGPlayerStats (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, scholarship_id INT DEFAULT NULL, statsMonth INT NOT NULL, hasWonMonthly TINYINT(1) NOT NULL, feedbackPoints SMALLINT NOT NULL, gameplayPoints SMALLINT NOT NULL, totalPoints SMALLINT NOT NULL, lastUpdated DATETIME NOT NULL, INDEX IDX_6F8F6CF899E6F5DF (player_id), INDEX IDX_6F8F6CF828722836 (scholarship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE GamePlaySession (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, player_id INT DEFAULT NULL, isCompleted TINYINT(1) NOT NULL, phase SMALLINT NOT NULL, startDate DATETIME NOT NULL, endDate DATETIME NOT NULL, score DOUBLE PRECISION NOT NULL, INDEX IDX_A722AD57E48FD905 (game_id), INDEX IDX_A722AD5799E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Votes (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, createdDate DATETIME NOT NULL, ipAddress VARCHAR(15) NOT NULL, INDEX IDX_904A55CBE48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE EGFeedback ADD CONSTRAINT FK_6F97333BE48FD905 FOREIGN KEY (game_id) REFERENCES Games (id)");
        $this->addSql("ALTER TABLE EGFeedback ADD CONSTRAINT FK_6F97333BA76ED395 FOREIGN KEY (user_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE Games ADD CONSTRAINT FK_3EE20435A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE Games ADD CONSTRAINT FK_3EE2043528722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
        $this->addSql("ALTER TABLE EGPlayerStats ADD CONSTRAINT FK_6F8F6CF899E6F5DF FOREIGN KEY (player_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE EGPlayerStats ADD CONSTRAINT FK_6F8F6CF828722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
        $this->addSql("ALTER TABLE GamePlaySession ADD CONSTRAINT FK_A722AD57E48FD905 FOREIGN KEY (game_id) REFERENCES Games (id)");
        $this->addSql("ALTER TABLE GamePlaySession ADD CONSTRAINT FK_A722AD5799E6F5DF FOREIGN KEY (player_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE Votes ADD CONSTRAINT FK_904A55CBE48FD905 FOREIGN KEY (game_id) REFERENCES Games (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE EGFeedback DROP FOREIGN KEY FK_6F97333BE48FD905");
        $this->addSql("ALTER TABLE GamePlaySession DROP FOREIGN KEY FK_A722AD57E48FD905");
        $this->addSql("ALTER TABLE Votes DROP FOREIGN KEY FK_904A55CBE48FD905");
        $this->addSql("DROP TABLE EGFeedback");
        $this->addSql("DROP TABLE Games");
        $this->addSql("DROP TABLE EGPlayerStats");
        $this->addSql("DROP TABLE GamePlaySession");
        $this->addSql("DROP TABLE Votes");
    }
}
