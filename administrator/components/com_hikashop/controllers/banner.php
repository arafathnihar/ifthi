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
class BannerController extends hikashopController{
	var $type='banner';
	var $pkey = 'banner_id';
	var $table = 'banner';
	var $orderingMap ='banner_ordering';
}
