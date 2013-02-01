<?php
class plgsystemconpayInstallerScript
{
	private $price = 40;

	/**
	 * Constructor
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __constructor(JAdapterInstance $adapter) {
	}

	/**
	 * Called before any type of action
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 * @return  boolean  True on success
	 */
	public function preflight($route, JAdapterInstance $adapter) {
	}

	/**
	 * Called after any type of action
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 * @return  boolean  True on success
	 */
	public function postflight($route, JAdapterInstance $adapter) {
	}

	/**
	 * Called on installation
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter)
	{
		$plugin = JPluginHelper::getPlugin('system', 'conpay');
		$params = new JParameter($plugin->params);

		$db =& JFactory::getDBO();

		// TODO: Use JText::_('SYSTEM_CONPAY_CREDIT_PURCHASE')
		$query = "INSERT INTO #__virtuemart_customs "."(custom_title, custom_value, field_type, published, is_hidden, custom_params) "."VALUES ('".JText::_('Credit purchase')."', '0', 'B', 1, 1, 'plg_conpay')";
		$db->setQuery($query);
		if (!$db->query()) {
			return false;
		}

		$params->set('credit_purchase_field_id', ($insert_id = $db->insertid()));
		$query = "UPDATE #__extensions SET "."params = ".$db->quote($params->toString())." "."WHERE element = 'conpay' AND folder = 'system'";
		$db->setQuery($query);

		if (!$db->query()) {
			return false;
		}
		if (!$this->insert_custom_fields($db, $insert_id)) {
			return false;
		}
		if (!$this->insert_custom_fields($db, $insert_id, 0, '<=')) {
			return false;
		}

		return true;
	}

	/**
	 * Binds custom field 'Credit purchase' to products
	 * @param $db
	 * @param $cid
	 * @param int $val
	 * @param string $op
	 * @return bool
	 */
	private function insert_custom_fields(&$db, $cid, $val = 1, $op = '>')
	{
		$query = "INSERT INTO #__virtuemart_product_customfields "."(virtuemart_product_id, virtuemart_custom_id, custom_value) "."SELECT p.virtuemart_product_id, ".$cid.", ".$val." "."FROM #__virtuemart_product_prices p WHERE p.product_price ".$op." ".$this->price;
		$db->setQuery($query);

		return $db->query() ? true : false;
	}

	/**
	 * Called on update
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter) {
	}

	/**
	 * Called on uninstallation
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 * @return bool
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		$db =& JFactory::getDBO();
		$query = "DELETE FROM #__virtuemart_product_customfields "."WHERE virtuemart_custom_id IN ("."SELECT virtuemart_custom_id "."FROM #__virtuemart_customs ".($if = "WHERE custom_params = 'plg_conpay'").")";

		$db->setQuery($query);

		if (!$db->query()) {
			return false;
		}

		$query = "DELETE FROM #__virtuemart_customs ".$if;
		$db->setQuery($query);

		return $db->query() ? true : false;
	}
}
