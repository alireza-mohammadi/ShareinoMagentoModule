<?php

class Shareino_Sync_Model_Resource_Configured_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('sync/configured');
    }

}
