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
      
    }

}