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
class hikashopImageHelper{

	function __construct() {
		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
		$app = JFactory::getApplication();
		if($app->isAdmin()){
			$this->uploadFolder_url = '../'.$this->uploadFolder_url;
		}else{
			$this->uploadFolder_url = rtrim(JURI::base(true),'/').'/'.$this->uploadFolder_url;
		}
		$this->thumbnail = $config->get('thumbnail',1);
		$this->thumbnail_x = $config->get('thumbnail_x',100);
		$this->thumbnail_y = $config->get('thumbnail_y',100);
		$this->main_thumbnail_x = $this->thumbnail_x;
		$this->main_thumbnail_y = $this->thumbnail_y;
		$this->main_uploadFolder_url = $this->uploadFolder_url;
		$this->main_uploadFolder = $this->uploadFolder;

		static $done = false;
		static $override = false;
		if(!$done){
			$done = true;
			$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'hikashop_image.php';
			if (file_exists($chromePath)){
				require_once ($chromePath);
				$override = true;
			}
		}
		$this->override = $override;
	}

	function display($path, $addpopup = true, $alt = '', $options = '', $optionslink = '', $width = 0, $height = 0) {
		$html = '';

		if(!$this->_checkImage($this->uploadFolder.$path)) {
			$config =& hikashop_config();
			$path = $config->get('default_image');
			if($path == 'barcode.png') {
				$this->uploadFolder_url = HIKASHOP_IMAGES;
				$this->uploadFolder = HIKASHOP_MEDIA.'images'.DS;
			}

			if(!$this->_checkImage($this->uploadFolder.$path)) {
				$this->uploadFolder_url = $this->main_uploadFolder_url;
				$this->uploadFolder = $this->main_uploadFolder;
				return $html;
			}
		}

		list($this->width, $this->height) = getimagesize($this->uploadFolder.$path);
		if($width != 0 && $height != 0) {
			$module = array(
				0 => $height,
				1 => $width
			);
			$this->main_thumbnail_x = $width;
			$this->main_thumbnail_y = $height;

			$html = $this->displayThumbnail($path, $alt, is_string($addpopup), $options, $module);
		} else {
			$html = $this->displayThumbnail($path, $alt, is_string($addpopup), $options);
		}

		if($addpopup) {
			$config =& hikashop_config();
			$popup_x = $config->get('max_x_popup',760);
			$popup_y = $config->get('max_y_popup',480);
			$this->width += 20;
			$this->height += 30;
			if($this->width > $popup_x)
				$this->width = $popup_x;
			if($this->height > $popup_y)
				$this->height = $popup_y;
			if(is_string($addpopup)) {
				static $first=true;
				if($first) {
					if($this->override && function_exists('hikashop_image_toggle_js')) {
						$js = hikashop_image_toggle_js($this);
					} else {
						$js = '
function hikashopChangeImage(id,url,x,y,obj,nAlt){
	if(nAlt === undefined) nAlt = \'\';
	image=document.getElementById(id);
	if(image){
		image.src=url;
		if(x) image.width=x;
		if(y) image.height=y;
		if(nAlt) image.alt=nAlt;
	}
	image_link = document.getElementById(id+\'_link\');
	if(image_link){
		image_link.href=obj.href;
		image_link.rel=obj.rel;
		if(nAlt) image_link.title=nAlt;
	}

	var myEls = getElementsByClass(\'hikashop_child_image\');
	for ( i=0;i<myEls.length;i++ ) {
		myEls[i].style.border=\'0px\';
	}

	obj.childNodes[0].style.border=\'1px solid\';
	return false;
}

function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = \'*\';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

window.addEvent(\'domready\', function() {
	image_link = document.getElementById(\'hikashop_image_small_link_first\');
	if(image_link){
		image_link.childNodes[0].style.border=\'1px solid\';
	}
});
';
					}
					$doc = JFactory::getDocument();
					$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
					$first = false;
					$optionslink.=' id="hikashop_image_small_link_first" ';
					JHTML::_('behavior.modal');
				}
				if(!empty($this->no_size_override)) {
					$this->thumbnail_x = '';
					$this->thumbnail_y = '';
					$this->uploadFolder_url_thumb = $this->uploadFolder_url.$path;
				}
				if($this->override && function_exists('hikashop_small_image_link_render')) {
					$html = hikashop_small_image_link_render($this,$path,$addpopup,$optionslink,$html,$alt);
				} else {
					$html = '<a title="'.$alt.'" class="hikashop_image_small_link" rel="{handler: \'image\'}" href="'.$this->uploadFolder_url.$path.'" onclick="SqueezeBox.fromElement(this,{parse: \'rel\'});return false;" target="_blank" onmouseover="return hikashopChangeImage(\''.$addpopup.'\',\''.$this->uploadFolder_url_thumb.'\',\''.$this->thumbnail_x.'\',\''.$this->thumbnail_y.'\',this,\''.$alt.'\');" '.$optionslink.'>'.$html.'</a>';
				}
			} else {
				JHTML::_('behavior.modal');

				if($this->override && function_exists('hikashop_image_link_render')) {
					$html = hikashop_image_link_render($this,$path,$addpopup,$optionslink,$html,$alt);
				} else {
					$html = '<a title="'.$alt.'" rel="{handler: \'image\'}" target="_blank" href="'.$this->uploadFolder_url.$path.'" onclick="SqueezeBox.fromElement(this,{parse: \'rel\'});return false;" '.$optionslink.'>'.$html.'</a>';
				}
			}
		}
		$this->uploadFolder_url = $this->main_uploadFolder_url;
		$this->uploadFolder = $this->main_uploadFolder;
		return $html;
	}

	function _checkImage($path){
		if(!empty($path)){
			jimport('joomla.filesystem.file');
			if(JFile::exists($path)){
				return true;
			}
		}
		return false;
	}

	function checkSize(&$width,&$height,&$row){
		$exists=false;
		if(!empty($row->file_path)){
			jimport('joomla.filesystem.file');
			if(JFile::exists(HIKASHOP_MEDIA.'upload'.DS.$row->file_path)){
				$exists=true;
			}else{
				$exists=false;
			}
		}

		if(!$exists){
			$config =& hikashop_config();
			$path = $config->get('default_image');
			if($path == 'barcode.png'){
				$file_path=HIKASHOP_MEDIA.'images'.DS.'barcode.png';
			}
			if(!empty($path)){
				jimport('joomla.filesystem.file');
				if(JFile::exists($this->main_uploadFolder.$path)){
					$exists=true;
				}
			}else{
				$exists=false;
			}
			if($exists){
				$file_path=$this->main_uploadFolder.$path;
			}
		}else{
			$file_path=$this->main_uploadFolder.$row->file_path;
		}
		if(!empty($file_path)){
			$theImage= new stdClass();
			list($theImage->width, $theImage->height) = getimagesize($file_path);
			if(empty($width)){
				if($theImage->height >= $height){
					list($width, $height) = $this->scaleImage($theImage->width, $theImage->height, 0, $height);
				}else{
					$width=$this->main_thumbnail_x;
				}
			}
			if(empty($height)){
				if($theImage->width >= $width){
					list($width, $height) = $this->scaleImage($theImage->width, $theImage->height, $width, 0);
				}else{
					$height=$this->main_thumbnail_y;
				}
			}
		}

	}

	function getPath($file_path,$url=true){
		if($url){
			return $this->uploadFolder_url.$file_path;
		}
		return $this->uploadFolder.$file_path;
	}

	function displayThumbnail($path,$alt='',$reduceSize=false,$options='',$module=false){
		if((empty($this->main_thumbnail_x) && !empty($this->main_thumbnail_y)) || (empty($this->main_thumbnail_y) && !empty($this->main_thumbnail_x))){
			$module[0]=$this->main_thumbnail_y;
			$module[1]=$this->main_thumbnail_x;
		}
		$new = $this->scaleImage($this->width, $this->height,$this->main_thumbnail_x,$this->main_thumbnail_y);

		if($new !== false) {
			$this->thumbnail_x = $new[0];
			$this->thumbnail_y = $new[1];
		}else{
			$this->thumbnail_x = $this->width;
			$this->thumbnail_y = $this->height;
		}

		if($module){
			if(empty($this->main_thumbnail_y)){$this->main_thumbnail_y=0;}
			if(empty($this->main_thumbnail_x)){$this->main_thumbnail_x=0;}
			$folder='thumbnail_'.$this->main_thumbnail_y.'x'.$this->main_thumbnail_x;
		}else{
			$folder='thumbnail_'.$this->thumbnail_y.'x'.$this->thumbnail_x;
		}

		if(!$reduceSize && !$module ){
			$options.=' height="'.$this->thumbnail_y.'" width="'.$this->thumbnail_x.'" ';
		}
		if($this->thumbnail){
			jimport('joomla.filesystem.file');
			$ok = true;
			if(!JFile::exists($this->uploadFolder.$folder.DS.$path)){
				if($module){
					$ok = $this->generateThumbnail($path, $module);
				}
				else{
					$ok = $this->generateThumbnail($path);
				}
			}

			if($ok){
				if(is_array($ok)){
					$folder='thumbnail_'.$ok[0].'x'.$ok[1];
				}
				$this->uploadFolder_url_thumb=$this->uploadFolder_url.$folder.'/'.$path;
				return '<img src="'.$this->uploadFolder_url_thumb.'" alt="'.$alt.'" '.$options.' />';
			}
		}
		$this->uploadFolder_url_thumb=$this->uploadFolder_url.$path;

		return '<img src="'.$this->uploadFolder_url_thumb.'" alt="'.$alt.'" '.$options.' />';
	}

	function getThumbnail($filename, $size = null, $options = array(), $relativePath = true, $cachePath = null) {
		$config =& hikashop_config();
		$scalemode = 'inside';

		$ret = new stdClass();
		$ret->success = false;
		$ret->path = $filename;
		$ret->height = 0;
		$ret->width = 0;
		$ret->req_height = 0;
		$ret->req_width = 0;

		$fullFilename = $filename;
		if($relativePath === true)
			$fullFilename = $this->uploadFolder . $filename;
		if(is_string($relativePath))
			$fullFilename = $relativePath . $filename;

		if(empty($cachePath))
			$cachePath = $this->uploadFolder;
		else
			$cachePath = rtim(JFolder::cleanPath($cachePath), DS) . DS;

		if(!JFile::exists($fullFilename)) {
			if(!isset($options['default']))
				return $ret;
			$config =& hikashop_config();
			$ret->path = $filename = $config->get('default_image');
			if($ret->path == 'barcode.png') {
				$fullFilename = HIKASHOP_MEDIA.'images'.DS . $ret->path;
				$ret->url = HIKASHOP_IMAGES;
			} else {
				$fullFilename = $this->uploadFolder . $ret->path;
			}
			if(!JFile::exists($fullFilename)) {
				return $ret;
			}
		}

		if(empty($size))
			$size = array('x' => $config->get('thumbnail_x', 100), 'y' => $config->get('thumbnail_y', 100));
		if(isset($size['width']))
			$size = array('x' => $size['width'], 'y' => $size['height']);
		if(!isset($size['x']))
			$size = array('x' => $size[0], 'y' => $size[1]);

		$optString = '';
		if(!empty($options['forcesize'])) $optString .= 'f';
		if(!empty($options['grayscale'])) $optString .= 'g';
		if(!empty($options['blur'])) $optString .= 'b';

		if(!empty($options['scale'])) {
			switch($options['scale']) {
				case 'outside':
					$scalemode = 'outside';
					$optString .= 'sO';
				case 'inside':
					break;
			}
		}

		if(!empty($options['radius']) && (int)$options['radius'] > 2) $optString .= 'r'.(int)$options['radius'];

		$destFolder = 'thumbnails' . DS . $size['y'] . 'x' . $size['x'] . $optString;
		$ret->req_height = $size['y'];
		$ret->req_width = $size['x'];

		$origin = new stdClass();
		list($origin->width, $origin->height) = getimagesize($fullFilename);
		$ret->orig_height = $origin->height;
		$ret->orig_width = $origin->width;

		if(JFile::exists($cachePath . $destFolder . DS . $filename)) {
			$ret->success = true;
			$ret->path = $destFolder . DS . $filename;
			list($ret->width, $ret->height) = getimagesize($cachePath . $destFolder . DS . $filename);
			return $ret;
		}

		$scaling = $this->scaleImage($origin->width, $origin->height, $size['x'], $size['y'], $scalemode);
		if($scaling === false && empty($options['forcesize'])) {
			$ret->success = true;
			$ret->width = $origin->width;
			$ret->height = $origin->height;

			return $ret;
		}
		unset($ret->url);
		if($scaling === false) {
			$scaling = array($origin->width, $origin->height);
		}

		$extension = strtolower(substr($filename, strrpos($filename, '.') + 1));
		$quality = array(
			'jpg' => 95,
			'png' => 9
		);
		if(!empty($options['quality'])) {
			if(is_array($options['quality'])) {
				if(!empty($options['quality']['jpg']))
					$quality['jpg'] = (int)$options['quality']['jpg'];
				if(!empty($options['quality']['png']))
					$quality['png'] = (int)$options['quality']['png'];
			} elseif((int)$options['quality'] > 0) {
				$quality['jpg'] = (int)$options['quality'];
			}
		}

		$img = $this->_getImage($fullFilename, $extension);
		if(!$img)
			return false;

		$transparentIndex = imagecolortransparent($img);
		if(in_array($extension, array('gif', 'png'))) {
			imagealphablending($img, false);
			imagesavealpha($img, true);
		}

		if(empty($options['forcesize']))
			$thumb = imagecreatetruecolor($scaling[0], $scaling[1]);
		else
			$thumb = imagecreatetruecolor($size['x'], $size['y']);

		$bgcolor = $this->_getBackgroundColor($thumb, @$option['background']);
		if(in_array($extension,array('gif', 'png'))) {
			if($transparentIndex >= 0) {
				$trnprt_color = imagecolorsforindex($img, $transparentIndex);
				$color = imagecolorallocate($thumb, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagecolortransparent($thumb, $color);
				imagefill($thumb, 0, 0, $color);
			} elseif($extension == 'png') {
				imagealphablending($thumb, false);
				$color = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
				imagefill($thumb, 0, 0, $color);
				imagesavealpha($thumb, true);
			}
		} else {
			imagefill($thumb, 0, 0, $bgcolor);
		}

		if(function_exists('imageantialias')) {
			imageantialias($thumb, true);
		}

		$x = 0;
		$y = 0;
		if(!empty($options['forcesize'])) {
			$x = ($size['x'] - $scaling[0]) / 2;
			$y = ($size['y'] - $scaling[1]) / 2;
		}
		$sx = $scaling[0];
		$sy = $scaling[1];
		if($origin->width < $sx) $sx = $origin->width;
		if($origin->height < $sy) $sy = $origin->height;

		if($transparentIndex >= 0 && function_exists('imagecopyresampled')) {
			imagecopyresampled($thumb, $img, $x, $y, 0, 0, $sx, $sy, $origin->width, $origin->height);
		} else {
			imagecopyresized($thumb, $img, $x, $y, 0, 0, $sx, $sy, $origin->width, $origin->height);
		}

		if(!empty($options['radius']) && (int)$options['radius'] > 2) {
			$radius = (int)$options['radius'];
			$corner_image = imagecreatetruecolor($radius, $radius);
			imagealphablending($corner_image, false);
			imagesavealpha($corner_image, true);
			$bgcolor = $this->_getBackgroundColor($corner_image, @$option['background']);
			$color = imagecolorallocatealpha($corner_image, 0, 0, 0, 127);
			imagecolortransparent($corner_image, $color);
			imagefill($corner_image, 0, 0, $bgcolor);
			imagefilledellipse($corner_image, $radius, $radius, $radius * 2, $radius * 2, $color);
			imagecopymerge($thumb, $corner_image, 0, 0, 0, 0, $radius, $radius, 100);
			$corner_image = imagerotate($corner_image, 90, 0);
			imagecopymerge($thumb, $corner_image, 0, $scaling[1] - $radius, 0, 0, $radius, $radius, 100);
			$corner_image = imagerotate($corner_image, 90, 0);
			imagecopymerge($thumb, $corner_image, $scaling[0] - $radius, $scaling[1] - $radius, 0, 0, $radius, $radius, 100);
			$corner_image = imagerotate($corner_image, 90, 0);
			imagecopymerge($thumb, $corner_image, $scaling[0] - $radius, 0, 0, 0, $radius, $radius, 100);
		}

		if(function_exists('imagefilter')) {
			if(!empty($options['grayscale']))
				imagefilter($thumb, IMG_FILTER_GRAYSCALE);
			if(!empty($options['blur']))
				imagefilter($thumb, IMG_FILTER_GAUSSIAN_BLUR);
		}

		ob_start();
		switch($extension) {
			case 'gif':
				$status = imagegif($thumb);
				break;
			case 'jpg':
			case 'jpeg':
				$status = imagejpeg($thumb, null, $quality['jpg']);
				break;
			case 'png':
				$status = imagepng($thumb, null, $quality['png']);
				break;
		}

		$imageContent = ob_get_clean();
		$ret->success = $status && JFile::write($cachePath . $destFolder . DS . $filename, $imageContent);
		if($ret->success) {
			$ret->width = $scaling[0];
			$ret->height = $scaling[1];
			$ret->path = $destFolder . DS . $filename;
		}

		imagedestroy($img);
		@imagedestroy($thumb);

		return $ret;
	}

	function _getBackgroundColor($resource, $color) {
		if(!empty($color)) {
			if(is_array($color)) {
				$bgcolor = imagecolorallocatealpha($resource, $color[0], $color[1], $color[2], 0);
			} elseif( is_string($color) ) {
				$rgb = str_split(ltrim($color, '#'), 2);
				$bgcolor = imagecolorallocatealpha($resource, hexdec($rgb[0]), hexdec($rgb[1]), hexdec($rgb[2]), 0);
			}
		}
		if(empty($bgcolor)) {
			$bgcolor = imagecolorallocatealpha($resource, 255, 255, 255, 0);
		}
		return $bgcolor;
	}

	function generateThumbnail($file_path, $module=false){
		$ok = true;
		if($this->thumbnail){
			$ok = false;
			$gd_ok = false;
			if (function_exists('gd_info')) {
				$gd = gd_info();
				if (isset ($gd["GD Version"])) {
					$gd_ok = true;
					list($this->width, $this->height) = getimagesize($this->uploadFolder.$file_path);
					$config =& hikashop_config();
					if($module){
						$thumbnail_x=$module[1];
						$thumbnail_y=$module[0];
					}
					else{
						$thumbnail_x=$config->get('thumbnail_x',100);
						$thumbnail_y=$config->get('thumbnail_y',100);
					}
					if(!$thumbnail_x && !$thumbnail_y){
						return true;
					};
					$new = $this->scaleImage($this->width, $this->height,$thumbnail_x,$thumbnail_y);
					if($new!==false){
						if(empty($thumbnail_y)){$thumbnail_y=0;}
						if(empty($thumbnail_x)){$thumbnail_x=0;}
						$ok = $this->_resizeImage($file_path, $new[0], $new[1], $this->uploadFolder.'thumbnail_'.$thumbnail_y.'x'.$thumbnail_x.DS);
						if($ok & !$module){
							$ok = array($new[1],$new[0]);
						}
					}
				}
			}
			if(!$gd_ok){
				$app =& JFactory::getApplication();
				if($app->isAdmin()){
					$app->enqueueMessage('The PHP GD extension could not be found. Thus, it is impossible to generate thumbnails in PHP from your images. If you want HikaShop to generate thumbnails you need to install GD or ask your hosting company to do so. Otherwise, you can deactivate thumbnails creation in the configuration of HikaShop and this message won\'t be displayed');
				}
			}
		}
		return $ok;
	}

	function resizeImage($file_path,$type='image'){
		$config =& hikashop_config();
		$image_x=$config->get('image_x',0);
		$image_y=$config->get('image_y',0);
		$watermark_name = $config->get('watermark','');
		$ok = true;
		if(($image_x || $image_y) || !empty($watermark_name)){
			$ok = false;
			$gd_ok = false;
			if (function_exists('gd_info')) {
				$gd = gd_info();

				if (isset ($gd["GD Version"])) {
					$gd_ok = true;
					$new = getimagesize($this->uploadFolder.$file_path);
					$this->width=$new[0];
					$this->height=$new[1];

					if(!$image_x && !$image_y && empty($watermark_name)){
						return true;
					}
					if($image_x || $image_y){
						$new = $this->scaleImage($this->width, $this->height,$image_x,$image_y);
						if($new===false){
							$new = array($this->width,$this->height);
						}
					}

					$ok = $this->_resizeImage($file_path,$new[0],$new[1],$this->uploadFolder,$type);
				}
			}
			if(!$gd_ok){
				$app =& JFactory::getApplication();
				if($app->isAdmin()){
					$app->enqueueMessage('The PHP GD extension could not be found. Thus, it is impossible to process your images in PHP. If you want HikaShop to process your images, you need to install GD or ask your hosting company to do so. Otherwise, you can deactivate thumbnails creation, remove your watermark image if any, and clear the image max width and height in the configuration of HikaShop and this message won\'t be displayed');
				}
			}
		}
		return $ok;
	}


	function _resizeImage($file_path,$newWidth,$newHeight,$dstFolder='',$type='thumbnail'){
		$image = $this->uploadFolder.$file_path;

		if(empty($dstFolder)){
			$dstFolder = $this->uploadFolder.'thumbnail_'.$this->thumbnail_y.'x'.$this->thumbnail_x.DS;
		}
		$watermark_path = '';

		if($type=='image'){
			if(hikashop_level(2)){
				$config =& hikashop_config();
				$watermark_name = $config->get('watermark','');

				if(!empty($watermark_name)){
					$watermark_path = $this->main_uploadFolder.$watermark_name;

					if(!$this->_checkImage($watermark_path)){
						$watermark_path = '';
					}else{
						$wm_extension = strtolower(substr($watermark_path,strrpos($watermark_path,'.')+1));

						$watermark = $this->_getImage($watermark_path,$wm_extension);
						if($watermark){
							if(in_array($wm_extension,array('gif','png'))){
								imagealphablending($watermark, false);
								imagesavealpha($watermark,true);
							}
						}else{
							$watermark_path = '';
						}
					}
				}
			}
		}

		$extension = strtolower(substr($file_path,strrpos($file_path,'.')+1));

		$img = $this->_getImage($image,$extension);
		if(!$img) return false;

		if(in_array($extension,array('gif','png'))){
			imagealphablending($img, false);
			imagesavealpha($img,true);
		}
		if($newWidth!=$this->width || $newHeight!=$this->height){

			$thumb = ImageCreateTrueColor($newWidth, $newHeight);

			if(in_array($extension,array('gif','png'))){
				$trnprt_indx = imagecolortransparent($img);

				if ($trnprt_indx >= 0) {
					$trnprt_color = imagecolorsforindex($img, $trnprt_indx);
					$trnprt_indx = imagecolorallocate($thumb, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
					imagefill($thumb, 0, 0, $trnprt_indx);
					imagecolortransparent($thumb, $trnprt_indx);
				}elseif($extension=='png'){
					imagealphablending($thumb, false);
					$color = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
					imagefill($thumb, 0, 0, $color);
					imagesavealpha($thumb,true);
				}
			}
			if(function_exists("imageAntiAlias")) {
				imageAntiAlias($thumb,true);
			}
			if(function_exists("imagecopyresampled")){
				ImageCopyResampled($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight,$this->width, $this->height);
			}else{
				ImageCopyResized($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight,$this->width, $this->height);
			}
		}else{
			$thumb =& $img;
		}

		if(!empty($watermark_path)){
			list($wm_width,$wm_height) = getimagesize($watermark_path);
			$padding = 3;
			$dest_x = $newWidth - $wm_width - $padding;
			$dest_y = $newHeight - $wm_height - $padding;
			$trnprt_color=null;
			if(in_array($extension,array('gif','png'))){
				$trnprt_indx = imagecolortransparent($img);
				if ($trnprt_indx >= 0) {
					$trnprt_color = imagecolorsforindex($img, $trnprt_indx);
				}
			}
			imagealphablending($thumb, false);
			imagealphablending($watermark, false);
			$this->imagecopymerge_alpha($thumb, $watermark, $dest_x, $dest_y, 0, 0, $wm_width, $wm_height, (int)$config->get('opacity',0),$trnprt_color);
			imagedestroy($watermark);
		}

		$dest = $dstFolder.$file_path;
		ob_start();
		switch($extension){
			case 'gif':
				$status = imagegif($thumb);
				break;
			case 'jpg':
			case 'jpeg':
				$status = imagejpeg($thumb,null,100);
				break;
			case 'png':
				$status = imagepng($thumb,null,0);
				break;
		}

		$imageContent = ob_get_clean();

		$status = $status && JFile::write($dest,$imageContent);
		imagedestroy($img);
		@imagedestroy($thumb);
		return $status;
	}

	function _getImage($image,$extension){
		switch($extension){
			case 'gif':
				return ImageCreateFromGIF($image);
				break;
			case 'jpg':
			case 'jpeg':
				return ImageCreateFromJPEG($image);
				break;
			case 'png':
				return ImageCreateFromPNG($image);
				break;
		}
	}


	function scaleImage($x, $y, $cx, $cy, $scaleMode = 'inside') {
		if(empty($cx)) $cx = 9999;
		if(empty($cy)) $cy = 9999;

		if ($x >= $cx || $y >= $cy) {
			if ($x>0) $rx = $cx / $x;
			if ($y>0) $ry = $cy / $y;

			switch($scaleMode) {

				case 'outside': {
					if ($rx > $ry)
						$r = $rx;
					else
						$r = $ry;
				}
				break;

				case 'inside':
				default: {
					if ($rx > $ry)
						$r = $ry;
					else
						$r = $rx;
				}
				break;
			}
			$x = intval($x * $r);
			$y = intval($y * $r);
			return array($x,$y);
		}
		return false;
	}









	function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct, $trans = NULL)
	{
		$dst_w = imagesx($dst_im);
		$dst_h = imagesy($dst_im);

		$src_x = max($src_x, 0);
		$src_y = max($src_y, 0);
		$dst_x = max($dst_x, 0);
		$dst_y = max($dst_y, 0);
		if ($dst_x + $src_w > $dst_w)
			$src_w = $dst_w - $dst_x;
		if ($dst_y + $src_h > $dst_h)
			$src_h = $dst_h - $dst_y;

		for($x_offset = 0; $x_offset < $src_w; $x_offset++)
			for($y_offset = 0; $y_offset < $src_h; $y_offset++)
			{
				$srccolor = imagecolorsforindex($src_im, imagecolorat($src_im, $src_x + $x_offset, $src_y + $y_offset));
				$dstcolor = imagecolorsforindex($dst_im, imagecolorat($dst_im, $dst_x + $x_offset, $dst_y + $y_offset));

				if (is_null($trans) || ($srccolor !== $trans))
				{
					$src_a = $srccolor['alpha'] * $pct / 100;
					$src_a = 127 - $src_a;
					$dst_a = 127 - $dstcolor['alpha'];
					$dst_r = ($srccolor['red'] * $src_a + $dstcolor['red'] * $dst_a * (127 - $src_a) / 127) / 127;
					$dst_g = ($srccolor['green'] * $src_a + $dstcolor['green'] * $dst_a * (127 - $src_a) / 127) / 127;
					$dst_b = ($srccolor['blue'] * $src_a + $dstcolor['blue'] * $dst_a * (127 - $src_a) / 127) / 127;
					$dst_a = 127 - ($src_a + $dst_a * (127 - $src_a) / 127);
					$color = imagecolorallocatealpha($dst_im, $dst_r, $dst_g, $dst_b, $dst_a);
					if (!imagesetpixel($dst_im, $dst_x + $x_offset, $dst_y + $y_offset, $color))
						return false;
					imagecolordeallocate($dst_im, $color);
				}
			}
		return true;
	}

}
