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

			$item_details = array('name' => $product->product_name, 'category' => $product->category_name, 'url' => ($host = 'http://'.$_SERVER['HTTP_HOST']).$product->link, 'quantity' => ($q = $product->quantity),);

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

		$api_key = $params->get('api_key');
		$merchant_id = $params->get('merchant_id');
		$checksum = md5($api_key.'!'.(string)$price.'!'.$merchant_id.(($user_details) ? '!'.implode($user_details, '!') : ''));

		$doc =& JFactory::getDocument();
		$doc->addScript("http://www.conpay.ru/public/js/credits/btn.1.5.proxy.min.js");

		$script .= "
		if (!jQuery) window.onload = mod_conpay;
		else jQuery(document).ready(mod_conpay);

		function mod_conpay() {
			try {
				window.conpay.init('/plugins/system/conpay/conpay-proxy.php', {"."'className': '".$params->get('button_class_name')."', "."'tagName': '".$params->get('button_tag_name')."', "."'text': '".$params->get('button_text')."'}".(($user_details) ? ', '.json_encode($user_details) : '').");
				window.conpay.addButton('".$checksum."', '".(($cont_id = $params->get('button_container_id')) ? $cont_id : 'conpay-link')."', ";

		$script .= json_encode($details);
		$script .= ");
			} catch(e){};
		}";

		$doc->addScriptDeclaration($script);

		return true;
	}
}
