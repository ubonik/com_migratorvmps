<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$controller = BaseController::getInstance('migratorvmps');
$controller->registerTask('unconfirm', 'confirm');
JLoader::register('MigratorvmpsHelper', __DIR__ . '/helpers/migratorvmps.php');
$controller->execute(Factory::getApplication()->input->get('task', 'display'));
$controller->redirect();
