<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add postal code and addresses
 */
class Version20160310122322 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE chill_main_address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE chill_main_postal_code_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chill_main_address ('
              . 'id INT NOT NULL, '
              . 'postcode_id INT DEFAULT NULL, '
              . 'streetAddress1 VARCHAR(255) NOT NULL, '
              . 'streetAddress2 VARCHAR(255) NOT NULL, '
              . 'validFrom DATE NOT NULL, '
              . 'PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_165051F6EECBFDF1 ON chill_main_address '
              . '(postcode_id)');
        $this->addSql('CREATE TABLE chill_main_postal_code ('
              . 'id INT NOT NULL, '
              . 'country_id INT DEFAULT NULL, '
              . 'label VARCHAR(255) NOT NULL, '
              . 'code VARCHAR(100) NOT NULL, '
              . 'PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6CA145FAF92F3E70 ON chill_main_postal_code '
              . '(country_id)');
        $this->addSql('ALTER TABLE chill_main_address ADD CONSTRAINT '
              . 'FK_165051F6EECBFDF1 '
              . 'FOREIGN KEY (postcode_id) '
              . 'REFERENCES chill_main_postal_code (id) '
              . 'NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chill_main_postal_code ADD CONSTRAINT '
              . 'FK_6CA145FAF92F3E70 '
              . 'FOREIGN KEY (country_id) '
              . 'REFERENCES Country (id) '
              . 'NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE chill_main_address '
              . 'DROP CONSTRAINT FK_165051F6EECBFDF1');
        $this->addSql('DROP SEQUENCE chill_main_address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE chill_main_postal_code_id_seq CASCADE');
        $this->addSql('DROP TABLE chill_main_address');
        $this->addSql('DROP TABLE chill_main_postal_code');
    }
}
