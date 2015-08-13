<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140303202117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("UPDATE translations SET translationText = 'I am' WHERE translationKey = 'profile_properties.IAm' AND locale = 'en'");
        $this->addSql("UPDATE translations SET translationText = 'Phone Number' WHERE translationKey = 'profile_properties.Telephone' AND locale = 'en'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("UPDATE translations SET translationText = 'I am a(n)' WHERE translationKey = 'profile_properties.IAm' AND locale = 'en'");
        $this->addSql("UPDATE translations SET translationText = 'Phone #' WHERE translationKey = 'profile_properties.Telephone' AND locale = 'en'");
    }
}
