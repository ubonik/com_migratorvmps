<?php

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

class MigratorvmpsControllerVmproducts extends AdminController
{
    public function getModel($name = 'Vmproducts', $prefix = 'MigratorvmpsModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function copyProducts()
    {
        $model = $this->getModel();
        $model->copyProducts();
        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    public function resetData()
    {
        $model = $this->getModel();       
        $model->resetData();
        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    public function copyImagesProducts()
    {
        $model = $this->getModel();
        $model->copyImagesProducts();
        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    public function copyImagesCategories(){
        $model = $this->getModel();
        $model->copyImagesCategories();
        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

    }

    public function createQueryList()
    {
        $model = $this->getModel();
        $model->createQueryList();
        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    public function archiveProducts()
    {
        $model = $this->getModel();
        $model->archiveProducts();

        $this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }
}
