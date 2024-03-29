<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2013 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelDatabase extends AModel
{
	/**
	 * The databases.ini contents
	 *
	 * @var array
	 */
	private $dbini = array();

	/**
	 * Returns the cached databases.ini information, parsing the databases.ini
	 * file if necessary.
	 *
	 * @return array
	 */
	public function getDatabasesIni()
	{
		if (empty($this->dbini))
		{
			$this->dbini = ASession::getInstance()->get('databases.dbini', null);
			if (empty($this->dbini))
			{
				$filename = APATH_INSTALLATION . '/sql/databases.ini';
				if (file_exists($filename))
				{
					$this->dbini = parse_ini_file($filename, true);
				}

				if(!empty($this->dbini))
				{
					// Add the custom options
					$temp = array();
					$joomlasql = null;
					foreach($this->dbini as $key => $data)
					{
						if(!array_key_exists('existing', $data))
						{
							$data['existing'] = 'drop';
						}
						if(!array_key_exists('prefix', $data))
						{
							$data['prefix'] = 'jos_';
						}
						if(!array_key_exists('foreignkey', $data))
						{
							$data['foreignkey'] = true;
						}
						if(!array_key_exists('replace', $data))
						{
							$data['replace'] = false;
						}
						if(!array_key_exists('utf8db', $data))
						{
							$data['utf8db'] = false;
						}
						if(!array_key_exists('utf8tables', $data))
						{
							$data['utf8tables'] = false;
						}
						if(!array_key_exists('maxexectime', $data))
						{
							$data['maxexectime'] = 5;
						}
						if(!array_key_exists('throttle', $data))
						{
							$data['throttle'] = 250;
						}

						if ($key == 'joomla.sql')
						{
							$joomlasql = $data;
						}
						else
						{
							$temp[$key] = $data;
						}
					}

					$temp = array_merge(array('joomla.sql' => $joomlasql), $temp);

					$this->dbini = $temp;
				}

				ASession::getInstance()->set('databases.dbini', $this->dbini);
			}
		}

		return $this->dbini;
	}

	/**
	 * Saves the (modified) databases information to the session
	 */
	public function saveDatabasesIni()
	{
		ASession::getInstance()->set('databases.dbini', $this->dbini);
	}

	/**
	 * Returns the keys of all available database definitions
	 *
	 * @return array
	 */
	public function getDatabaseNames()
	{
		$dbini = $this->getDatabasesIni();

		return array_keys($dbini);
	}

	/**
	 * Returns an object with a database's connection information
	 *
	 * @param   string  $key  The database's key (name of SQL file)
	 *
	 * @return  null|stdClass
	 */
	public function getDatabaseInfo($key)
	{
		$dbini = $this->getDatabasesIni();

		if(array_key_exists($key, $dbini))
		{
			return (object)$dbini[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Sets a database's connection information
	 *
	 * @param   string  $key   The database's key (name of SQL file)
	 * @param   mixed   $data  The database's data (stdObject or array)
	 */
	public function setDatabaseInfo($key, $data)
	{
		$dbini = $this->getDatabasesIni();

		$this->dbini[$key] = (array) $data;

		$this->saveDatabasesIni();
	}
}