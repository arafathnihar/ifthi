<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.1.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_westernunion_end" id="hikashop_westernunion_end">
	<span class="hikashop_westernunion_end_message" id="hikashop_westernunion_end_message">
		<?php echo JText::_('ORDER_IS_COMPLETE').'<br/>'. 
		JText::sprintf('PLEASE_TRANSFERT_MONEY',$amount).'<br/>'. 
		$information.'<br/>'. 
		JText::sprintf('INCLUDE_ORDER_NUMBER_TO_TRANSFER',$order_number).'<br/>'. 
		JText::_('THANK_YOU_FOR_PURCHASE');?>
	</span>
</div>
