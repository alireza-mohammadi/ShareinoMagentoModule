<?php
/**
 * Created by IntelliJ IDEA.
 * User: saeed
 * Date: 8/17/16
 * Time: 3:23 PM
 */

class Shareino_Sync_Model_Synced_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract{
    protected function _constuct(){
        $this->_init('Shareino_sync/synced');
    }
}