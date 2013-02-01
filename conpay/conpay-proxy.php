<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('__JPATH_BASE__', substr($jpb = dirname(__FILE__), 0, strlen($jpb) - 22));

if (file_exists(__JPATH_BASE__ . '/defines.php')) {
	include_once __JPATH_BASE__ . '/defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', __JPATH_BASE__);
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');
require_once(JPATH_BASE . DS . 'libraries' . DS . 'joomla' . DS . 'application' . DS . 'module' . DS . 'helper.php');
jimport('joomla.html.parameter');

$mainframe =& JFactory::getApplication('site');

$plugin = JPluginHelper::getPlugin('system', 'conpay');
$params = new JParameter($plugin->params);

// Подключаем скрипт с классом ConpayProxyModel, выполняющим бизнес-логику
require_once './ConpayProxyModel.php';
try
{
	// Создаем объект класса ConpayProxyModel
	$proxy = new ConpayProxyModel;
	// Устанавливаем свой идентификатор продавца
	$proxy->setMerchantId($params->get('merchant_id'));
	// Устанавливаем кодировку, используемую на сайте (по-умолчанию 'UTF-8')
	$proxy->setCharset('UTF-8');
	// Выполняем запрос, выводя его результат
	echo $proxy->sendRequest();
}
catch (Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
