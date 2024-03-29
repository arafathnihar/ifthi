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
$row = & $this->row;
$type_item = $row->type_item;
$hikashop_vote_nb_star = $row->hikashop_vote_nb_star;
$email_comment = $row->email_comment;
$comment_enabled = $row->comment_enabled;
$vote_enabled = $row->vote_enabled;
$hikashop_vote_average_score = $row->hikashop_vote_average_score;
$hikashop_vote_average_score_rounded = $row->hikashop_vote_average_score_rounded;
$hikashop_vote_total_vote = $row->hikashop_vote_total_vote;

$purchased = $row->purchased;
$access_vote = $row->access_vote;
$hikashop_vote_user_id = hikashop_loadUser();

if(($access_vote == 'registered' && empty($hikashop_vote_user_id)) || ($access_vote == 'buyed' && $purchased == 0)){
	$hide = 1;
}

if ($comment_enabled == 1 && empty($hide)) {

	$hikashop_vote_ref_id = $row->vote_ref_id;
	$hikashop_vote_user_id = hikashop_loadUser();

?>
	<div id="reload" class="hikashop_vote_form">
	<input type="hidden" id="hikashop_vote_ref_id" value="<?php echo $hikashop_vote_ref_id;?>"/>
	<input type="hidden" id="vote_type" value="<?php echo $type_item; ?>"/>
	<input type="hidden" id="hikashop_vote_user_id" value="<?php echo $hikashop_vote_user_id;?>"/>
	<input type="hidden" id="hikashop_vote_ok" value="0"/>
<?php
	if ($vote_enabled == 1) {
		echo '<div class="hikashop_vote_stars">';
		echo JText::_('VOTE').": ";
		echo '<select name="hikashop_vote_rating" id="hikashop_vote_rating_id">';
		for ($i = 1; $i <= $hikashop_vote_nb_star; $i++) {
			echo '<option value="' . $i . '">' . $i . '</option>';
		}
?>
		</select>
		<script type='text/javascript'>
			window.addEvent('domready', function() {
				var rating = new hikashop_ratings(document.getElementById('hikashop_vote_rating_id'), {
					id : 'hikashop_vote_rating_<?php echo $type_item; ?>_<?php echo $hikashop_vote_ref_id;?>_',
					showSelectBox : false,
					container : null,
					defaultRating : <?php echo $hikashop_vote_average_score_rounded; ?>
				});
			});
		</script>
	<span class="hikashop_total_vote" >(<?php echo JHTML::tooltip($hikashop_vote_average_score.'/'.$hikashop_vote_nb_star, JText::_('VOTE_AVERAGE'), '', ' '.$hikashop_vote_total_vote.' '); ?>)</span>
	</div>
	<div class="clear_both"></div>

<?php
	}
?>
	<div id='hikashop_vote_status_form' class="hikashop_vote_notification" ></div>
	<br/>
	<p class="hikashop_form_comment ui-corner-top"><?php echo JText::_('HIKASHOP_LET_A_COMMENT'); ?></p>
	<?php
	if (hikashop_loadUser() == "") { ?>
		<table class="hikashop_comment_form">
			<tr class="hikashop_comment_form_name">
				<td>
					<?php echo JText::_('HIKA_USERNAME').":"; ?>
				</td>
				<td>
					<input  type='text' id='pseudo_comment' />
				</td>
			</tr>
		<?php
		if ($email_comment == 1) {
		?>
			<tr class="hikashop_comment_form_mail">
				<td>
					<?php echo JText::_('HIKA_EMAIL').":"; ?>
				</td>
				<td>
					<input  type='text' id='email_comment' value=''/>
				</td>
			</tr>
		<?php
		} else {
		?>
			<input type='hidden' id='email_comment' value='0'/>
		<?php
		}
		?>
			</table>
		<?php
	} else {
		?>
		<input type='hidden' id='pseudo_comment' value='0'/>
		<input type='hidden' id='email_comment' value='0'/>
	<?php
	}
	?>
		<textarea type="text" id="hikashop_vote_comment" class="hikashop_comment_textarea" onfocus="clearTextArea(this);" ><?php echo JText::_('HIKASHOP_POST_COMMENT');?></textarea>
		<input class="button" type="button" value="<?php echo JText::_('HIKASHOP_SEND_COMMENT'); ?>" onClick="hikashop_send_comment();"/>
	</div>

<?php
}
?>
<script type='text/javascript'>
	var clickedIt = false;
	function clearTextArea(id){
		if (clickedIt == false){
			id.value="";
			clickedIt=true;
		}
	}
</script>
