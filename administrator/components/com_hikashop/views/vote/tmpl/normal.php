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
	$row =& $this->rows[0];
	$item =& $this->item;
	$newItem = $item->newItem;
	if($newItem == true){
		$row->vote_ip = '';
		$this->element->vote_type = 'product';
$this->item->enabled = '3';
$row->vote_rating = '';
$row->vote_comment = ' ';
$row->vote_date = time();
	}
?>
					<table class="admintable table"  width="100%">
						<tr>
							<td class="key">
								<label for="data[vote][vote_ref_id]">
									<?php echo JText::_( 'HIKASHOP_ITEM' ); ?>
								</label>
							</td>
							<td>
								<?php if($newItem != true){echo $row->product_name;}
										else{?><input type='text' size='100' name='<?php echo $this->vote_ref_id_input; ?>' value=''><?php echo JText::_( 'VOTE_ENTER_ITEM_ID' );}?>
							</td>
						</tr>
						<?php if($newItem == true){ ?>
						<tr>
							<td class="key">
								<label for="data[vote][vote_type]">
									<?php echo JText::_( 'HIKA_TYPE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->vote_type_input; ?>" value="<?php echo $this->element->vote_type; ?>"/>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="key">
								<label for="data[vote][vote_pseudo]">
									<?php echo JText::_( 'HIKA_USERNAME' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->vote_pseudo_input; ?>"
								value="<?php if($newItem == true){echo "\"";}else if($this->element->vote_pseudo == '0'){echo $row->username."\" disabled=\"disabled\"";}else{echo $this->escape(@$this->element->vote_pseudo);} ?>" />

							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[vote][vote_ip]">
									<?php echo JText::_( 'HIKA_IP' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $row->vote_ip; ?>" value="<?php if($newItem == true){echo "\"";}else{ echo $row->vote_ip."\" disabled=\"disabled\"";} ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[vote][vote_email]">
									<?php echo JText::_( 'HIKA_EMAIL' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->vote_email_input; ?>"
								value="<?php if($newItem == true){echo "\"";} else if($this->element->vote_pseudo == '0'){echo $row->email."\" disabled=\"disabled\"";}elseif($this->element->vote_email != '0'){echo $this->escape(@$this->element->vote_email)."\"";}else{echo "";}?> "/>

							</td>
						</tr>
						<?php if(($this->item->enabled == 1 || $this->item->enabled == 3) && $row->vote_rating != '0'){ ?>
						<tr>
							<td class="key">
								<label for="data[vote][vote_rating]">
									<?php echo JText::_( 'VOTE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $this->vote_rating_input; ?>"
								value="<?php echo $this->escape(@$this->element->vote_rating);?>" />

							</td>
						</tr>
						<?php }
							if(($this->item->enabled == 2 || $this->item->enabled == 3) && $row->vote_comment != ''){
						?>
						<tr>
							<td class="key">
								<label for="data[vote][vote_comment]">
									<?php echo JText::_( 'COMMENT' ); ?>
								</label>
							</td>
							<td>
								<textarea cols="71" name="<?php echo $this->vote_comment_input; ?>" ><?php echo $this->escape(@$this->element->vote_comment); ?></textarea>

							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="key">
								<label for="data[vote][vote_date]">
									<?php echo JText::_( 'DATE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" size="100" name="<?php echo $row->vote_date; ?>" value="<?php echo date('d/m/Y h:m:s', $row->vote_date); ?>" disabled="disabled"/>
							</td>
						</tr>
					</table>
