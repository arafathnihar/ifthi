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
class hikashopLayoutType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'div',JText::_('DIV') );
		$this->values[] = JHTML::_('select.option', 'table',JText::_('TABLE'));
		$this->values[] = JHTML::_('select.option', 'list',JText::_('LIST'));
		if(JRequest::getCmd('from_display',false) == false)
			$this->values[] = JHTML::_('select.option', 'inherit',JText::_('HIKA_INHERIT'));
	}
	function display($map,$value,&$js,$update=true){
		$this->load();
		$options = '';
		if($update){
			$options = 'var options = [\'div\', \'table\', \'list\'];';
			$js .=$options.'switchPanel(\''.$value.'\',options,\'layout\');';
			$options = 'onchange="'.$options.'return switchPanel(this.value,options,\'layout\');"';
		}

		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value,'layout_select' );
	}
}
