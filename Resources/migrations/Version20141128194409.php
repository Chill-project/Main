<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Insert Main Bundle table and indexes
 */
class Version20141128194409 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE Country (id INT NOT NULL, name JSON NOT NULL, countryCode VARCHAR(3) NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE TABLE centers (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE TABLE Language (id VARCHAR(255) NOT NULL, name JSON NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE TABLE group_centers (id INT NOT NULL, center_id INT DEFAULT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE INDEX IDX_A14D8F3D5932F377 ON group_centers (center_id);");
        $this->addSql("CREATE TABLE groupcenter_permissionsgroup (groupcenter_id INT NOT NULL, permissionsgroup_id INT NOT NULL, PRIMARY KEY(groupcenter_id, permissionsgroup_id));");
        $this->addSql("CREATE INDEX IDX_55DFEC607EC2FA68 ON groupcenter_permissionsgroup (groupcenter_id);");
        $this->addSql("CREATE INDEX IDX_55DFEC606FA97D46 ON groupcenter_permissionsgroup (permissionsgroup_id);");
        $this->addSql("CREATE TABLE role_scopes (id INT NOT NULL, scope_id INT DEFAULT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE INDEX IDX_AFF20281682B5931 ON role_scopes (scope_id);");
        $this->addSql("CREATE TABLE permission_groups (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE TABLE permissionsgroup_rolescope (permissionsgroup_id INT NOT NULL, rolescope_id INT NOT NULL, PRIMARY KEY(permissionsgroup_id, rolescope_id));");
        $this->addSql("CREATE INDEX IDX_B22441DC6FA97D46 ON permissionsgroup_rolescope (permissionsgroup_id);");
        $this->addSql("CREATE INDEX IDX_B22441DCA0AE1DB7 ON permissionsgroup_rolescope (rolescope_id);");
        $this->addSql("CREATE TABLE users (id INT NOT NULL, username VARCHAR(80) NOT NULL, password VARCHAR(255) NOT NULL, salt VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, locked BOOLEAN NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE TABLE user_groupcenter (user_id INT NOT NULL, groupcenter_id INT NOT NULL, PRIMARY KEY(user_id, groupcenter_id));");
        $this->addSql("CREATE INDEX IDX_33FFE54AA76ED395 ON user_groupcenter (user_id);");
        $this->addSql("CREATE INDEX IDX_33FFE54A7EC2FA68 ON user_groupcenter (groupcenter_id);");
        $this->addSql("CREATE TABLE scopes (id INT NOT NULL, name JSON NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE SEQUENCE Country_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE centers_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE group_centers_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE role_scopes_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE permission_groups_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE scopes_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("ALTER TABLE group_centers ADD CONSTRAINT FK_A14D8F3D5932F377 FOREIGN KEY (center_id) REFERENCES centers (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE groupcenter_permissionsgroup ADD CONSTRAINT FK_55DFEC607EC2FA68 FOREIGN KEY (groupcenter_id) REFERENCES group_centers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE groupcenter_permissionsgroup ADD CONSTRAINT FK_55DFEC606FA97D46 FOREIGN KEY (permissionsgroup_id) REFERENCES permission_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE role_scopes ADD CONSTRAINT FK_AFF20281682B5931 FOREIGN KEY (scope_id) REFERENCES scopes (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE permissionsgroup_rolescope ADD CONSTRAINT FK_B22441DC6FA97D46 FOREIGN KEY (permissionsgroup_id) REFERENCES permission_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE permissionsgroup_rolescope ADD CONSTRAINT FK_B22441DCA0AE1DB7 FOREIGN KEY (rolescope_id) REFERENCES role_scopes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE user_groupcenter ADD CONSTRAINT FK_33FFE54AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE user_groupcenter ADD CONSTRAINT FK_33FFE54A7EC2FA68 FOREIGN KEY (groupcenter_id) REFERENCES group_centers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
