<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140417194804 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE NewsArticle (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, dateCreated DATETIME NOT NULL, lastModified DATETIME NOT NULL, publishDate DATETIME NOT NULL, published TINYINT(1) NOT NULL, INDEX IDX_3E819CDAF675F31B (author_id), INDEX IDX_3E819CDA12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE NewsArticleContent (article_id INT NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(article_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE NewsCategory (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, shortName VARCHAR(60) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE NewsTag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE NewsArticle ADD CONSTRAINT FK_3E819CDAF675F31B FOREIGN KEY (author_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE NewsArticle ADD CONSTRAINT FK_3E819CDA12469DE2 FOREIGN KEY (category_id) REFERENCES NewsCategory (id)");
        $this->addSql("ALTER TABLE NewsArticleContent ADD CONSTRAINT FK_C81D95B67294869C FOREIGN KEY (article_id) REFERENCES NewsArticle (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE NewsArticleContent DROP FOREIGN KEY FK_C81D95B67294869C");
        $this->addSql("ALTER TABLE NewsArticle DROP FOREIGN KEY FK_3E819CDA12469DE2");
        $this->addSql("DROP TABLE NewsArticle");
        $this->addSql("DROP TABLE NewsArticleContent");
        $this->addSql("DROP TABLE NewsCategory");
        $this->addSql("DROP TABLE NewsTag");
    }
}
