<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.1.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_postfinance_thankyou" id="hikashop_postfinance_thankyou">
	<span id="hikashop_postfinance_thankyou_message" class="hikashop_postfinance_thankyou_message">
		<?php echo JText::_('THANK_YOU_FOR_PURCHASE');
		if(!empty($return_url)){
			echo '<br/><a href="'.$return_url.'">'.JText::_('GO_BACK_TO_SHOP').'</a>';
		}?>
	</span>
</div>
<?php 
if(!empty($return_url)){
	$doc =& JFactory::getDocument();
	$doc->addScriptDeclaration("window.addEvent('domready', function() {window.location='".$return_url."'});");
}
