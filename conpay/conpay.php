<?php

defined('_JEXEC') or die('Direct access to this location is not allowed.');

jimport('joomla.plugin.plugin');

class plgSystemConpay extends JPlugin {

	public function __construct( &$subject, $config ) {
		parent::__construct($subject, $config);
	}

	public function onBeforeRender()
	{
		$cart = null;
		$products = array();
		$view = JRequest::getString('view');

		if ('productdetails' == $view) {
			$products = array(($id = JRequest::getInt('virtuemart_product_id')) => VmModel::getModel('Product')->getProduct($id));
		}
		elseif ('cart' == $view)
		{
			$cart = VirtueMartCart::getCart();
			$products = $cart->products;
		}
		else {
			return false;
		}

		$user = null;
		$details = array();
		$user_details = array();
		$price = 0;

		$plugin = JPluginHelper::getPlugin('system', 'conpay');
		$params = new JParameter($plugin->params);

		if (!($cfi = $params->get('credit_purchase_field_id')))
		{
			return false;
		}

		foreach ($products as $i => $product)
		{
			// TODO: check if credit purchase field exists
			$cfs = array();
			if ('cart' == $view) {
				$cfs = VmModel::getModel('customfields')->getProductCustomsField($product);
			}
			else {
				$cfs = $product->customfieldsSorted['normal'];
			}

			foreach ($cfs as $cf)
			{
				if ($cfi == $cf->virtuemart_custom_id && !$cf->custom_value) {
					continue 2;
				}
			}
			
			$q = 1; // $product->quantity;

			$item_details = array(
				'name' => $product->product_name,
				'category' => $product->category_name,
				'id' => $product->virtuemart_product_id,
				'url' => ($host = 'http://'.$_SERVER['HTTP_HOST']).$product->link,
				'quantity' => $q,
			);

			// TODO: find faster way to get product media
			if ('cart' != $view)
			{
				$item_details['image'] = $host.'/'.$product->images[0]->file_url;
				$item_details['price'] = $product->prices['salesPrice'];
			}
			else
			{
				$pm = VmModel::getModel('media')->getFiles(true, false, $i);
				if ($pm && $pm[0]) {
					$item_details['image'] = $host.'/'.$pm[0]->file_url;
				}
				$item_details['price'] = $cart->pricesUnformatted[$i]['salesPrice'];
			}

			$details[] = $item_details;
			$price += $q * $details['price'];
		}

		if (!$details) {
			return false;
		}

		$user =& JFactory::getUser();
		if (!$user->guest)
		{
			if ($v = $user->email) {
				$user_details['email'] = $v;
			}
			if ($v = $user->username) {
				$user_details['login'] = $v;
			}
			if ($v = $user->name) {
				$user_details['user_name'] = $v;
			}
		}

		$cont_id = $params->get('button_container_id');

		$doc =& JFactory::getDocument();
		$doc->addScript("http://www.conpay.ru/public/api/btn.1.6.min.js");

		$script = "
		if (!jQuery) window.onload = mod_conpay;
		else jQuery(document).ready(mod_conpay);

		function mod_conpay() {
			try {
				window.conpay.init('/plugins/system/conpay/conpay-proxy.php', {
					'className': '".$params->get('button_class_name')."',
					'tagName': '".$params->get('button_tag_name')."',
					'text': '".$params->get('button_text')."'
				}".($user_details ? ', '.json_encode($user_details) : '').");
				window.conpay.addButton(".json_encode($details).($cont_id ? ", '".$cont_id."'" : '').");
			} catch(e){};
		}";

		$doc->addScriptDeclaration($script);

		return true;
	}
}
