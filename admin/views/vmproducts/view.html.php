<?php

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;

class MigratorvmpsViewVmproducts extends HtmlView
{
    /**
     * An array of items.
     *
     * @var  array
     *
     * @since  2.0.0
     */
    protected $items;
    public $pagination;    

    public function display($tpl = null)
    {
        $this->items = $this->get('items');       
        $this->pagination = $this->get('pagination');
        $this->addToolbar();

        return parent::display($tpl);
    }

    /**
     * Add title and toolbar.
     *
     * @since  2.0.0
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_MIGRATORVMPS'));
        ToolbarHelper::custom('vmproducts.copyProducts', '', '', 'COM_MIGRATORVMPS_COPY_PRODUCTS_WITH_CATEGORIES', false);
        ToolbarHelper::custom('vmproducts.copyImages', '', '', 'COM_MIGRATORVMPS_COPY_IMAGES', false);
        ToolbarHelper::custom('vmproducts.createQueryList', '', '', 'COM_MIGRATORVMPS_PREPARING_PRESTASHOP_MODULE', false);        
        ToolbarHelper::custom('vmproducts.archiveProducts', '', '', 'COM_MIGRATORVMPS_DOWNLOAD_PRESTASHOP_MODULE', false);
        ToolbarHelper::custom('vmproducts.resetData', '', '', 'COM_MIGRATORVMPS_RESET', false);
    }
}
