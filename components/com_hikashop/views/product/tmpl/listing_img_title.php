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
$link = hikashop_completeLink('product&task=show&cid='.$this->row->product_id.'&name='.$this->row->alias.$this->itemid.$this->category_pathway);

if(!empty($this->row->extraData->top)) { echo implode("\r\n",$this->row->extraData->top); }

if($this->config->get('thumbnail',1)){ ?>
<!-- PRODUCT IMG -->
<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;text-align:center;clear:both;" class="hikashop_product_image">
	<div style="position:relative;text-align:center;clear:both;width:<?php echo $this->image->main_thumbnail_x;?>px;margin: auto;" class="hikashop_product_image_subdiv">
		<?php if($this->params->get('link_to_product_page',1)){ ?>
			<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->product_name); ?>">
		<?php }
			echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->file_name), '' , '' , $this->image->main_thumbnail_x,  $this->image->main_thumbnail_y);
			$main_thumb_x = $this->image->main_thumbnail_x;
			$main_thumb_y = $this->image->main_thumbnail_y;
			if($this->params->get('display_badges',1)){
				$this->classbadge->placeBadges($this->image, $this->row->badges, -10, 0);
			}
			$this->image->main_thumbnail_x = $main_thumb_x;
			$this->image->main_thumbnail_y = $main_thumb_y;

		if($this->params->get('link_to_product_page',1)){ ?>
			</a>
		<?php } ?>
	</div>
</div>
<!-- EO PRODUCT IMG -->

<!-- PRODUCT PRICE -->
<?php
}
if($this->params->get('show_price','-1')=='-1'){
	$config =& hikashop_config();
	$this->params->set('show_price',$config->get('show_price'));
}
if($this->params->get('show_price')){
	$this->setLayout('listing_price');
	echo $this->loadTemplate();
}

?>
<!-- EO PRODUCT PRICE -->

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

<?php if(!empty($this->row->extraData->afterProductName)) { echo implode("\r\n",$this->row->extraData->afterProductName); } ?>

<!-- PRODUCT VOTE -->
<?php
if($this->params->get('show_vote_product')){
	$this->setLayout('listing_vote');
	echo $this->loadTemplate();
}
?>
<!-- EO PRODUCT VOTE -->

<!-- ADD TO CART BUTTON AREA -->
<?php
if($this->params->get('add_to_cart') || $this->params->get('add_to_wishlist')){
	$this->setLayout('add_to_cart_listing');
	echo $this->loadTemplate();
}?>
<!-- EO ADD TO CART BUTTON AREA -->

<!-- COMPARISON AREA -->
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
<!-- EO COMPARISON AREA -->
<?php if(!empty($this->row->extraData->bottom)) { echo implode("\r\n",$this->row->extraData->bottom); } ?>
