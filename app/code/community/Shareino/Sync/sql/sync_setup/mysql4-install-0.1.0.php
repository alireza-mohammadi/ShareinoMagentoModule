<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('sync/synced'))
    ->addColumn('id_shareino_sync', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
    ))
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ))
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned' => true,
        'nullable' => false,
    ))
    ->addColumn('errors', Varien_Db_Ddl_Table::TYPE_TEXT, null, array())
    ->addColumn('date_add', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array('nullable' => true))
    ->addColumn('date_upd', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array('nullable' => true));

$installer->getConnection()->createTable($table);

$installer->endSetup();
