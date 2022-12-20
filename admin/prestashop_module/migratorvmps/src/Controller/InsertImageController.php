<?php

namespace Migratorvmps\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Migratorvmps\Repository\InsertImageRepository;

class InsertImageController extends FrameworkBundleAdminController
{      
    public function insertImageAction()
    {        
        $repository = $this->get('prestashop.module.migratorvmps.insertimagerepository');
        $message = $repository->insertImage();        
         
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['message'=>$message]);
    }

}