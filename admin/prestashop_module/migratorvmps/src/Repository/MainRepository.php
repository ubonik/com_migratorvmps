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
        
        if ($this->tables_filled) {
            return "Данные уже заполнены";
        }        
        
        $file = file_get_contents(_PS_MODULE_DIR_ . "/migratorvmps/sql/dump.sql");
        $sql = $this->splitSql($file);
    
        foreach($sql as $query) 
        {  			
            $query = $this->connection->prepare($query);
            $query->execute();           
        }

        Configuration::updateValue('MIGRATORVMPS_TABLES_FILLED', 1);
        
        return "Данные успешно заполнены";                         
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
        
        return "Данные не заполнены";
    }

    public function getTablesFilled()
    {
        return Configuration::get('MIGRATORVMPS_TABLES_FILLED');
    }
    
    public function getTablesFilledMessage()
    {
        $tables_filled = $this->getTablesFilled();
        if ($tables_filled) {
            return "Таблицы заполнены";
        }

        return "Таблицы не заполнены";
    }

    public function splitSql($sql)
    {
        $start = 0;
        $open = false;
        $comment = false;
        $endString = '';
        $end = strlen($sql);
        $queries = array();
        $query = '';

        for ($i = 0; $i < $end; $i++) {
            $current = substr($sql, $i, 1);
            $current2 = substr($sql, $i, 2);
            $current3 = substr($sql, $i, 3);
            $lenEndString = strlen($endString);
            $testEnd = substr($sql, $i, $lenEndString);

            if ($current == '"' || $current == "'" || $current2 == '--'
                || ($current2 == '/*' && $current3 != '/*!' && $current3 != '/*+')
                || ($current == '#' && $current3 != '#__')
                || ($comment && $testEnd == $endString)) {
                // Check if quoted with previous backslash
                $n = 2;

                while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i) {
                    $n++;
                }

                // Not quoted
                if ($n % 2 == 0) {
                    if ($open) {
                        if ($testEnd == $endString) {
                            if ($comment) {
                                $comment = false;
                                if ($lenEndString > 1) {
                                    $i += ($lenEndString - 1);
                                    $current = substr($sql, $i, 1);
                                }
                                $start = $i + 1;
                            }
                            $open = false;
                            $endString = '';
                        }
                    } else {
                        $open = true;
                        if ($current2 == '--') {
                            $endString = "\n";
                            $comment = true;
                        } elseif ($current2 == '/*') {
                            $endString = '*/';
                            $comment = true;
                        } elseif ($current == '#') {
                            $endString = "\n";
                            $comment = true;
                        } else {
                            $endString = $current;
                        }
                        if ($comment && $start < $i) {
                            $query = $query . substr($sql, $start, ($i - $start));
                        }
                    }
                }
            }

            if ($comment) {
                $start = $i + 1;
            }

            if (($current == ';' && !$open) || $i == $end - 1) {
                if ($start <= $i) {
                    $query = $query . substr($sql, $start, ($i - $start + 1));
                }
                $query = trim($query);

                if ($query) {
                    if (($i == $end - 1) && ($current != ';')) {
                        $query = $query . ';';
                    }
                    $queries[] = $query;
                }

                $query = '';
                $start = $i + 1;
            }
        }

        return $queries;
    }
}
