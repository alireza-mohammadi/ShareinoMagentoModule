<?php
class Shareino_Sync_Model_Organized extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('Shareino_sync/organized','organized_id');
    }
}