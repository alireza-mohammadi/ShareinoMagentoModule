<?php

/**
 * Created by PhpStorm.
 * User: darvish
 * Date: 9/17/16
 * Time: 12:37 PM
 */
class Shareino_Sync_Model_Synced extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('sync/synced');
    }
}