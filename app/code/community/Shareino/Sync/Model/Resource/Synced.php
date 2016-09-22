<?php

/**
 * Created by PhpStorm.
 * User: darvish
 * Date: 9/17/16
 * Time: 2:24 PM
 */
class Shareino_Sync_Model_Resource_Synced extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('sync/synced', 'id_shareino_synced');
    }
}