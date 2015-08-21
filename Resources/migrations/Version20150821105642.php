<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Migrate association from 
 *     ManyToMany between PermissionGroup <-> GroupCenter
 * to
 *     ManyToOne : a GroupCenter can have only one PermissionGroup
 * 
 * @link https://redmine.champs-libres.coop/issues/578 The issue describing the move
 */
class Version20150821105642 extends AbstractMigration implements 
    \Symfony\Component\DependencyInjection\ContainerAwareInterface
{
    /**
     *
     * @var ContainerInterface
     */
    private $container;

    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE group_centers ADD permissionsGroup_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE group_centers ADD CONSTRAINT FK_A14D8F3D447BBB3B FOREIGN KEY (permissionsGroup_id) REFERENCES permission_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A14D8F3D447BBB3B ON group_centers (permissionsGroup_id)');

    }
    
    public function postUp(Schema $schema)
    {
        //transform data from groupcenter_permissionsgroup table
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        //get all existing associations
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('groupcenter_id', 'groupcenter_id');
        $rsm->addScalarResult('permissionsgroup_id', 'permissionsgroup_id');
        
        $groupPermissionsAssociations = $em->createNativeQuery(
                "SELECT groupcenter_id, permissionsgroup_id "
                . "FROM groupcenter_permissionsgroup",
                $rsm
                )
                ->getScalarResult();

        //update 
        foreach ($groupPermissionsAssociations as $groupPermissionAssociation) {
            //get the corresponding groupCenter
            $rsmGroupCenter = new ResultSetMapping();
            $rsmGroupCenter->addScalarResult('id', 'id');
            $rsmGroupCenter->addScalarResult('permissionsGroup_id', 'permissionsGroup_id');
            $rsmGroupCenter->addScalarResult('center_id', 'center_id');
            
            $groupCenters = $em->createNativeQuery("SELECT id, permissionsGroup_id, center_id "
                    . "FROM group_centers "
                    . "WHERE id = :groupcenter_id AND permissionsGroup_id IS NULL",
                    $rsmGroupCenter)
                    ->setParameter('groupcenter_id', $groupPermissionAssociation['groupcenter_id'])
                    ->getResult();
            
            if (count($groupCenters) === 1) {
                // we have to update this group with the current association
                $em->getConnection()->executeUpdate("UPDATE group_centers "
                        . "SET permissionsGroup_id = ? "
                        . "WHERE id = ?", array(
                            $groupPermissionAssociation['permissionsgroup_id'], 
                            $groupPermissionAssociation['groupcenter_id'])
                        );
            } elseif (count($groupCenters) === 0) {
                // the association was multiple. We have to create a new group_center
                $rsmNewId = new ResultSetMapping();
                $rsmNewId->addScalarResult('new_id', 'new_id');
                $newId = $em->createNativeQuery("select nextval('group_centers_id_seq') as new_id",
                        $rsmNewId)
                        ->getSingleScalarResult();
                
                $em->getConnection()->insert("group_centers", array(
                            'id' =>  $newId,
                            'center_id' => $group_center['center_id'],
                            'permissionsGroup_id' => $groupPermissionAssociation['permissionsgroup_id']
                        ));
                
                // we have to link existing users to new created groupcenter
                $em->getConnection()->executeQuery('INSERT INTO user_groupcenter '
                        . '(user_id, groupcenter_id) SELECT user_id, '.$newId.' '
                        . 'FROM user_groupcenter WHERE groupcenter_id = '
                        .$groupPermissionAssociation['groupcenter_id']);
            } else {
                throw new \RuntimeException("Error in the data : we should not have two groupCenter "
                        . "with the same id !");
            }
        }
        
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE group_centers DROP CONSTRAINT FK_A14D8F3D447BBB3B');
        $this->addSql('DROP INDEX IDX_A14D8F3D447BBB3B');
        $this->addSql('ALTER TABLE group_centers DROP permissionGroup_id');
        
    }

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        if ($container === NULL) {
            throw new \RuntimeException('Container is not provided. This migration '
                    . 'need container to set a default center');
        }
        
        $this->container = $container;
    }

}
