<?php

/**
 * Created by Saeed Darvish.
 * Email : sd.saeed.darvish@gmail.com
 * mobile : 09179960554
 */
class Shareino_Sync_Block_Adminhtml_Synced_Grid extends Mage_Adminhtml_Block_Widget_Grid
{


    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('id_shareino_sync');
        $this->setId('id_shareino_sync');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        
    }

    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'sync/synced';
    }


    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
//        $collection = Mage::getResourceModel($this->_getCollectionClass())->load();;
        $collection = Mage::getModel($this->_getCollectionClass())->getCollection()->load();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('id_shareino_sync',
            array(
                'header' => $this->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'id_shareino_sync'
            )
        );

        $this->addColumn('product_id',
            array(
                'header' => $this->__('کد محصول'),
                'index' => 'product_id',
                'width' => '50px'

            )
        );
        $this->addColumn('status',
            array(
                'header' => $this->__('وضعیت'),
                'index' => 'status',
                'width' => '50px'
            )
        );  $this->addColumn('errors',
            array(
                'header' => $this->__('پیغام ها'),
                'index' => 'errors',
            )
        );
        $this->addColumn('updated_at',
            array(
                'header' => $this->__('تاریخ بروز رسانی'),
                'index' => 'updated_at',
                'width' => '140px'
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}