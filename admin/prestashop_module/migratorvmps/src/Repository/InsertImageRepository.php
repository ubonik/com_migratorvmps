<?php

namespace Migratorvmps\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Migratorvmps;

class InsertImageRepository extends EntityRepository
{
    /**
     * @var Migratorvmps
     */
    private $module;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, Migratorvmps $module)
    {
        $this->connection = $connection;
        $this->module = $module;        
    }

    public function insertImage()
    {
        $s = _PS_MODULE_DIR_ . "migratorvmps/images_categories/";
        $d = _PS_CAT_IMG_DIR_;

               
        $dir = opendir($s);
        while (($file = readdir($dir)) !== false) {
            $a = $s.$file;
            $b = $d.$file;
            if (is_file($a)) {
                copy($a, $b);
            }
        }
        closedir($dir);

        $this->lowering(_PS_MODULE_DIR_ . 'migratorvmps/images', _PS_IMG_DIR_ . 'p');        
        
        return $this->module->getTranslator()->trans('Images are filled', [], 'Modules.Migratorvmps.Admin');    
    }

    private function lowering($dirname, $dirdestination)
    {
        $dir = opendir($dirname);
        while (($file = readdir($dir)) !== false) {
            if (is_file($dirname . '/' . $file)) {
                copy($dirname . '/' . $file, $dirdestination . '/' . $file);
            }
            if (is_dir($dirname . '/' . $file) && $file != '.' && $file != '..') {
                if (!file_exists($dirdestination . '/' . $file)) {
                    mkdir($dirdestination . '/' . $file);
                }

                $this->lowering("$dirname/$file", "$dirdestination/$file");
            }
        }
        closedir($dir);
    }
}
