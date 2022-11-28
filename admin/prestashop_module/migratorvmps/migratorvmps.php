<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Language;

class Migratorvmps extends Module
{
    public function __construct()
    {
        $this->name = 'migratorvmps';
        $this->tab = 'migratorvmps';
        $this->version = '1.0.0';
        $this->author = 'alex';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('migratorvmps');
        $this->description = $this->l('Description of my module');
        $this->confirmUninstall = $this->l('Delete?');

        $tabNames = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tabNames[$lang['locale']] = $this->trans('MIGRATORVMPS', array(), 'Modules.Migratorvmps.Admin', $lang['locale']);
        }
        $this->tabs = [
            [
                'route_name' => 'main',
                'class_name' => 'MigratorvmpsMainController',
                'visible' => true,
                'name' => $tabNames,
                'parent_class_name' => 'DEFAULT',
                'wording' => 'MIGRATORVMPS',
                'wording_domain' => 'Modules.Migratorvmps.Admin'
            ],
        ];
    }

    public function install()
    {/*
        $file = file_get_contents(__DIR__ . "/sql/dump.sql");
        $sql = $this->splitSql($file);

        foreach($sql as $query)
        {
            Db::getInstance()->execute($query);
        }
*/
        return (parent::install()
            && Configuration::updateValue('MIGRATORVMPS_TABLES_FILLED', 0)
        );
    }

    public function uninstall()
    {
		    return (parent::uninstall()
                && Configuration::deleteByName('MIGRATORVMPS_TABLES_FILLED')
        );
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            SymfonyContainer::getInstance()->get('router')->generate('main')
        );
    }
/*

protected function generateControllerURI()
        {
               $router = SymfonyContainer::getInstance()->get('router');

               return $router->generate('test_configuration');
        }
*/
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
