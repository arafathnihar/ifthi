<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.1.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_check_end" id="hikashop_check_end">
	<span class="hikashop_check_end_message" id="hikashop_check_end_message">
		<?php echo JText::_('ORDER_IS_COMPLETE').'<br/>'.
		JText::sprintf('PLEASE_SEND_CHECK',$amount).'<br/>'.
		$information.'<br/>'.
		JText::sprintf('INCLUDE_ORDER_NUMBER_TO_CHECK',$order_number).'<br/>'.
		JText::_('THANK_YOU_FOR_PURCHASE');?>
	</span>
</div>
<?php
if(!empty($return_url)){
	$doc =& JFactory::getDocument();
	$doc->addScriptDeclaration("window.addEvent('domready', function() {window.location='".$return_url."'});");
}
