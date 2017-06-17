<?php

class Shareino_Sync_Block_Adminhtml_Sync_Operations extends Mage_Adminhtml_Block_Abstract
{

    public function getOperationAction($op)
    {
        return $this->getUrl("*/*/$op");
    }

}
