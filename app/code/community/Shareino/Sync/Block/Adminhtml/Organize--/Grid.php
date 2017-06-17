<?php

class Shareino_Sync_Block_Adminhtml_Organize_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('id_shareino_organized');
        $this->setId('id_shareino_organized');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'sync/organize';
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
        $this->addColumn('id_shareino_organized', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id_shareino_organized'
            )
        );

        $this->addColumn('cat_id', array(
            'header' => $this->__('کد دسته بندی'),
            'index' => 'cat_id',
            'width' => '50px'
            )
        );
        $this->addColumn('name', array(
            'header' => $this->__('نام'),
            'index' => 'name',
            )
        );
        $this->addColumn('names', array(
            'header' => $this->__('نام معادل'),
            'index' => 'names',
            )
        );
        $this->addColumn('ids', array(
            'header' => $this->__('کد دسته های معادل'),
            'index' => 'ids',
            'width' => '50px'
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
