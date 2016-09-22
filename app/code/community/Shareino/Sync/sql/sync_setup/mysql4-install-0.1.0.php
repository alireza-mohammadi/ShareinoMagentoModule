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
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array())
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array());

$installer->getConnection()->createTable($table);

$table = $installer->getConnection()->newTable($installer->getTable('sync/organize'))
    ->addColumn('id_shareino_organized', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
    ))
    ->addColumn('model', Varien_Db_Ddl_Table::TYPE_TEXT, null, array())
    ->addColumn('cat_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ))
    ->addColumn('ids', Varien_Db_Ddl_Table::TYPE_TEXT, null, array())
    ->addColumn('names', Varien_Db_Ddl_Table::TYPE_TEXT, null, array())
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array())
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array());

$installer->getConnection()->createTable($table);

$installer->endSetup();