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

class UserViewUser extends hikashopView{
	var $ctrl= 'user';
	var $nameListing = 'USERS';
	var $nameForm = 'HIKA_USER';
	var $icon = 'user';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','user',false);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->filter_partner = $app->getUserStateFromRequest( $this->paramBase.".filter_partner",'filter_partner','','int');
		$database	= JFactory::getDBO();
		$filters = array();
		if(!empty($pageInfo->filter->filter_partner)){
			if($pageInfo->filter->filter_partner==1){
				$filters[]='a.user_partner_activated = 1';
				$query='CREATE OR REPLACE VIEW '.hikashop_table('click_view').' AS SELECT a.user_id, SUM(b.click_partner_price) AS click_price FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('click').' AS b ON a.user_id=b.click_partner_id AND (CASE WHEN a.user_currency_id=0 THEN '.hikashop_getCurrency().' ELSE a.user_currency_id END)=b.click_partner_currency_id WHERE a.user_partner_activated=1 AND b.click_partner_paid=0 GROUP BY b.click_partner_id;';
				$database->setQuery($query);
				$database->query();
				$config =& hikashop_config();
				$partner_valid_status_list=explode(',',$config->get('partner_valid_status','confirmed,shipped'));
				foreach($partner_valid_status_list as $k => $partner_valid_status){
					$partner_valid_status_list[$k]= $database->Quote($partner_valid_status);
				}
				$query='CREATE OR REPLACE VIEW '.hikashop_table('sale_view').' AS SELECT a.user_id, SUM(b.order_partner_price) AS sale_price FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('order').' AS b ON a.user_id=b.order_partner_id AND (CASE WHEN a.user_currency_id=0 THEN '.hikashop_getCurrency().' ELSE a.user_currency_id END)=b.order_partner_currency_id WHERE a.user_partner_activated=1 AND b.order_partner_paid=0 AND b.order_type=\'sale\' AND b.order_status IN ('.implode(',',$partner_valid_status_list).') GROUP BY b.order_partner_id;';
				$database->setQuery($query);
				$database->query();
				$query='CREATE OR REPLACE VIEW '.hikashop_table('lead_view').' AS SELECT a.user_id, SUM(b.user_partner_price) AS lead_price FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('user').' AS b ON a.user_id=b.user_partner_id AND (CASE WHEN a.user_currency_id=0 THEN '.hikashop_getCurrency().' ELSE a.user_currency_id END)=b.user_partner_currency_id WHERE a.user_partner_activated=1 AND b.user_partner_paid=0 GROUP BY b.user_partner_id;';
				$database->setQuery($query);
				$database->query();
				$database->setQuery('UPDATE '.hikashop_table('user').' SET user_unpaid_amount=0');
				$database->query();
				$query='UPDATE '.hikashop_table('user').' AS a JOIN '.hikashop_table('click_view').' AS b ON a.user_id=b.user_id SET a.user_unpaid_amount=b.click_price WHERE a.user_partner_activated=1';
				$database->setQuery($query);
				$database->query();
				$query='UPDATE '.hikashop_table('user').' AS a JOIN '.hikashop_table('sale_view').' AS b ON a.user_id=b.user_id SET a.user_unpaid_amount=a.user_unpaid_amount+b.sale_price WHERE a.user_partner_activated=1';
				$database->setQuery($query);
				$database->query();
				$query='UPDATE '.hikashop_table('user').' AS a JOIN '.hikashop_table('lead_view').' AS b ON a.user_id=b.user_id SET a.user_unpaid_amount=a.user_unpaid_amount+b.lead_price WHERE a.user_partner_activated=1';
				$database->setQuery($query);
				$database->query();
				$currencyClass = hikashop_get('class.currency');
				$this->assignRef('currencyHelper',$currencyClass);
			}else{
				$filters[]='a.user_partner_activated=0';
			}
		}
		$searchMap = array('a.user_id','a.user_email','b.username','b.email','b.name');
		foreach($fields as $field){
			$searchMap[]='a.'.$field->field_namekey;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users',false).' AS b ON a.user_cms_id=b.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$fieldsClass->handleZoneListing($fields,$rows);
		foreach($rows as $k => $row){
			if(!empty($row->user_params)){
				$rows[$k]->user_params = unserialize($row->user_params);
			}
		}
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'user_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_user_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'check' => JText::_('HIKA_VALIDDELETEITEMS'), 'display' => hikashop_isAllowed($config->get('acl_user_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$partner = hikashop_get('type.user_partner');
		$this->assignRef('partner',$partner);
		$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		if(empty($plugin)){
			$affiliate_active = false;
		}else{
			$affiliate_active = true;
		}
		$this->assignRef('affiliate_active',$affiliate_active);
	}

	function sales(){
		$this->paramBase.= '_sales';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'b.order_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database = JFactory::getDBO();
		$config =& hikashop_config();
		$partner_valid_status_list=explode(',',$config->get('partner_valid_status','confirmed,shipped'));
		foreach($partner_valid_status_list as $k => $partner_valid_status){
			$partner_valid_status_list[$k]= $database->Quote($partner_valid_status);
		}
		$filters = array('b.order_partner_id='.hikashop_getCID('user_id'),'b.order_partner_paid=0','b.order_status IN ('.implode(',',$partner_valid_status_list).')');

		$searchMap = array('c.id','c.username','c.name','a.user_email','b.order_user_id','b.order_id','b.order_full_price','b.order_number');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			$filters[] =  $filter;
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('order').' AS b LEFT JOIN '.hikashop_table('user').' AS a ON b.order_user_id=a.user_id LEFT JOIN '.hikashop_table('users',false).' AS c ON a.user_cms_id=c.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*,c.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'order_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
	}

	function clicks(){

		$this->paramBase.='_clicks';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.click_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();

		$filters = array('a.click_partner_paid=0');

		$user_id = hikashop_getCID('user_id');
		if(!empty($user_id)){
			$filters[] = 'a.click_partner_id='.$user_id;
		}
		$this->assignRef('user_id',$user_id);

		$searchMap = array('a.click_ip','a.click_referer','a.click_partner_id','a.click_id','b.user_email');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$query = ' FROM '.hikashop_table('click').' AS a';
		$query .= ' JOIN '.hikashop_table('user').' AS b ON a.click_partner_id = b.user_id';
		if(!empty($filters)) $query .= ' WHERE '. implode(' AND ',$filters);


		$database->setQuery('SELECT a.*, b.user_email, b.user_currency_id '.$query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'click_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);

		hikashop_setTitle(JText::_('CLICKS'),'click',$this->ctrl.'&task=clicks&user_id='.$user_id);
	}

	function leads(){
		$this->paramBase='leads';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','user',false);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$user_id = hikashop_getCID('user_id');
		$userClass = hikashop_get('class.user');
		$user = $userClass->get($user_id);
		$this->assignRef('user',$user);
		$filters = array('a.user_partner_id='.$user_id,'a.user_partner_paid=0');

		$searchMap = array('a.user_id','a.user_email','b.username','b.email','b.name');
		foreach($fields as $field){
			$searchMap[]='a.'.$field->field_namekey;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users',false).' AS b ON a.user_cms_id=b.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$fieldsClass->handleZoneListing($fields,$rows);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'user_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
	}

	function pay(){
		$user=null;
		$user_id = hikashop_getCID('user_id');
		if(!empty($user_id)){
			$class = hikashop_get('class.user');
			$user = $class->get($user_id);
			if(!empty($user)) $class->loadPartnerData($user);
		}
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		$this->assignRef('user',$user);
		$method = 'paypal';
		$this->assignRef('method',$method);
	}

	function form(){
		$user_id = hikashop_getCID('user_id');
		$fieldsClass = hikashop_get('class.field');
		$addresses = array();
		$fields = null;
		$rows = array();
		$affiliates = array();
		if(!empty($user_id)){
			$class = hikashop_get('class.user');
			$user = $class->get($user_id,'hikashop',true);
			if(!empty($user)) $class->loadPartnerData($user);
			$fields['user'] = $fieldsClass->getFields('backend',$user);

			$addressClass = hikashop_get('class.address');
			$addresses = $addressClass->getByUser($user_id);
			if(!empty($addresses)){
				$addressClass->loadZone($addresses,'name','backend');
				$fields['address'] =& $addressClass->fields;
			}

			$db = JFactory::getDBO();
			$filters = array(
				'order_user_id='.$user_id
			);
			$query = 'SELECT * FROM '.hikashop_table('order').' WHERE order_type='.$db->Quote('sale').' AND ('.implode(' OR ',$filters).') ORDER BY order_id DESC';
			$db->setQuery($query);
			$orders = $db->loadObjectList();
			foreach($orders as $order){
				if($order->order_user_id==$user_id){
					$rows[]=$order;
				}
			}
			$task='edit';
		}else{
			$user = new stdClass();
			$task='add';
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('affiliates',$affiliates);
		$this->assignRef('user',$user);
		$this->assignRef('fields',$fields);
		$this->assignRef('addresses',$addresses);
		$this->assignRef('fieldsClass',$fieldsClass);
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		$currencyType = hikashop_get('type.currency');
		$this->assignRef('currencyType',$currencyType);
		$pluginClass = hikashop_get('class.plugins');
		$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		if(empty($plugin)){
			$affiliate_active = false;
		}else{
			$affiliate_active = true;
		}
		$this->assignRef('affiliate_active',$affiliate_active);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);
		if(version_compare(JVERSION,'1.6','<')){
			$url_link = JRoute::_('index.php?option=com_users&task=edit&cid[]='.$user->user_cms_id );
			$email_link = hikashop_completeLink('order&task=mail&user_id='.$user_id,true);
		}else{
			$url_link = JRoute::_('index.php?option=com_users&task=user.edit&id='.$user->user_cms_id );
			$email_link = 'index.php?option=com_hikashop&ctrl=order&task=mail&tmpl=component&user_id='.$user_id;
		}
		$this->toolbar = array(
			array('name' => 'link', 'icon' => 'upload', 'alt' => JText::_('JOOMLA_USER_OPTIONS'), 'url' => $url_link,'display'=>!empty($user->user_cms_id)),
			array('name' => 'popup', 'icon' => 'send', 'alt' => JText::_('HIKA_EMAIL'), 'url' => $email_link,'display'=>!empty($user_id)),
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$js ='
		function updateCustomFeesPanel(active){
			if(active==1){
				var displayFee = \'\';
			}else{
				var displayFee = \'none\';
			}
			document.getElementById(\'custom_fees_panel\').style.display=displayFee;
		}';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$order_info='';
		$order_id = JRequest::getInt('order_id',0);
		if(!empty($order_id)){
			$order_info='&order_id='.$order_id;
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&user_id='.$user_id.$order_info);
	}

	function editaddress(){
		$user_id = JRequest::getInt('user_id');
		$address_id = hikashop_getCID('address_id');
		$address = new stdClass();
		if(!empty($address_id)){
			$class=hikashop_get('class.address');
			$address = $class->get($address_id);
		}
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;
		$extraFields['address'] = $fieldsClass->getFields('backend',$address,'address','user&task=state');
		$this->assignRef('extraFields',$extraFields);
		$this->assignRef('user_id',$user_id);
		$this->assignRef('address',$address);
		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['address'],$address,0);
		$requiredFields = array();
		$validMessages = array();
		$values = array('address'=>$address);
		$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
		$fieldsClass->addJS($requiredFields,$validMessages,array('address'));
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		jimport('joomla.html.parameter');
		$params = new HikaParameter('');
		$this->assignRef('params',$params);
	}


	function state(){
		$namekey = JRequest::getCmd('namekey','');
		if(!empty($namekey)){

			$field_namekey = JRequest::getString('field_namekey', '');
			if(empty($field_namekey))
				$field_namekey = 'address_state';

			$field_id = JRequest::getString('field_id', '');
			if(empty($field_id))
				$field_id = 'address_state';

			$field_type = JRequest::getString('field_type', '');
			if(empty($field_type))
				$field_type = 'address';

			$class = hikashop_get('type.country');
			echo $class->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type);

		}
		exit;
	}

}
