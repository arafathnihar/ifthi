<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2013 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieControllerSetup extends AController
{
	public function apply()
	{
		$model = $this->getThisModel();
		try
		{
			$writtenConfiguration = $model->applySettings();
			$msg = null;
			$url = AUri::base() . 'index.php?view=finalise';
			if (!$writtenConfiguration)
			{
				$url .= '&showconfig=1';
			}
		}
		catch (Exception $exc)
		{
			$error = true;
			$msg = $exc->getMessage();
			$url = AUri::base() . 'index.php?view=setup';
		}

		$this->setRedirect($url, $msg, 'error');
	}
}