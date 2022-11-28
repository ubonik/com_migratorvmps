<?php

namespace Migratorvmps\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Configuration;

class MainRepository extends EntityRepository
{
    private $tables_filled;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;        
    }

    public function insertTables()
    {   
        $this->tables_filled = $this->getTablesFilled();      
        
        if (!$this->tables_filled) {
            $file = file_get_contents(_PS_MODULE_DIR_ . "/migratorvmps/sql/dump.sql");
            $sql = $this->splitSql($file);
       
			foreach($sql as $query) 
			{           
			   // Db::getInstance()->execute($query);	
				
				$query = $this->connection->prepare($query);
				$query->execute();           
			}

/*
            $sql_product_lang = "INSERT INTO `ps_product_lang` ( 
                `id_product`,            
                `id_shop`, 
                `id_lang`, 
                `description`, 
                `description_short`, 
                `link_rewrite`, 
                `name`
                )
            SELECT 
                `id_product`,                        
                `id_shop`, 
                `id_lang`, 
                `description`, 
                `description_short`, 
                `link_rewrite`, 
                `name`                        
            FROM `migratorps`;        
            ";
            
                $query = $this->connection->prepare($sql_product_lang);
                $query->execute();
            
                
            $sql_product =  "INSERT INTO `ps_product` ( 
                `id_product`,            
                `id_shop_default`,
                `id_tax_rules_group`,
                `date_add`,
                `date_upd`
                )
            SELECT 
                `id_product`,                        
                `id_shop`,
                `id_tax_rules_group`,
                `date_add`,
                `date_upd`                                
            FROM `migratorps`;        
            ";
            
            $query = $this->connection->prepare($sql_product);
            $query->execute(); 
            
            $sql_product_shop =  "INSERT INTO `ps_product_shop` ( 
                `id_product`,            
                `id_shop`,
                `id_tax_rules_group`,
                `date_add`,
                `date_upd`
                )
            SELECT 
                `id_product`,                        
                `id_shop`,
                `id_tax_rules_group`,
                `date_add`,
                `date_upd`                                
            FROM `migratorps`;        
            ";
                
            $query = $this->connection->prepare($sql_product_shop);
            $query->execute(); 
            
            $sql_image_shop =  "INSERT INTO `ps_image_shop` ( 
                `id_product`,
                `id_image`,            
                `id_shop`
                )
            SELECT 
                `id_product`, 
                `id_product`,                      
                `id_shop`                                
            FROM `migratorps`;        
            ";
                
            $query = $this->connection->prepare($sql_image_shop);
            $query->execute();

            $sql_image =  "INSERT INTO `ps_image` (
                `id_image`, 
                `id_product`    
            )
            SELECT 
                `id_product`, 
                `id_product`                                            
            FROM `migratorps`;        
            ";
                
            $query = $this->connection->prepare($sql_image);
            $query->execute();

            $sql_image_lang =  "INSERT INTO `ps_image_lang` (
                `id_image`, 
                `id_lang`    
            )
            SELECT 
                `id_product`, 
                `id_lang`                                            
            FROM `migratorps`;        
            ";
                
            $query = $this->connection->prepare($sql_image_lang);
            $query->execute(); */

            Configuration::updateValue('MIGRATORVMPS_TABLES_FILLED', 1);           
        }                    
    }

    public function resetTables()
    {        
        $sql_product_lang =  "TRUNCATE `ps_product_lang`;";            
        $query = $this->connection->prepare($sql_product_lang);        
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_product_lang` AUTO_INCREMENT = 1;");
        $query->execute();

        $sql_product =  "TRUNCATE `ps_product`;";            
        $query = $this->connection->prepare($sql_product);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_product` AUTO_INCREMENT = 1;");
        $query->execute();

        $sql_product_shop =  "TRUNCATE `ps_product_shop`;";            
        $query = $this->connection->prepare($sql_product_shop);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_product_shop` AUTO_INCREMENT = 1;");
        $query->execute();

        $sql_image_shop =  "TRUNCATE `ps_image_shop`;";            
        $query = $this->connection->prepare($sql_image_shop);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_image_shop` AUTO_INCREMENT = 1;");
        $query->execute();

        $sql_image =  "TRUNCATE `ps_image`;";            
        $query = $this->connection->prepare($sql_image);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_image` AUTO_INCREMENT = 1;");
        $query->execute();
        
        $sql_image_lang =  "TRUNCATE `ps_image_lang`;";            
        $query = $this->connection->prepare($sql_image_lang);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_image_lang` AUTO_INCREMENT = 1;");
        $query->execute();
        
        $sql_product_lang =  "TRUNCATE `ps_category_lang`;";            
        $query = $this->connection->prepare($sql_product_lang);        
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_category_lang` AUTO_INCREMENT = 1;");
        $query->execute();

        $sql_category =  "TRUNCATE `ps_category`;";            
        $query = $this->connection->prepare($sql_category);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_category` AUTO_INCREMENT = 1;");
        $query->execute();
        
        $sql_category_product =  "TRUNCATE `ps_category_product`;";            
        $query = $this->connection->prepare($sql_category_product);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_category` AUTO_INCREMENT = 1;");
        $query->execute();

        $sql_category_shop =  "TRUNCATE `ps_category_shop`;";            
        $query = $this->connection->prepare($sql_category_shop);
        $query->execute();
        $query = $this->connection->prepare("ALTER TABLE  `ps_category_shop` AUTO_INCREMENT = 1;");
        $query->execute();
        $sql_category_group =  "TRUNCATE `ps_category_group`;";            
        $query = $this->connection->prepare($sql_category_group);
        $query->execute();

        Configuration::updateValue('MIGRATORVMPS_TABLES_FILLED', 0);              
    }

    public function getTablesFilled()
    {
        return Configuration::get('MIGRATORVMPS_TABLES_FILLED');
    }

    public function getTablesFilledMessage()
    {
        $tables_filled = $this->getTablesFilled();
        if ($tables_filled) {
            return "Таблицы уже заполнены";
        }

        return "Таблицы не заполнены";
    }

}
