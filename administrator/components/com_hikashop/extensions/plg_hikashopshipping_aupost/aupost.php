<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.1.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopshippingAupost extends JPlugin
{

	function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages){
		$config =& hikashop_config();
		if(!$config->get('force_shipping') && bccomp($order->weight,0,5)<=0) return true;
		if(!hikashop_loadUser()) return true;
		if(empty($dbrates)){
			$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
		}else{
			$found = false;
			foreach($dbrates as $k => $rate){
				if($rate->shipping_type=='aupost'){
					$found = true;
					if(bccomp($order->total->prices[0]->price_value,0,5)){
					if(@$rate->shipping_params->shipping_min_price>$order->total->prices[0]->price_value){
						$messages['order_total_too_low'] = JText::_('ORDER_TOTAL_TOO_LOW_FOR_SHIPPING_METHODS');
						continue;
					}
				}
				if(!empty($rate->shipping_zone_namekey)){
					$zoneClass=hikashop_get('class.zone');
						$zones = $zoneClass->getOrderZones($order);
					if(!in_array($rate->shipping_zone_namekey,$zones)){
						$messages['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
						continue;
					}
					if(empty($rate->shipping_params->SEA) && empty($rate->shipping_params->AIR) && !empty($order->shipping_address->address_country)){
						$db = JFactory::getDBO();
						if(is_array($order->shipping_address->address_country)){
							$address_country = reset($order->shipping_address->address_country);
						}else{
							$address_country = $order->shipping_address->address_country;
						}
						$db->setQuery('SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey='.$db->Quote($address_country));
						$zone = $db->loadObject();
						if($zone->zone_code_3!='AUS'){
							$messages['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
							continue;
						}
					}
				}
				$check = false;

				if(empty($order->shipping_address->address_post_code)){
					$check = true;
					$message = 'The Australia Post shipping plugin requires the user to enter a postal code when goods are shipped within Australia. Please go to "Display->Custom fields" and set the post code field to required.';
				}elseif(!preg_match('#[0-9]{4}#',$order->shipping_address->address_post_code)){
					$check = true;
					$message = 'The post code entered is not valid';
					$order->shipping_address->address_post_code = preg_replace('#[^0-9A-Z]#','',$order->shipping_address->address_post_code);
				}
				if($check){
					$zoneClass=hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);
					$db = JFactory::getDBO();
					$db->setQuery('SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE zone_code_3='.$db->Quote('AUS'));
					$australia_zone = $db->loadResult();
					if(in_array($australia_zone,$zones)){
						$messages['post_code_missing'] = $message;
						continue;
					}
				}

				$weightClass=hikashop_get('helper.weight');
				$volumeClass=hikashop_get('helper.volume');
				$max_weight = 20000;
				$max_volume = 128625000;
				$max_length = 1050;
				$parcel=new stdClass();
				$parcel->Weight = 0;
				$parcel->global_volume = 0;
				$parcel->Width = 0;
				$parcel->Height = 0;
				$parcel->Length = 0;
				$parcels = array($parcel);
				$i=0;
				if(empty($order->shipping_address_full)){
					$cart = hikashop_get('class.cart');
					$app = JFactory::getApplication();
					$address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
					$cart->loadAddress($order->shipping_address_full,$address,'object','shipping');
				}
				$query = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code=\'AUD\'';
				$db = JFactory::getDBO();
				$db->setQuery($query);
				$currency = $db->loadResult();
				$rates = array();
				if(isset($rate->shipping_params->shipping_group) && $rate->shipping_params->shipping_group)
				{
					foreach($order->products as $k => $product){
						if(!empty($product->cart_product_quantity) && !empty($product->product_weight)){
							if(!isset($product->product_weight_unit_orig))
							{
								if(isset($product->product_weight_orig))
									$product_weight=(int)$weightClass->convert($product->product_weight_orig,$product->product_weight_unit,'g');
								else
									$product_weight=(int)$weightClass->convert($product->product_weight,'','g');
								if(!isset($product->product_dimension_unit))$product->product_dimension_unit = 'm';
								$product_volume=(int)$volumeClass->convert($product->product_length*$product->product_width*$product->product_height,$product->product_dimension_unit,'mm');
								$product_width=(int)$volumeClass->convert($product->product_width,$product->product_dimension_unit,'mm','dimension');
								$product_height=(int)$volumeClass->convert($product->product_height,$product->product_dimension_unit,'mm','dimension');
								$product_length=(int)$volumeClass->convert($product->product_length,$product->product_dimension_unit,'mm','dimension');
							}
							else
							{
								if(!isset($product->product_dimension_unit_orig)){
									$product->product_dimension_unit_orig = $product->product_dimension_unit;
								}
								if(isset($product->product_weight_orig))
									$product_weight=(int)$weightClass->convert($product->product_weight_orig,$product->product_weight_unit_orig,'g');
								else
									$product_weight=(int)$weightClass->convert($product->product_weight,$product->product_weight_unit,'g');
								$product_volume=(int)$volumeClass->convert($product->product_length*$product->product_width*$product->product_height,$product->product_dimension_unit_orig,'mm');
								$product_width=(int)$volumeClass->convert($product->product_width,$product->product_dimension_unit_orig,'mm','dimension');
								$product_height=(int)$volumeClass->convert($product->product_height,$product->product_dimension_unit_orig,'mm','dimension');
								$product_length=(int)$volumeClass->convert($product->product_length,$product->product_dimension_unit_orig,'mm','dimension');
							}
							if($product_weight>$max_weight){
								$messages['items_weight_over_limit'] = JText::_('ITEMS_WEIGHT_TOO_BIG_FOR_SHIPPING_METHODS');
								return true;
							}
							if($product_volume>$max_volume){
								$messages['items_volume_over_limit'] = JText::_('ITEMS_VOLUME_TOO_BIG_FOR_SHIPPING_METHODS');
								return true;
							}
							$items = $product->cart_product_quantity;
							while($items>0){
								$x=min($product_width,$product_height,$product_length);
								if($x==$product_width){
									$y=min($product_height,$product_length);
									if($y==$product_height) $z=$product_length;
									else $z=$product_height;
								}
								if($x==$product_height){
									$y=min($product_width,$product_length);
									if($y==$product_width) $z=$product_length;
									else $z=$product_width;
								}
								if($x==$product_length){
									$y=min($product_height,$product_width);
									if($y==$product_height) $z=$product_width;
									else $z=$product_height;
								}
								if($parcels[$i]->Weight &&
								(($parcels[$i]->Weight+$product_weight>$max_weight ||
								$parcels[$i]->global_volume+$product_volume>$max_volume ||
								$parcels[$i]->Length+$x> $max_length ||
								max($parcels[$i]->Height,$y)>350 ||
								max($parcels[$i]->Width,$z)>350)))
								{
									unset($parcels[$i]->global_volume);
									$parcel = new stdClass();
									$parcel->Weight = 0;
									$parcel->global_volume = 0;
									$parcel->Width = 0;
									$parcel->Height = 0;
									$parcel->Length = 0;
									$parcels[]=$parcel;
									$i++;
								}
								$parcels[$i]->Weight+=$product_weight;
								$parcels[$i]->global_volume+=$product_volume;
								$parcel->Width=max($parcel->Width,$z);
								$parcel->Height=max($parcel->Height,$y);
								$parcel->Length+=$x;
								$items--;
							}
						}
					}
					if($parcels[$i]->Weight<1)$parcels[$i]->Weight=1;
					if(isset($parcels[$i]->global_volume))unset($parcels[$i]->global_volume);
					if($parcels[$i]->Length<150)$parcels[$i]->Length=150;
					if($parcels[$i]->Width<150)$parcels[$i]->Width=150;
					if($parcels[$i]->Height<1)$parcels[$i]->Height=1;
					if($parcels[$i]->Length>1050)$parcels[$i]->Length=1050;
					if($parcels[$i]->Width>350)$parcels[$i]->Width=350;
					if($parcels[$i]->Height>350)$parcels[$i]->Height=350;
					foreach($parcels as $parcel){
						$parcel->Country = @$order->shipping_address_full->shipping_address->address_country->zone_code_2;
						$parcel->Pickup_Postcode = substr(trim(@$rate->shipping_params->post_code),0,4);
						$parcel->Destination_Postcode = substr(trim($order->shipping_address->address_post_code),0,4);
						$parcel->Quantity=1;
						if($parcel->Country=='AU'){
							if(!empty($rate->shipping_params->EXPRESS)){
								$this->addRate($rates,'EXPRESS',$parcel,$rate,$currency);
							}
							if(!empty($rate->shipping_params->STANDARD)){
								$this->addRate($rates,'STANDARD',$parcel,$rate,$currency);
							}
						}else{
							if(!empty($rate->shipping_params->SEA)){
								$this->addRate($rates,'SEA',$parcel,$rate,$currency);
							}
							if(!empty($rate->shipping_params->AIR)){
								$this->addRate($rates,'AIR',$parcel,$rate,$currency);
							}
						}
					}
				}

				if(!isset($rate->shipping_params->shipping_group) || !$rate->shipping_params->shipping_group){
					foreach($order->products as $k => $product){
						if(!empty($product->cart_product_quantity) && !empty($product->product_weight)){
							if(!isset($product->product_weight_unit_orig))
							{
								if(isset($product->product_weight_orig))
									$product_weight=(int)$weightClass->convert($product->product_weight_orig,$product->product_weight_unit,'g');
								else
									$product_weight=(int)$weightClass->convert($product->product_weight,'','g');
								$product_volume=(int)$volumeClass->convert($product->product_length*$product->product_width*$product->product_height,$product->product_dimension_unit,'mm');
								$product_width=(int)$volumeClass->convert($product->product_width,$product->product_dimension_unit,'mm','dimension');
								$product_height=(int)$volumeClass->convert($product->product_height,$product->product_dimension_unit,'mm','dimension');
								$product_length=(int)$volumeClass->convert($product->product_length,$product->product_dimension_unit,'mm','dimension');
							}
							else
							{
								if(!isset($product->product_dimension_unit_orig)){
									$product->product_dimension_unit_orig = $product->product_dimension_unit;
								}
								if(isset($product->product_weight_orig))
									$product_weight=(int)$weightClass->convert($product->product_weight_orig,$product->product_weight_unit_orig,'g');
								else
									$product_weight=(int)$weightClass->convert($product->product_weight,$product->product_weight_unit,'g');
								$product_volume=(int)$volumeClass->convert($product->product_length*$product->product_width*$product->product_height,$product->product_dimension_unit_orig,'mm');
								$product_width=(int)$volumeClass->convert($product->product_width,$product->product_dimension_unit_orig,'mm','dimension');
								$product_height=(int)$volumeClass->convert($product->product_height,$product->product_dimension_unit_orig,'mm','dimension');
								$product_length=(int)$volumeClass->convert($product->product_length,$product->product_dimension_unit_orig,'mm','dimension');
							}
							if($product_weight>$max_weight){
								$messages['items_weight_over_limit'] = JText::_('ITEMS_WEIGHT_TOO_BIG_FOR_SHIPPING_METHODS');
								return true;
							}
							if($product_volume>$max_volume || $product_width>350 || $product_height>350 || $product_length>1050){
								$messages['items_volume_over_limit'] = JText::_('ITEMS_VOLUME_TOO_BIG_FOR_SHIPPING_METHODS');
								return true;
							}
							$parcel->Weight = $product_weight;
							$parcel->global_volume = $product_volume;
							$parcel->Width = $product_width;
							$parcel->Height = $product_height;
							$parcel->Length = $product_length;
							if($parcels[$i]->Weight<1)$parcels[$i]->Weight=1;
							if(isset($parcels[$i]->global_volume))unset($parcels[$i]->global_volume);
							if($parcels[$i]->Length<150)$parcels[$i]->Length=150;
							if($parcels[$i]->Width<150)$parcels[$i]->Width=150;
							if($parcels[$i]->Height<1)$parcels[$i]->Height=1;
							if($parcels[$i]->Length>1050)$parcels[$i]->Length=1050;
							if($parcels[$i]->Width>350)$parcels[$i]->Width=350;
							if($parcels[$i]->Height>350)$parcels[$i]->Height=350;
							$parcel->Country = $order->shipping_address_full->shipping_address->address_country->zone_code_2;
							$parcel->Pickup_Postcode = substr(trim(@$rate->shipping_params->post_code),0,4);
							$parcel->Destination_Postcode = substr(trim($order->shipping_address->address_post_code),0,4);
							$parcel->Quantity=$product->cart_product_quantity;
							if($parcel->Country=='AU'){
								if(!empty($rate->shipping_params->EXPRESS)){
									$this->addRate($rates,'EXPRESS',$parcel,$rate,$currency);
								}
								if(!empty($rate->shipping_params->STANDARD)){
									$this->addRate($rates,'STANDARD',$parcel,$rate,$currency);
								}
							}else{
								if(!empty($rate->shipping_params->SEA)){
									$this->addRate($rates,'SEA',$parcel,$rate,$currency);
								}
								if(!empty($rate->shipping_params->AIR)){
									$this->addRate($rates,'AIR',$parcel,$rate,$currency);
								}

							}
						}
					}
				}
			if(!empty($rate->shipping_params->reverse_order)){
				$rates=array_reverse($rates,true);
			}
			foreach($rates as $rate){
				$usable_rates[]=$rate;
			}
		}
			if(!$found){
				$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
			}
		}
		}
		return true;
	}
	function addRate(&$rates,$type,$parcel,&$rate,$currency){

		$parcel->Service_Type=$type;
		$url='http://drc.edeliver.com.au/ratecalc.asp?';
		foreach(get_object_vars($parcel) as $key => $val){
			$url.=$key.'='.$val.'&';
		}
		$url = rtrim($url,'&');
		$url = parse_url($url);
		if(!isset($url['query'])){
		$url['query'] = '';
		}

		if(!isset($url['port'])){
			if(!empty($url['scheme'])&&in_array($url['scheme'],array('https','ssl'))){
				$url['port'] = 443;
			}else{
			$url['port'] = 80;
			}
		}
		if(!empty($url['scheme'])&&in_array($url['scheme'],array('https','ssl'))){
			$url['host_socket'] = 'ssl://'.$url['host'];
		}else{
			$url['host_socket'] = $url['host'];
		}
		$fp = fsockopen ( $url['host_socket'], $url['port'], $errno, $errstr, 30);
		if (!$fp) {
			$app = JFactory::getApplication();
			$app->enqueueMessage( 'Cannot connect to australia post web service. You hosting company might be blocking outbond connections');
			return false;
		}
		$uri = $url['path'].($url['query']!='' ? '?' . $url['query'] : '');
		$header = "GET $uri HTTP/1.0\r\n".
			"User-Agent: PHP/".phpversion()."\r\n".
			"Referer: ".hikashop_currentURL()."\r\n".
			"Server: ".$_SERVER['SERVER_SOFTWARE']."\r\n".
			"Host: ".$url['host'].":".$url['port']."\r\n".
			"Accept: */"."*\r\n\r\n";

			fwrite($fp, $header);
		$response = '';
		while (!feof($fp)) {
			$response .= fgets ($fp, 1024);
		}
		fclose ($fp);
		$pos = strpos($response, "\r\n\r\n");
		$header = substr($response, 0, $pos);
		$body = substr($response, $pos + 2 * strlen("\r\n\r\n"));
		if(preg_match_all('#([a-z_]+)=([a-z_\.0-9 ]+?)#Ui',$response,$matches)){
			$data = array();
			foreach($matches[1] as $key=>$val){
				$data[$val]=$matches[2][$key];
			}
			if(!empty($data['err_msg'])){
				if($data['err_msg']=='OK'){
					if(empty($rates[$type])){
						$info = new stdClass();
						$info->shipping_name = $rate->shipping_name.' '.JText::_($type);
						$shipping_description = JText::_($type.'_DESCRIPTION');
						if($shipping_description ==$type.'_DESCRIPTION'){
							$shipping_description = '';
						}
						if(empty($shipping_description)){
							$shipping_description = $rate->shipping_description;
						}
						$info->shipping_description=$shipping_description;
						$info->packages = 1;
						$info->shipping_id = $type;
						$info->shipping_type='aupost';
						$info->shipping_price = 0.0;
						if(!empty($rate->shipping_params->additional_fee)){
							$info->shipping_price += $rate->shipping_params->additional_fee;
						}
						$info->shipping_currency_id = $currency;
						$info->shipping_images = $rate->shipping_images;
						$info->shipping_tax_id = $rate->shipping_tax_id;
						$rates[$type]=$info;
					} else {
						$rates[$type]->packages++;
						$shipping_description = JText::_($type.'_DESCRIPTION');
						if($shipping_description ==$type.'_DESCRIPTION'){ $shipping_description = ''; }
						if(empty($shipping_description)){ $shipping_description = $rate->shipping_description; }
						if(!empty($shipping_description)){ $shipping_description .= '<br/>'; }
						$rates[$type]->shipping_description = $shipping_description . JText::sprintf('X_PACKAGES', $rates[$type]->packages);
					}
					if(@$rates[$type]->shipping_tax_id){
						$currencyClass = hikashop_get('class.currency');
						$data['charge'] = $currencyClass->getUntaxedPrice($data['charge'],hikashop_getZone(),$rates[$type]->shipping_tax_id);
					}
					$rates[$type]->shipping_price += $data['charge'];
				}elseif(!empty($data['err_msg'])){
					if(preg_match('#Selected Destination not reached by .*#i',$data['err_msg'])){
						return true;
					}
					$app = JFactory::getApplication();
					$app->enqueueMessage('The request to the Australia Post server failed with the message: '.$data['err_msg']);
				}else{
					$app = JFactory::getApplication();
					$app->enqueueMessage('The request to the Australia Post server failed');
				}
			}
		}
	}
	function shippingMethods(&$main){
		$methods = array();
		if(!empty($main->shipping_params->SEA)){
			$methods['SEA']=$main->shipping_name.' '.JText::_('SEA');
		}
		if(!empty($main->shipping_params->AIR)){
			$methods['AIR']=$main->shipping_name.' '.JText::_('AIR');
		}
		if(!empty($main->shipping_params->EXPRESS)){
			$methods['EXPRESS']=$main->shipping_name.' '.JText::_('EXPRESS');
		}
		if(!empty($main->shipping_params->STANDARD)){
			$methods['STANDARD']=$main->shipping_name.' '.JText::_('STANDARD');
		}
		return $methods;
		}

		function onShippingConfiguration(&$elements){
			$this->aupost = JRequest::getCmd('name','aupost');
			$this->currency = hikashop_get('type.currency');
			$this->categoryType = hikashop_get('type.categorysub');
			$this->categoryType->type = 'tax';
			$this->categoryType->field = 'category_id';


			if(empty($elements)){
			$element = new stdClass();
				$element->shipping_name='Australia Post';
				$element->shipping_description='';
				$element->shipping_images='aupost';
				$element->shipping_type=$this->aupost;
				$element->shipping_params=new stdClass();
				$element->shipping_params->AIR='AIR';
				$element->shipping_params->SEA='SEA';
				$element->shipping_params->STANDARD='STANDARD';
				$element->shipping_params->EXPRESS='EXPRESS';
				$element->shipping_params->post_code='';
				$elements = array($element);
			}
			$this->toolbar = array(
				'save',
				'apply',
				array('name' => 'link', 'icon'=> 'cancel', 'alt' => JText::_('HIKA_CANCEL'),'url'=>hikashop_completeLink('plugins&plugin_type=shipping')),
				'|',
				array('name' => 'pophelp', 'target' =>'shipping-'.$this->aupost.'-form')
			);

			hikashop_setTitle(JText::_('HIKASHOP_SHIPPING_METHOD'),'plugin','plugins&plugin_type=shipping&task=edit&name='.$this->aupost);
		}

		function onShippingConfigurationSave(&$elements){
			return true;
		}

	function onShippingSave(&$cart,&$methods,&$shipping_id){
			$usable_mehtods = array();
			$errors = array();
			$this->onShippingDisplay($cart,$methods,$usable_mehtods,$errors);
			foreach($usable_mehtods as $k => $usable_method){
				if($usable_method->shipping_id==$shipping_id){
					return $usable_method;
				}
			}

			return false;
		}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
			return true;
		}
}
