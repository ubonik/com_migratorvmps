<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

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

        $this->displayName = $this->trans('migratorvmps', [], 'Modules.Migratorvmps.Migratorvmps');
        $this->description = $this->trans('A module for transferring products and categories 
            from the Virtuemart 3 component (Joomla 3) to Prestashop 1.7.8.', [], 'Modules.Migratorvmps.Migratorvmps');
        $this->confirmUninstall = $this->trans('Delete?', [], 'Modules.Migratorvmps.Migratorvmps');

        $tabNames = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tabNames[$lang['locale']] = $this->trans('Migrator VMPS', array(), 'Modules.Migratorvmps.Admin', $lang['locale']);
        }
        $this->tabs = [
            [
                'route_name' => 'main',
                'class_name' => 'MigratorvmpsMainController',
                'visible' => true,
                'name' => $tabNames,
                'parent_class_name' => 'DEFAULT',
                'wording' => 'Migrator VMPS',
                'wording_domain' => 'Modules.Migratorvmps.Admin'
            ],
        ];
    }

    public function install()
    {
        return (parent::install()
            && Configuration::updateValue('MIGRATORVMPS_TABLES_FILLED', 0)
        );
    }

    public function uninstall()
    {
        return (parent::uninstall()
            && Configuration::deleteByName('MIGRATORVMPS_TABLES_FILLED')
            && $this->uninstallTab()
        );
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            SymfonyContainer::getInstance()->get('router')->generate('main')
        );
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('MigratorvmpsMainController');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }    
}
