<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131213123744 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE GameGenres (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Games ADD genre_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Games ADD CONSTRAINT FK_3EE204354296D31F FOREIGN KEY (genre_id) REFERENCES GameGenres (id)");
        $this->addSql("CREATE INDEX IDX_3EE204354296D31F ON Games (genre_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Games DROP FOREIGN KEY FK_3EE204354296D31F");
        $this->addSql("DROP TABLE GameGenres");
        $this->addSql("DROP INDEX IDX_3EE204354296D31F ON Games");
        $this->addSql("ALTER TABLE Games DROP genre_id");
    }
}
