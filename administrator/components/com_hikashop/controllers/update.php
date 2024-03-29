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
class updateController extends HikashopBridgeController {
	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('update');
	}

	function install(){
		hikashop_setTitle('HikaShop','install','update');
		$newConfig = new stdClass();
		$newConfig->installcomplete = 1;
		$config = hikashop_config();
		$config->save($newConfig);
		$updateHelper = hikashop_get('helper.update');
		$updateHelper->addJoomfishElements();
		$updateHelper->addDefaultData();
		$updateHelper->createUploadFolders();
		$updateHelper->installMenu();
		$updateHelper->addUpdateSite();
		$updateHelper->installExtensions();
		if (!HIKASHOP_PHP5) {
			$bar =& JToolBar::getInstance('toolbar');
		}else{
			$bar = JToolBar::getInstance('toolbar');
		}
		$bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$this->_iframe(HIKASHOP_UPDATEURL.'install');
	}

	function update(){
		hikashop_setTitle(JText::_('UPDATE_ABOUT'),'install','update');
		if (!HIKASHOP_PHP5) {
			$bar =& JToolBar::getInstance('toolbar');
		}else{
			$bar = JToolBar::getInstance('toolbar');
		}
		$bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		return $this->_iframe(HIKASHOP_UPDATEURL.'update');
	}
	function _iframe($url){
		$config =& hikashop_config();
		$menu_style = $config->get('menu_style','title_bottom');
		if($menu_style=='content_top'){
			echo hikashop_getMenu('',$menu_style);
		}
?>
		<div id="hikashop_div">
			<iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="hikashop_frame" id="hikashop_frame" src="<?php echo $url.'&level='.$config->get('level').'&component=hikashop&version='.$config->get('version'); ?>"></iframe>
		</div>
<?php
	}

}
