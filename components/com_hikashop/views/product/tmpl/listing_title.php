<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.1.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $link = hikashop_completeLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway);?>
<!-- PRODUCT NAME -->
<span class="hikashop_product_name">
	<?php if($this->params->get('link_to_product_page',1)){ ?>
		<a href="<?php echo $link;?>">
	<?php }
		echo $this->row->product_name;
	if($this->params->get('link_to_product_page',1)){ ?>
	</a>
	<?php } ?>
</span>
<!-- EO PRODUCT NAME -->
<!-- PRODUCT CODE -->
<span class='hikashop_product_code_list'>
	<?php if ($this->config->get('show_code')) { ?>
		<a href="<?php echo $link;?>">
		<?php
		echo $this->row->product_code;
		?>
		</a>
	<?php } ?>
</span>
<!-- EO PRODUCT CODE -->
<!-- PRODUCT PRICE -->
<?php

if($this->params->get('show_price')){
	$this->setLayout('listing_price');
	echo $this->loadTemplate();
} ?>
<!-- EO PRODUCT PRICE -->
<!-- ADD TO CART BUTTON AREA -->
<?php

if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')){
	$this->setLayout('add_to_cart_listing');
	echo $this->loadTemplate();
} ?>
<!-- EO ADD TO CART BUTTON AREA -->
<?php
if(JRequest::getVar('hikashop_front_end_main',0) && JRequest::getVar('task')=='listing' && $this->params->get('show_compare')) { ?>
	<br/><?php
	if( $this->params->get('show_compare') == 1 ) {
?>
	<a class="hikashop_compare_button" href="<?php echo $link;?>" onclick="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this); return false;"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></a>
<?php } else { ?>
	<input type="checkbox" class="hikashop_compare_checkbox" id="hikashop_listing_chk_<?php echo $this->row->product_id;?>" onchange="setToCompareList(<?php echo $this->row->product_id;?>,'<?php echo $this->escape($this->row->product_name); ?>',this);"><label for="hikashop_listing_chk_<?php echo $this->row->product_id;?>"><?php echo JText::_('ADD_TO_COMPARE_LIST'); ?></label>
<?php }
} ?>
