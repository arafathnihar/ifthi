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


if (!empty ($this->element->characteristics)) {
?><div id="hikashop_product_characteristics" class="hikashop_product_characteristics"><?php
	echo $this->characteristic->displayFE($this->element, $this->params);
?></div><?php
}
?>
