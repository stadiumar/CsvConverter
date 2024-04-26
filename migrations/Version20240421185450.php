<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240421185450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX str_product_code ON tbl_product_data');
        $this->addSql('ALTER TABLE tbl_product_data ADD stock VARCHAR(50) DEFAULT NULL, ADD cost_gbp DOUBLE PRECISION DEFAULT NULL, CHANGE int_product_data_id int_product_data_id INT NOT NULL, CHANGE stm_timestamp stm_timestamp DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tbl_product_data MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON tbl_product_data');
        $this->addSql('ALTER TABLE tbl_product_data DROP id, DROP stock, DROP cost_gbp, CHANGE int_product_data_id int_product_data_id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE stm_timestamp stm_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX str_product_code ON tbl_product_data (str_product_code)');
        $this->addSql('ALTER TABLE tbl_product_data ADD PRIMARY KEY (int_product_data_id)');
    }
}
