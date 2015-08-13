<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140120223001 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE VideoCategory DROP FOREIGN KEY FK_DDE138B228722836");
        $this->addSql("ALTER TABLE VideoCategory CHANGE scholarship_id scholarship_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE VideoCategory ADD CONSTRAINT FK_DDE138B228722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Video CHANGE category_id category_id INT NOT NULL, CHANGE status_id status_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL");
        $this->addSql("ALTER TABLE VideoCategory DROP FOREIGN KEY FK_DDE138B228722836");
        $this->addSql("ALTER TABLE VideoCategory CHANGE scholarship_id scholarship_id INT NOT NULL");
        $this->addSql("ALTER TABLE VideoCategory ADD CONSTRAINT FK_DDE138B228722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
        $this->addSql("ALTER TABLE VideoVote CHANGE video_id video_id INT NOT NULL");
    }
}
