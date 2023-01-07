<?php

namespace Migratorvmps\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Configuration;
use Migratorvmps;

class MainRepository extends EntityRepository
{
    /**
     * @var Migratorvmps
     */    
    private $module;

    /**
     * The variable stores the value true if the tables are filled with new data, or false if not.
     * 
     *  @var bool
     * */
    private $tables_filled;

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
     *The method fills the tables of products and categories with data from the $file file.
     *
     * @return string
     */
    public function insertTables(): string
    {
        $this->tables_filled = $this->getTablesFilled();

        if ($this->tables_filled) {
            return $this->module->getTranslator()->trans('The data is already filled', [], 'Modules.Migratorvmps.Admin');
        }

        $file = file_get_contents(_PS_MODULE_DIR_ . "/migratorvmps/sql/dump.sql");
        $sql = $this->splitSql($file);

        foreach ($sql as $query) {
            $query = $this->connection->prepare($query);
            $query->execute();
        }

        Configuration::updateValue('MIGRATORVMPS_TABLES_FILLED', 1);

        return $this->module->getTranslator()->trans('The data has been filled in successfully', [], 'Modules.Migratorvmps.Admin');
    }

    /**
     * Method for resetting the filled data.
     *
     * @return string
     */
    public function resetTables(): string
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

        return $this->module->getTranslator()->trans('The data is not filled', [], 'Modules.Migratorvmps.Admin');
    }

    /**
     * The method gets the value of the MIGRATORVMPS_TABLES_FILLED variable.
     *
     * @return boolean
     */
    public function getTablesFilled(): bool
    {
        return Configuration::get('MIGRATORVMPS_TABLES_FILLED');
    }

    /**
     *The method generates a message based on the value of the MIGRATORVMPS_TABLES_FILLED variable.
     *
     * @return string
     */
    public function getTablesFilledMessage(): string
    {
        $tables_filled = $this->getTablesFilled();

        if ($tables_filled) {
            return $this->module->getTranslator()->trans('The data is filled', [], 'Modules.Migratorvmps.Admin');
        }

        return $this->module->getTranslator()
            ->trans('The data is not filled', [], 'Modules.Migratorvmps.Admin');
    }

    /**
     * Splits a string of multiple queries into an array of individual queries.
     * Single line or line end comments and multi line comments are stripped off.
     *
     * @param   string  $sql  Input SQL string with which to split into individual queries.
     *
     * @return  array  The queries from the input string separated into an array.
     */
    public function splitSql(string $sql): array
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
