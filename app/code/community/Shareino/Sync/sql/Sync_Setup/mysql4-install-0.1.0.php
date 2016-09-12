<?php
/**
 * Created by PhpStorm.
 * User: keramatifar
 * Date: 09/09/2016
 * Time: 01:34 AM
 * 
 */
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


-- DROP TABLE IF EXISTS {$this->getTable('shareino_organized')};
CREATE TABLE IF NOT EXISTS `{$this->getTable('shareino_organized')}` (
  `id_shareino_organized` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(50) DEFAULT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `ids` varchar(200) NOT NULL,
  `names` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_shareino_organized`),
  UNIQUE KEY `cat_id_UNIQUE` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='ShareIno Organized;

");

$installer->endSetup();
