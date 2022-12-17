<?php

namespace Migratorvmps\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Migratorvmps\Repository\UpdateImageRepository;

class UpdateImageController extends FrameworkBundleAdminController
{      
    public function updateImageAction()
    {        
        $repository = $this->get('prestashop.module.migratorvmps.updateimagerepository');
        $name = $repository->updateImage();
        
        return $this->render('@Modules/migratorvmps/views/templates/admin/demo.html.twig', ['test1'=>$name]);
    }
}