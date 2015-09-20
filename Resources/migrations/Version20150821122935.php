<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * drop table groupcenter_permissionsgroup, not necessary after 
 * 20150821105642
 */
class Version20150821122935 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        
        $this->addSql('DROP TABLE groupcenter_permissionsgroup');
        $this->addSql('ALTER TABLE group_centers ALTER permissionsGroup_id SET NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE group_centers ALTER permissionsGroup_id SET DEFAULT NULL');
        $this->addSql('CREATE TABLE groupcenter_permissionsgroup (groupcenter_id INT NOT NULL, permissionsgroup_id INT NOT NULL, PRIMARY KEY(groupcenter_id, permissionsgroup_id))');
        $this->addSql('CREATE INDEX idx_55dfec607ec2fa68 ON groupcenter_permissionsgroup (groupcenter_id)');
        $this->addSql('CREATE INDEX idx_55dfec606fa97d46 ON groupcenter_permissionsgroup (permissionsgroup_id)');
       
        $this->addSql('ALTER TABLE groupcenter_permissionsgroup ADD CONSTRAINT fk_55dfec607ec2fa68 FOREIGN KEY (groupcenter_id) REFERENCES group_centers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE groupcenter_permissionsgroup ADD CONSTRAINT fk_55dfec606fa97d46 FOREIGN KEY (permissionsgroup_id) REFERENCES permission_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

    }
}
