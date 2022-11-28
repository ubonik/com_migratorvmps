<?php
 
namespace Migratorvmps\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Migratorvmps\Repository\MainRepository;

class MainController extends FrameworkBundleAdminController
{        
    public function mainAction()
    {   
        $repository = $this->get('prestashop.module.migratorvmps.mainrepository');
        $message = $repository->getTablesFilledMessage();        
       
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['message' => $message]);
    }

    public function insertAction()
    {        
        $repository = $this->get('prestashop.module.migratorvmps.mainrepository');
        $repository->insertTables();
        $message = $repository->getTablesFilledMessage();
        
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['message' => $message]);
    }

    public function resetAction()
    {
        $repository = $this->get('prestashop.module.migratorvmps.mainrepository');
        $repository->resetTables();
        $message = $repository->getTablesFilledMessage();
        
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['message' => $message]);
    }

}