<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150325165224 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `blog_comment`
            CHANGE `created_at` `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `blog_comment`
            CHANGE `created_at` `created_at` DATETIME NOT NULL;
        ");
    }
}
