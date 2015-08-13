<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130923175541 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE ActivityLog (idActivityLog INT AUTO_INCREMENT NOT NULL, ActivityDateTime DATETIME NOT NULL, ActivityType VARCHAR(200) NOT NULL, ClientIP4Address VARCHAR(15) NOT NULL, UserID INT DEFAULT NULL, INDEX IDX_D66F045458746832 (UserID), PRIMARY KEY(idActivityLog)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(200) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE EntrySponsors (createdDate DATETIME NOT NULL, entryId INT NOT NULL, userId INT NOT NULL, INDEX IDX_889732468B3CFEDC (entryId), INDEX IDX_8897324664B64DCC (userId), PRIMARY KEY(entryId, userId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE NotificationCommType (idNotificationCommType INT AUTO_INCREMENT NOT NULL, NotificationCommTypeName VARCHAR(100) NOT NULL, PRIMARY KEY(idNotificationCommType)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE NotificationSubscriptions (idNotificationType INT NOT NULL, idUser INT NOT NULL, INDEX IDX_5C7207B463D38BC3 (idNotificationType), INDEX IDX_5C7207B4FE6E88D7 (idUser), PRIMARY KEY(idNotificationType, idUser)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE NotificationTypes (idNotificationTypes INT AUTO_INCREMENT NOT NULL, NotificationTypesName VARCHAR(100) NOT NULL, NotificationCommType INT DEFAULT NULL, INDEX IDX_534BAFEF1C0495D9 (NotificationCommType), PRIMARY KEY(idNotificationTypes)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ProfileProperties_In_Networks (createdDate DATETIME NOT NULL, propertyId INT NOT NULL, networkId INT NOT NULL, INDEX IDX_7C52397F21077CDE (propertyId), INDEX IDX_7C52397F1111D441 (networkId), PRIMARY KEY(propertyId, networkId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ProfileProperty (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, isRequired TINYINT(1) NOT NULL, defaultVisibility SMALLINT NOT NULL, fieldType VARCHAR(20) NOT NULL, fieldOptions LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ProfilePropertyGroups (idProfilePropertyGroups INT AUTO_INCREMENT NOT NULL, GroupName VARCHAR(200) NOT NULL, visibility SMALLINT NOT NULL, PRIMARY KEY(idProfilePropertyGroups)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ProfilePropertyGroupMap (idGroup INT NOT NULL, idProfileProperty INT NOT NULL, INDEX IDX_E241075C7A0407D8 (idGroup), INDEX IDX_E241075CBCA89F7E (idProfileProperty), PRIMARY KEY(idGroup, idProfileProperty)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Roles (id INT AUTO_INCREMENT NOT NULL, roleName VARCHAR(100) NOT NULL, roleDescription LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Scholarships (idScholarships INT AUTO_INCREMENT NOT NULL, ScholarshipName VARCHAR(200) NOT NULL, StartDate DATETIME NOT NULL, EndDate DATETIME NOT NULL, ScholarshipType SMALLINT NOT NULL, PRIMARY KEY(idScholarships)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Entries (id INT AUTO_INCREMENT NOT NULL, CreatedDate DATETIME NOT NULL, extraData VARCHAR(255) NOT NULL, idScholarship INT DEFAULT NULL, idUser INT DEFAULT NULL, INDEX IDX_E2458A594F622947 (idScholarship), INDEX IDX_E2458A59FE6E88D7 (idUser), UNIQUE INDEX UniqueIndexStoU (idScholarship, idUser), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(16) NOT NULL, translationKey VARCHAR(255) NOT NULL, translationText LONGTEXT NOT NULL, domain VARCHAR(32) NOT NULL, INDEX domain_idx (domain), UNIQUE INDEX locale_key (locale, translationKey), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE User (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, createdDate DATETIME NOT NULL, lastModified DATETIME NOT NULL, UNIQUE INDEX UNIQ_2DA1797792FC23A8 (username_canonical), UNIQUE INDEX UNIQ_2DA17977A0D96FBF (email_canonical), INDEX Username (username), INDEX Username_Password (username, password, salt), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE UsergMeshNetworks (networkId INT AUTO_INCREMENT NOT NULL, networkName VARCHAR(100) NOT NULL, createdDate DATETIME NOT NULL, isDeleted TINYINT(1) NOT NULL, ownerUserId INT DEFAULT NULL, INDEX IDX_B8A72E8219BB9141 (ownerUserId), PRIMARY KEY(networkId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE UserProfile (id INT AUTO_INCREMENT NOT NULL, property_id INT DEFAULT NULL, user_id INT DEFAULT NULL, propertyValue VARCHAR(1000) NOT NULL, isSearchableByDefault TINYINT(1) NOT NULL, lastModified DATETIME NOT NULL, visibility SMALLINT NOT NULL, INDEX IDX_5417E0FA549213EC (property_id), INDEX IDX_5417E0FAA76ED395 (user_id), UNIQUE INDEX UniqueIndexUToP (user_id, property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Users_In_Networks (createdDate DATETIME NOT NULL, userId INT NOT NULL, networkId INT NOT NULL, INDEX IDX_38BC2BFE64B64DCC (userId), INDEX IDX_38BC2BFE1111D441 (networkId), UNIQUE INDEX UniqueIndexNToU (networkId, userId), PRIMARY KEY(userId, networkId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE ActivityLog ADD CONSTRAINT FK_D66F045458746832 FOREIGN KEY (UserID) REFERENCES User (id)");
        $this->addSql("ALTER TABLE EntrySponsors ADD CONSTRAINT FK_889732468B3CFEDC FOREIGN KEY (entryId) REFERENCES Entries (id)");
        $this->addSql("ALTER TABLE EntrySponsors ADD CONSTRAINT FK_8897324664B64DCC FOREIGN KEY (userId) REFERENCES User (id)");
        $this->addSql("ALTER TABLE NotificationSubscriptions ADD CONSTRAINT FK_5C7207B463D38BC3 FOREIGN KEY (idNotificationType) REFERENCES NotificationTypes (idNotificationTypes)");
        $this->addSql("ALTER TABLE NotificationSubscriptions ADD CONSTRAINT FK_5C7207B4FE6E88D7 FOREIGN KEY (idUser) REFERENCES User (id)");
        $this->addSql("ALTER TABLE NotificationTypes ADD CONSTRAINT FK_534BAFEF1C0495D9 FOREIGN KEY (NotificationCommType) REFERENCES NotificationCommType (idNotificationCommType)");
        $this->addSql("ALTER TABLE ProfileProperties_In_Networks ADD CONSTRAINT FK_7C52397F21077CDE FOREIGN KEY (propertyId) REFERENCES UserProfile (id)");
        $this->addSql("ALTER TABLE ProfileProperties_In_Networks ADD CONSTRAINT FK_7C52397F1111D441 FOREIGN KEY (networkId) REFERENCES UsergMeshNetworks (networkId)");
        $this->addSql("ALTER TABLE ProfilePropertyGroupMap ADD CONSTRAINT FK_E241075C7A0407D8 FOREIGN KEY (idGroup) REFERENCES ProfilePropertyGroups (idProfilePropertyGroups)");
        $this->addSql("ALTER TABLE ProfilePropertyGroupMap ADD CONSTRAINT FK_E241075CBCA89F7E FOREIGN KEY (idProfileProperty) REFERENCES ProfileProperty (id)");
        $this->addSql("ALTER TABLE Entries ADD CONSTRAINT FK_E2458A594F622947 FOREIGN KEY (idScholarship) REFERENCES Scholarships (idScholarships)");
        $this->addSql("ALTER TABLE Entries ADD CONSTRAINT FK_E2458A59FE6E88D7 FOREIGN KEY (idUser) REFERENCES User (id)");
        $this->addSql("ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES User (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES Roles (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE UsergMeshNetworks ADD CONSTRAINT FK_B8A72E8219BB9141 FOREIGN KEY (ownerUserId) REFERENCES User (id)");
        $this->addSql("ALTER TABLE UserProfile ADD CONSTRAINT FK_5417E0FA549213EC FOREIGN KEY (property_id) REFERENCES ProfileProperty (id)");
        $this->addSql("ALTER TABLE UserProfile ADD CONSTRAINT FK_5417E0FAA76ED395 FOREIGN KEY (user_id) REFERENCES User (id)");
        $this->addSql("ALTER TABLE Users_In_Networks ADD CONSTRAINT FK_38BC2BFE64B64DCC FOREIGN KEY (userId) REFERENCES User (id)");
        $this->addSql("ALTER TABLE Users_In_Networks ADD CONSTRAINT FK_38BC2BFE1111D441 FOREIGN KEY (networkId) REFERENCES UsergMeshNetworks (networkId)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE NotificationTypes DROP FOREIGN KEY FK_534BAFEF1C0495D9");
        $this->addSql("ALTER TABLE NotificationSubscriptions DROP FOREIGN KEY FK_5C7207B463D38BC3");
        $this->addSql("ALTER TABLE ProfilePropertyGroupMap DROP FOREIGN KEY FK_E241075CBCA89F7E");
        $this->addSql("ALTER TABLE UserProfile DROP FOREIGN KEY FK_5417E0FA549213EC");
        $this->addSql("ALTER TABLE ProfilePropertyGroupMap DROP FOREIGN KEY FK_E241075C7A0407D8");
        $this->addSql("ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC");
        $this->addSql("ALTER TABLE Entries DROP FOREIGN KEY FK_E2458A594F622947");
        $this->addSql("ALTER TABLE EntrySponsors DROP FOREIGN KEY FK_889732468B3CFEDC");
        $this->addSql("ALTER TABLE ActivityLog DROP FOREIGN KEY FK_D66F045458746832");
        $this->addSql("ALTER TABLE EntrySponsors DROP FOREIGN KEY FK_8897324664B64DCC");
        $this->addSql("ALTER TABLE NotificationSubscriptions DROP FOREIGN KEY FK_5C7207B4FE6E88D7");
        $this->addSql("ALTER TABLE Entries DROP FOREIGN KEY FK_E2458A59FE6E88D7");
        $this->addSql("ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395");
        $this->addSql("ALTER TABLE UsergMeshNetworks DROP FOREIGN KEY FK_B8A72E8219BB9141");
        $this->addSql("ALTER TABLE UserProfile DROP FOREIGN KEY FK_5417E0FAA76ED395");
        $this->addSql("ALTER TABLE Users_In_Networks DROP FOREIGN KEY FK_38BC2BFE64B64DCC");
        $this->addSql("ALTER TABLE ProfileProperties_In_Networks DROP FOREIGN KEY FK_7C52397F1111D441");
        $this->addSql("ALTER TABLE Users_In_Networks DROP FOREIGN KEY FK_38BC2BFE1111D441");
        $this->addSql("ALTER TABLE ProfileProperties_In_Networks DROP FOREIGN KEY FK_7C52397F21077CDE");
        $this->addSql("DROP TABLE ActivityLog");
        $this->addSql("DROP TABLE Company");
        $this->addSql("DROP TABLE EntrySponsors");
        $this->addSql("DROP TABLE NotificationCommType");
        $this->addSql("DROP TABLE NotificationSubscriptions");
        $this->addSql("DROP TABLE NotificationTypes");
        $this->addSql("DROP TABLE ProfileProperties_In_Networks");
        $this->addSql("DROP TABLE ProfileProperty");
        $this->addSql("DROP TABLE ProfilePropertyGroups");
        $this->addSql("DROP TABLE ProfilePropertyGroupMap");
        $this->addSql("DROP TABLE Roles");
        $this->addSql("DROP TABLE Scholarships");
        $this->addSql("DROP TABLE Entries");
        $this->addSql("DROP TABLE translations");
        $this->addSql("DROP TABLE User");
        $this->addSql("DROP TABLE user_role");
        $this->addSql("DROP TABLE UsergMeshNetworks");
        $this->addSql("DROP TABLE UserProfile");
        $this->addSql("DROP TABLE Users_In_Networks");
    }
}
