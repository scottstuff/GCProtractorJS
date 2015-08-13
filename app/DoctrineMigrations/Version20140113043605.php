<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140113043605 extends AbstractMigration
{
    public function up(Schema $schema)
    {

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE Video (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, status_id INT NOT NULL, user_id INT NOT NULL, scholarship_id INT NOT NULL, title VARCHAR(200) NOT NULL, youtubeURL VARCHAR(500) NOT NULL, dtAdded DATETIME NOT NULL, INDEX IDX_BD06F52812469DE2 (category_id), INDEX IDX_BD06F5286BF700BD (status_id), INDEX IDX_BD06F528A76ED395 (user_id), INDEX IDX_BD06F52828722836 (scholarship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE VideoCategory (id INT AUTO_INCREMENT NOT NULL, scholarship_id INT NOT NULL, CategoryName VARCHAR(200) NOT NULL, CategoryDescription VARCHAR(500) NOT NULL, INDEX IDX_DDE138B228722836 (scholarship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE VideoStatus (id INT AUTO_INCREMENT NOT NULL, StatusType INT NOT NULL, StatusDescription VARCHAR(200) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE VideoVote (id INT AUTO_INCREMENT NOT NULL, video_id INT NOT NULL, IP4Address VARCHAR(15) NOT NULL, dtAdded DATETIME NOT NULL, INDEX IDX_CF21740729C1004E (video_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Video ADD CONSTRAINT FK_BD06F52812469DE2 FOREIGN KEY (category_id) REFERENCES VideoCategory (id)");
        $this->addSql("ALTER TABLE Video ADD CONSTRAINT FK_BD06F5286BF700BD FOREIGN KEY (status_id) REFERENCES VideoStatus (id)");
        $this->addSql("ALTER TABLE Video ADD CONSTRAINT FK_BD06F528A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE Video ADD CONSTRAINT FK_BD06F52828722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
        $this->addSql("ALTER TABLE VideoCategory ADD CONSTRAINT FK_DDE138B228722836 FOREIGN KEY (scholarship_id) REFERENCES Scholarships (idScholarships)");
        $this->addSql("ALTER TABLE VideoVote ADD CONSTRAINT FK_CF21740729C1004E FOREIGN KEY (video_id) REFERENCES Video (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE VideoVote DROP FOREIGN KEY FK_CF21740729C1004E");
        $this->addSql("ALTER TABLE Video DROP FOREIGN KEY FK_BD06F52812469DE2");
        $this->addSql("ALTER TABLE Video DROP FOREIGN KEY FK_BD06F5286BF700BD");
        $this->addSql("DROP TABLE Video");
        $this->addSql("DROP TABLE VideoCategory");
        $this->addSql("DROP TABLE VideoStatus");
        $this->addSql("DROP TABLE VideoVote");
    }
}
