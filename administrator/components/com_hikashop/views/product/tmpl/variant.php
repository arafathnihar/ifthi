<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.1.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<table width="100%">
	<tr>
		<td style="vertical-align:top;">
			<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=product" method="post"  name="adminForm" id="adminForm">
				<table class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'HIKA_NUM' );?>
							</th>
							<th class="title titlebox">
								<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
							</th>
							<th class="title">
								<?php echo JText::_('PRODUCT_CODE'); ?>
							</th>
							<?php if(!empty($this->characteristics)){
								foreach($this->characteristics as $characteristic){
									echo '<th class="title">'.$characteristic->characteristic_value.'</th>';
								}
							}?>
							<th class="title">
								<?php echo JText::_('PRODUCT_PRICE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('PRODUCT_QUANTITY'); ?>
							</th>
							<th class="title titletoggle">
								<?php echo JText::_('HIKA_PUBLISHED'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'ID' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php

							if(!empty($this->rows)){
								$k = 0;
								for($i = 0,$a = count($this->rows);$i<$a;$i++){
									$row =& $this->rows[$i];
									$publishedid = 'product_published-'.$row->product_id;
								?>
								<tr class="<?php echo "row$k"; ?>">
									<td align="center">
									<?php echo $i; ?>
									</td>
									<td align="center">
										<?php echo JHTML::_('grid.id', $i, $row->product_id ); ?>
									</td>
									<td>
										<a href="<?php echo hikashop_completeLink('product&task=edit&variant=1&cid[]='.$row->product_id); ?>">
											<?php echo $row->product_code; ?>
										</a>
									</td>
									<?php if(!empty($this->characteristics)){
										foreach($this->characteristics as $characteristic){
											echo '<td>'.@$row->characteristics[$characteristic->characteristic_value].'</td>';
										}
									}?>
									<td>
										<?php echo $this->currencyHelper->displayPrices(@$row->prices); ?>
									</td>
									<td>
										<?php echo ($row->product_quantity==-1?JText::_('UNLIMITED'):$row->product_quantity); ?>
									</td>
									<td align="center">
										<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->product_published,'product') ?></span>
									</td>
									<td width="1%" align="center">
										<?php echo $row->product_id; ?>
									</td>
								</tr>
								<?php
									$k = 1-$k;
								}
							}
						?>
					</tbody>
				</table>
				<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="task" value="variant" />
				<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" id="parent_id" name="parent_id" value="<?php echo $this->product_id; ?>" />
				<input type="hidden" id="variant" name="variant" value="1" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
		</td>
	</tr>
</table>
