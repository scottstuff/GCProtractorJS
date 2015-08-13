<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131202130540 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE User ADD status VARCHAR(50) NOT NULL");
        
        /**
         * We need to make sure the status gets set correctly for all existing
         * users. So, let's take the following steps:
         * 
         * 1. Set everyone to active.
         * 2. Find all of the users who still have GUIDs for usernames and set
         *    them to not_converted.
         * 3. Find all of the users who are enabled = 0 and set them to 
         *    admin_disabled.
         * 4. Find all of the users who still have confirmation tokens and set
         *    them to unconfirmed.
         */
        $this->addSql("UPDATE User SET status = 'active'");
        $this->addSql("UPDATE User SET status = 'not_converted' WHERE username LIKE '%-%-%-%-%'");
        $this->addSql("UPDATE User SET status = 'admin_disabled' WHERE enabled = 0");
        $this->addSql("UPDATE User SET status = 'unconfirmed' WHERE confirmation_token IS NOT NULL AND confirmation_token != ''");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE User DROP status");
    }
}
