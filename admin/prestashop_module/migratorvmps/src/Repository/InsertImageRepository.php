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

    /**
     * The method of transferring images from the module to the cms Prestashop folders.
     *
     * @return string
     */
    public function insertImage(): string
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

    /**
     * The method of transferring files and folders of product images from 
     * the module to the /img/p cms Prestashop folder.
     *
     * @param string $dirname
     * @param string $dirdestination
     * @return void
     */
    private function lowering(string $dirname, string $dirdestination): void
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
