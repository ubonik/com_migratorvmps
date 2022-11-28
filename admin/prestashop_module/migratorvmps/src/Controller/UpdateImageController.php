<?php

// modules/your-module/src/Controller/DemoController.php 
namespace Migratorvmps\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Migratorvmps\Repository\UpdateImageRepository;

class UpdateImageController extends FrameworkBundleAdminController
{   
    /*     
    public function demoAction()
    {       
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['test'=>'qwertyuio']);
    }

    public function insertAction()
    {        
        $repository = $this->get('prestashop.module.migratorvmps.repository');
        $name = $repository->insert();

       dump($name);  
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['test'=>$name]);
    }
    */
    public function updateImageAction()
    {        
        $repository = $this->get('prestashop.module.migratorvmps.updateimagerepository');
        $name = $repository->updateImage();
//$name = 10000;
       dump($name);  
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['test1'=>$name]);
    }

}