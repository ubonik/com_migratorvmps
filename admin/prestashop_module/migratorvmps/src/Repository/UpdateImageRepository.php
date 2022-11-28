<?php

namespace Migratorvmps\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use ImageManager;
use Product;
use PrestaShop\PrestaShop\Adapter\Image\ImageValidator;
use PrestaShop\PrestaShop\Adapter\Image\Uploader\EmployeeImageUploader;
use Uploader;

class UpdateImageRepository extends EntityRepository
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;        
    }   

    public function updateImage()
    {
      // $image = ImageManager::getMimeTypeByExtension();
      /*
      $sql = "SELECT `id_image` FROM `ps_image`";
      $query = $this->connection->prepare($sql);
      $query->execute();
      //dump($query->fetch());
      dump($query);
      $image = scandir(_PS_PROD_IMG_DIR_);
     // dump($image);
     */
/*
       $image = ImageManager::create('png', 
             _PS_PROD_IMG_DIR_ . '/9/6/96.png', '96');


 */


/*
 $real_image = _PS_PROD_IMG_DIR_ . '/9/4/94.png';
 $cache = '94.png';

$image = ImageManager::thumbnail($real_image,  $cache, 64, 'jpg', true, true);
*/




//$image = ImageManager::resize(_PS_PROD_IMG_DIR_ . '/9/5/95.png',  _PS_PROD_IMG_DIR_ . '/9/5/95.jpg', 64, 64);


//$uploader = new Uploader();

//$image = $uploader->upload(_PS_PROD_IMG_DIR_ . '/9/5/95.jpg', _PS_PROD_IMG_DIR_ . '/9/5/');



     //dump($image);

        //$image = (new ImageValidator(20))->assertIsValidImageType( _PS_PROD_IMG_DIR_ . '/9/6/96.png');
        //dump($image);

        return 12345678;
    }



}