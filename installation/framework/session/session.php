<?php
/**
 * @package angifw
 * @copyright Copyright (C) 2009-2013 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

class ASession
{
	/** @var string Chooses the data storage method (file/session) */
	private $method;

	/** @var string Where temporary data is stored when using file storage */
	private $storagefile;

	/** @var array The session data, as an associative array */
	private $data;

	/** @var string The session storage key */
	private $sessionkey = null;

	/**
	 * Singleton implementation
	 *
	 * @return  ASession
	 */
	static function &getInstance()
	{
		static $instance = null;

		if(!is_object($instance))
		{
			$instance = new ASession();
		}

		return $instance;
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Calculate the session key
		// -- Get the user's IP
		AUtilsIp::workaroundIPIssues();
		$ip = AUtilsIp::getUserIP();
		// -- Get the HTTPS status
		$httpsstatus = empty($_SERVER['HTTPS']) ? 'off' : $_SERVER['HTTPS'];
		// -- Calculate the session key
		$this->sessionkey = md5($ip . $_SERVER['HTTP_USER_AGENT'] . $httpsstatus . $_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_NAME']);

		// Always use the file method. The PHP session method seems to be
		// causing database restoration issues.
		$this->method = 'file';

		/**
		if(!function_exists('ini_get'))
		{
			$this->method = 'file';
		}
		elseif(is_writable(ini_get('session.save_path')))
		{
			$this->method = 'session';
		}
		else
		{
			$this->method = 'file';
		}
		/**/

		if ($this->method == 'file')
		{
			$storagefile = APATH_INSTALLATION . '/tmp/storagedata-' . $this->sessionkey . '.dat';
			$this->storagefile = $storagefile;
		}

		$this->loadData();
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->saveData();
	}

	/**
	 * Is the storage class able to save the data between page loads?
	 *
	 * @return  bool  True if everything works properly
	 */
	public function isStorageWorking()
	{
		switch($this->method)
		{
			case 'file':
				if(!file_exists($this->storagefile)) {
					$dummy = '';
					$fp = @fopen($this->storagefile,'wb');
					if($fp === false) {
						$result = false;
					} else {
						@fclose($fp);
						@unlink($this->storagefile);
						$result = true;
					}
					return $result;
				} else {
					return @is_writable($this->storagefile);
				}
				break;

			case 'session':
				return @is_writable(ini_get('session.save_path'));
				break;
		}

		return false;
	}

	/**
	 * Resets the internal storage
	 */
	public function reset()
	{
		$this->data = array();
	}

	/**
	 * Loads session data from a file or a session variable (auto detect)
	 */
	public function loadData()
	{
		switch($this->method)
		{
			case 'file':
				$this->load_using_file();
				break;

			case 'session':
				$this->load_using_session();
				break;
		}
	}

	/**
	 * Saves session data to a file or a session variable (auto detect)
	 */
	public function saveData()
	{
		switch($this->method)
		{
			case 'file':
				$this->save_using_file();
				break;

			case 'session':
				$this->save_using_session();
				break;
		}
	}

	/**
	 * Sets or updates the value of a session variable
	 *
	 * @param   $key    string  The variable's name
	 * @param   $value  string  The value to store
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
	}

	/**
	 * Returns the value of a temporary variable
	 *
	 * @param   $key      string  The variable's name
	 * @param   $default  mixed   The default value, null if not specified
	 *
	 * @return  mixed  The variable's value
	 */
	public function get($key, $default = null)
	{
		if(array_key_exists($key, $this->data))
		{
			return $this->data[$key];
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Removes a variable from the storage
	 *
	 * @param   $key  string  The name of the variable to remove
	 */
	public function remove($key)
	{
		if(array_key_exists($key, $this->data))
		{
			unset($this->data[$key]);
		}
	}

	/**
	 * Loads temporary data from a file
	 */
	private function load_using_file()
	{
		$file = @fopen($this->storagefile,'rb');
		if($file === false)
		{
			$this->data = array();
			return;
		}
		else
		{
			$raw_data = fread($file, filesize($this->storagefile));
		}
		if(@strlen($raw_data) > 0)
		{
			$this->decode_data($raw_data);
		}
		else
		{
			$this->data = array();
		}
	}

	/**
	 * Saves temporary data to a file
	 */
	private function save_using_file()
	{
		$data = $this->encode_data();
		$fp = @fopen($this->storagefile,'wb');
		@fwrite($fp, $data);
		@fclose($fp);
	}

	/**
	 * Loads temporary data from a session variable
	 */
	private function load_using_session()
	{
		session_start();
		if( isset($_SESSION['abidata-' . $this->sessionkey]) )
		{
			$data = $_SESSION['abidata-' . $this->sessionkey];
		}
		else
		{
			$data = '';
		}
		$this->decode_data($data);
	}

	/**
	 * Saves temporary data to a session variable
	 */
	private function save_using_session()
	{
		$_SESSION['abidata-' . $this->sessionkey] = $this->encode_data();
		//session_write_close();
	}

	/**
	 * Returns a serialized form of the temporary data
	 * @return string The serialized data
	 */
	private function encode_data()
	{
		$data = serialize($this->data);
		if( function_exists('base64_encode') && function_exists('base64_decode') )
		{
			// Prefer Basse64 ebcoding of data
			$data = base64_encode($data);
		}
		elseif( function_exists('convert_uuencode') && function_exists('convert_uudecode') )
		{
			// UUEncode is just as good if Base64 is not available
			$data = convert_uuencode( $data );
		}
		elseif( function_exists('bin2hex') && function_exists('pack') )
		{
			// Ugh! Let's use plain hex encoding
			$data = bin2hex($data);
		}
		// Note: on an anal server we might end up with raw data; all bets are off!

		return $data;
	}

	/**
	 * Loads the temporary data off their serialized form
	 * @param $data
	 */
	private function decode_data($data)
	{
		$this->data = array();

		if( function_exists('base64_encode') && function_exists('base64_decode') )
		{
			// Prefer Basse64 ebcoding of data
			$data = base64_decode($data);
		}
		elseif( function_exists('convert_uuencode') && function_exists('convert_uudecode') )
		{
			// UUEncode is just as good if Base64 is not available
			$data = convert_uudecode( $data );
		}
		elseif( function_exists('bin2hex') && function_exists('pack') )
		{
			// Ugh! Let's use plain hex encoding
			$data = pack("H*" , $data);
		}
		// Note: on an anal server we might end up with raw data; all bets are off!

		$temp = @unserialize($data);
		if(is_array($temp))
		{
			$this->data = $temp;
		}
		else
		{
			$this->data = array();
		}
	}
}