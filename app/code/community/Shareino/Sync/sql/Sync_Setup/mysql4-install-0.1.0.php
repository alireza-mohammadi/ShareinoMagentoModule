<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('shareino_sync')};
CREATE TABLE {$this->getTable('shareino_sync')} (
      `id_shareino_sync` INT NOT NULL AUTO_INCREMENT,
      `product_id` INT NOT NULL,
      `status` TINYINT NULL,
      `errors` VARCHAR(500) NULL,
      `date_add` DATETIME NULL,
      `date_upd` DATETIME NULL,
      PRIMARY KEY (`id_shareino_sync`),
      UNIQUE INDEX `product_id_UNIQUE` (`product_id` ASC))DEFAULT CHARSET=utf8 COMMENT='ShareIno Sync;

insert  into {$this->getTable('shareino_sync')}
(`product_id`,`status`)
 values 
select id as `product_id`,0 AS `status` from  `{$this->getTable('posts')}`
      where post_type='product'
      and post_status='publish';
");

$installer->endSetup();
