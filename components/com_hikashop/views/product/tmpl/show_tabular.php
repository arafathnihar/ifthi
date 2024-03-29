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
	hikashop_loadJslib('jquery');
	$js = '';
	$params = null;
	$this->params->set('vote_type','product');
	if(isset($this->element->main)){
		$product_id = $this->element->main->product_id;
	}else{
		$product_id = $this->element->product_id;
	}
	$this->params->set('vote_ref_id',$product_id);
	$this->params->set('productlayout','show_tabular');
	$layout_vote_mini = hikashop_getLayout('vote', 'mini', $this->params, $js);
	$layout_vote_listing = hikashop_getLayout('vote', 'listing', $this->params, $js);
	$layout_vote_form = hikashop_getLayout('vote', 'form', $this->params, $js);
	$config =& hikashop_config();
	$status_vote = $config->get('enable_status_vote');
	$hide_specs = 1;
	if($this->element->product_weight != 0 || $this->element->product_width != 0 || $this->element->product_height != 0 || $this->element->product_length != 0)
		$hide_specs = 0;
	foreach ($this->fields as $fieldName => $oneExtraField) {
	$value = '';
	if(empty($this->element->$fieldName) && !empty($this->element->main->$fieldName))$this->element->$fieldName = $this->element->main->$fieldName;
	if(isset($this->element->$fieldName))
		$value = trim($this->element->$fieldName);
		if(!empty($value))
			$hide_specs = 0;
	}
?>
<div id="hikashop_product_top_part" class="hikashop_product_top_part">
<?php if(!empty($this->element->extraData->topBegin)) { echo implode("\r\n",$this->element->extraData->topBegin); } ?>
	<h1>
		<span id="hikashop_product_name_main" class="hikashop_product_name_main">
			<?php
			if (hikashop_getCID('product_id')!=$this->element->product_id && isset ($this->element->main->product_name))
				echo $this->element->main->product_name;
			else
				echo $this->element->product_name;
			?>
		</span>
		<?php if ($this->config->get('show_code')) { ?>
		<span id="hikashop_product_code_main" class="hikashop_product_code_main">
			<?php
			echo $this->element->product_code;
			?>
		</span>
		<?php } ?>
	</h1>
<?php if(!empty($this->element->extraData->topEnd)) { echo implode("\r\n",$this->element->extraData->topEnd); } ?>
	<?php
	$pluginsClass = hikashop_get('class.plugins');
	$plugin = $pluginsClass->getByName('content', 'hikashopsocial');
	if (@ $plugin->published || @ $plugin->enabled) {
		echo '{hikashop_social}';
	}
	?>
</div>
<?php if(HIKASHOP_RESPONSIVE){ ?>
	<div class="row-fluid">
<?php } ?>
<div id="hikashop_product_left_part" class="hikashop_product_left_part" style="width: 500px; ">
	<?php
	if(!empty($this->element->extraData->leftBegin)) { echo implode("\r\n",$this->element->extraData->leftBegin); }

	$this->row = & $this->element;
	$this->setLayout('show_block_img');
	echo $this->loadTemplate();
	?>
	<div id="hikashop_product_description_main_mini" class="hikashop_product_description_main_mini">
		<?php
		$resume = substr(strip_tags(preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->element->product_description)),0,300);
		$resume .= "...<a href='#description'>".JText::_('READ_MORE')."</a>";
		echo JHTML::_('content.prepare',$resume);
		?>
	</div>
	<?php if(!empty($this->element->extraData->leftEnd)) { echo implode("\r\n",$this->element->extraData->leftEnd); } ?>
</div>
<div id="hikashop_product_right_part" class="hikashop_product_right_part">
	<?php
	if(!empty($this->element->extraData->rightBegin))
		echo implode("\r\n",$this->element->extraData->rightBegin);
	?>
	<span id="hikashop_product_price_main" class="hikashop_product_price_main">
		<?php
		if ($this->params->get('show_price')) {
			$this->row = & $this->element;
			$this->setLayout('listing_price');
			echo $this->loadTemplate();
		}
		?>
	</span><br />
	<div id="hikashop_product_vote_mini" class="hikashop_product_vote_mini">
		<?php
		if($this->params->get('show_vote_product') == '-1'){
			$this->params->set('show_vote_product',$config->get('show_vote_product'));
		}
		if($this->params->get('show_vote_product')){
			echo $layout_vote_mini;
		}
		?>
	</div>
	<?php
	$this->setLayout('show_block_characteristic');
	echo $this->loadTemplate();
	?>
	<br />
	<?php
	$form = ',0';
	if (!$this->config->get('ajax_add_to_cart', 1)) {
		$form = ',\'hikashop_product_form\'';
	}
	if (hikashop_level(1) && !empty ($this->element->options)) {
	?>
		<div id="hikashop_product_options" class="hikashop_product_options">
			<?php
			$this->setLayout('option');
			echo $this->loadTemplate();
			?>
		</div>
		<br />
		<?php
		$form = ',\'hikashop_product_form\'';
		if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
		?>
			<input type="hidden" name="popup" value="1"/>
		<?php
		}
	}
	if (!$this->params->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty ($this->element->prices))) {
		if (!empty ($this->itemFields)) {
			$form = ',\'hikashop_product_form\'';
			if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
			?>
				<input type="hidden" name="popup" value="1"/>
			<?php
			}
			$this->setLayout('show_block_custom_item');
			echo $this->loadTemplate();
		}
	}
	$this->formName = $form;
	if($this->params->get('show_price')){ ?>
		<span id="hikashop_product_price_with_options_main" class="hikashop_product_price_with_options_main">
		</span>
	<?php } ?>
	<?php $contact = $this->config->get('product_contact',0); ?>
	<div id="hikashop_product_contact_main" class="hikashop_product_contact_main">
		<?php
		if (hikashop_level(1) && ($contact == 2 || ($contact == 1 && !empty ($this->element->product_contact)))) {
			$empty = '';
			$params = new HikaParameter($empty);
			global $Itemid;
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
			echo $this->cart->displayButton(JText :: _('CONTACT_US_FOR_INFO'), 'contact_us', $params, hikashop_completeLink('product&task=contact&cid=' . $this->row->product_id.$url_itemid), 'window.location=\'' . hikashop_completeLink('product&task=contact&cid=' . $this->row->product_id.$url_itemid) . '\';return false;');
		}
		?>
	</div>
	<?php
	if(!empty($this->element->extraData->rightMiddle))
		echo implode("\r\n",$this->element->extraData->rightMiddle);
	?>

	<span id="hikashop_product_id_main" class="hikashop_product_id_main">
		<input type="hidden" name="product_id" value="<?php echo $this->element->product_id; ?>" />
	</span>
	<br />
	<div id="hikashop_product_quantity_main" class="hikashop_product_quantity_main">
		<?php
		$this->row = & $this->element;
		$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form\')){ return hikashopModifyQuantity(\'' . $this->row->product_id . '\',field,1' . $form . ',\'cart\'); } else { return false; }';
		$this->setLayout('quantity');
		echo $this->loadTemplate();
		?>
	</div>
	<?php
	$this->setLayout('show_block_product_files');
	echo $this->loadTemplate();
	?>
	<?php
	if(!empty($this->element->extraData->rightEnd))
		echo implode("\r\n",$this->element->extraData->rightEnd);
	?>
</div>
<?php if(HIKASHOP_RESPONSIVE){ ?>
	</div>
<?php } ?>
<div id="hikashop_product_bottom_part" class="hikashop_product_bottom_part show_tabular">
	<div id="hikashop_tabs">
		<ul>
			<li><a href="#description"><?php echo JText::_('PRODUCT_DESCRIPTION');?></a></li>
			<?php if($hide_specs == 0){ ?>
			<li><a href="#specification"><?php echo JText::_('SPECIFICATIONS');?></a></li>
			<?php }
			if($status_vote == "comment" || $status_vote == "two" || $status_vote == "both" ){
			?>
			<li><a href="#comment"><?php echo JText::_('PRODUCT_COMMENT');?></a></li>
			<li><a href="#new_comment"><?php echo JText::_('PRODUCT_NEW_COMMENT');?></a></li>
			<?php } ?>
		</ul>
		<?php
		if(!empty($this->element->extraData->bottomBegin))
			echo implode("\r\n",$this->element->extraData->bottomBegin);
		?>
		<div id="description">
			<div id="hikashop_product_description_main" class="hikashop_product_description_main">
				<?php
				echo JHTML::_('content.prepare',preg_replace('#<hr *id="system-readmore" */>#i','',$this->element->product_description));
				?>
			</div>
			<span id="hikashop_product_url_main" class="hikashop_product_url_main">
				<?php
				if (!empty ($this->element->product_url)) {
					echo JText :: sprintf('MANUFACTURER_URL', '<a href="' . $this->element->product_url . '" target="_blank">' . $this->element->product_url . '</a>');
				}
				?>
			</span>
		</div>
		<?php if($hide_specs == 0){ ?>
		<div id="specification">
		<?php
			$this->setLayout('show_block_dimensions');
			echo $this->loadTemplate();
			if(!empty($this->fields)){
				$this->setLayout('show_block_custom_main');
				echo $this->loadTemplate();
			}
		?>
		</div>
		<?php } ?>
	<input type="hidden" name="cart_type" id="type" value="cart"/>
	<input type="hidden" name="add" value="1"/>
	<input type="hidden" name="ctrl" value="product"/>
	<input type="hidden" name="task" value="updatecart"/>
	<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
</form>
<?php
if($status_vote == "comment" || $status_vote == "two" || $status_vote == "both" ){ ?>
<form action="<?php echo hikashop_currentURL() ?>" method="post" name="hikashop_comment_form">
		<?php
		if(!empty($this->element->extraData->bottomMiddle))
			echo implode("\r\n",$this->element->extraData->bottomMiddle);
		?>
			<div id="comment">
				<div id="hikashop_product_vote_listing" class="hikashop_product_vote_listing">
					<?php
						echo $layout_vote_listing;
					?>
				</div>
			</div>
			<div id="new_comment">
				<div id="hikashop_product_vote_form" class="hikashop_product_vote_form">
					<?php
						echo $layout_vote_form;
					?>
				</div>
			</div>
</form>
<?php } ?>
		<?php
		if(!empty($this->element->extraData->bottomEnd))
			echo implode("\r\n",$this->element->extraData->bottomEnd);
		?>
	</div>
</div>
<script>
(function($) {
		jQuery( "#hikashop_tabs" ).tabs();
})(jQuery);
</script>
