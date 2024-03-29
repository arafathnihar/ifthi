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

class ImportViewImport extends hikashopView{
	var $ctrl= 'import';
	var $icon = 'import';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function show(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$config =& hikashop_config();

		hikashop_setTitle(JText::_('IMPORT'),$this->icon,$this->ctrl.'&task=show');
		$importIcon = 'upload';
		if(HIKASHOP_J30) {
			$importIcon = 'import';
		}
		$this->toolbar = array(
			array('name' => 'custom', 'icon' => $importIcon, 'alt' => JText::_('IMPORT'), 'task' => 'import', 'check' => false),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl),
			'dashboard'
		);

		$importData = array();
		$import = new stdClass();
		$import->text = JText::_('PRODUCTS_FROM_CSV');
		$import->key = 'file';
		$importData[] = $import;
		$textArea = new stdClass();
		$textArea->text = JText::_('PRODUCTS_FROM_TEXTAREA');
		$textArea->key = 'textarea';
		$importData[] = $textArea;

		$folder = new stdClass();
		$folder->text = JText::_('PRODUCTS_FROM_FOLDER');
		$folder->key = 'folder';
		$importData[] = $folder;

		$database = JFactory::getDBO();
		$query='SHOW TABLES LIKE '.$database->Quote($database->getPrefix().substr(hikashop_table('vm_product',false),3));
		$database->setQuery($query);
		$table = $database->loadResult();
		if(!empty($table)){
			$vm_here = true;
		}else{
			$vm_here = false;
		}
		$this->assignRef('vm',$vm_here);
		$vm = new stdClass();
		$vm->text = JText::_('PRODUCTS_FROM_VM');
		$vm->key = 'vm';
		$importData[] = $vm;

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onDisplayImport', array( & $importData) );

		$this->assignRef('importData',$importData);
		$importValues = array();
		foreach($importData as $data){
			if(!empty($data->key)){
				$importValues[] = JHTML::_('select.option', $data->key,$data->text);
			}
		}
		$this->assignRef('importValues', $importValues);
		$importFolders = array(JHTML::_('select.option', 'images',JText::_('HIKA_IMAGES')),JHTML::_('select.option', 'files',JText::_('HIKA_FILES')),JHTML::_('select.option', 'both',JText::_('FILES').' & '.JText::_('HIKA_IMAGES')));
		$this->assignRef('importFolders', $importFolders);
		$js = '
		var currentoption = \'file\';
		function updateImport(newoption){
			document.getElementById(currentoption).style.display = "none";
			document.getElementById(newoption).style.display = \'block\';
			currentoption = newoption;
		}
		var currentoptionFolder = \'images\';
		function updateImportFolder(newoption){
			document.getElementById(currentoptionFolder).style.display = "none";
			document.getElementById(newoption).style.display = \'block\';
			currentoptionFolder = newoption;
		}';

		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( $js );
		JHTML::_('behavior.modal');
	}

}
