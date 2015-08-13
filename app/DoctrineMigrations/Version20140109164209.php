<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140109164209 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
          UPDATE ProfileProperty
          SET fieldOptions = '{\"allowed_types\":[\"image/png\",\"image/jpeg\"]}'
          WHERE name = 'PhotoURL'
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("
          UPDATE ProfileProperty
          SET fieldOptions = ''
          WHERE name = 'PhotoURL'
        ");
    }
}
