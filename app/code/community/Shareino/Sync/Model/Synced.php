<?php

class Shareino_Sync_Model_Synced extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('Shareino_sync/synced','synced_id');
    }
}