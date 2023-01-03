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
       
        return $this->render('@Modules/migratorvmps/views/templates/admin/main.html.twig', ['message' => $message]);
    }

    public function insertAction()
    {        
        $repository = $this->get('prestashop.module.migratorvmps.mainrepository');        
        $message = $repository->insertTables();
        
        return $this->render('@Modules/migratorvmps/views/templates/admin/main.html.twig', ['message' => $message]);
    }

    public function resetAction()
    {
        $repository = $this->get('prestashop.module.migratorvmps.mainrepository');        
        $message = $repository->resetTables();
        
        return $this->render('@Modules/migratorvmps/views/templates/admin/main.html.twig', ['message' => $message]);
    }

}