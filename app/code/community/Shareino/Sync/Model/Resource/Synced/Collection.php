<?php
/**
 * Created by PhpStorm.
 * User: darvish
 * Date: 9/17/16
 * Time: 3:07 PM
 */
class Shareino_Sync_Model_Resource_Synced_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct()
    {
        $this->_init('sync/synced');
    }
}