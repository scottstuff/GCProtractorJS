<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131212143725 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE game_scholarships (game_id INT NOT NULL, scholarship_id INT NOT NULL, scholarshipType VARCHAR(50) NOT NULL, INDEX IDX_43B7E98EE48FD905 (game_id), INDEX IDX_43B7E98E28722836 (scholarship_id), UNIQUE INDEX UniqueIndexGoS (game_id, scholarship_id), PRIMARY KEY(game_id, scholarship_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE game_scholarships ADD CONSTRAINT FK_43B7E98EE48FD905 FOREIGN KEY (game_id) REFERENCES Games (id)");
        $this->addSql("ALTER TABLE game_scholarships ADD CONSTRAINT FK_43B7E98E28722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
        $this->addSql("ALTER TABLE Games DROP FOREIGN KEY FK_3EE2043528722836");
        $this->addSql("DROP INDEX IDX_3EE2043528722836 ON Games");
        $this->addSql("ALTER TABLE Games ADD status VARCHAR(50) NOT NULL, DROP scholarship_id, DROP isInChampionship, DROP isInQualifier, DROP isInContest");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE game_scholarships");
        $this->addSql("ALTER TABLE Games ADD scholarship_id INT DEFAULT NULL, ADD isInChampionship TINYINT(1) NOT NULL, ADD isInQualifier TINYINT(1) NOT NULL, ADD isInContest TINYINT(1) NOT NULL, DROP status");
        $this->addSql("ALTER TABLE Games ADD CONSTRAINT FK_3EE2043528722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
        $this->addSql("CREATE INDEX IDX_3EE2043528722836 ON Games (scholarship_id)");
    }
}
