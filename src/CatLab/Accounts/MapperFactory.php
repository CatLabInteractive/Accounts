<?php

namespace CatLab\Accounts;


class MapperFactory {

	public static function getInstance ()
	{
		static $in;
		if (!isset ($in))
		{
			$in = new self ();
		}
		return $in;
	}

	private $mapped = array ();

	public function setMapper ($key, $mapper)
	{
		$this->mapped[$key] = $mapper;
	}

	public function getMapper ($key, $default)
	{
		if (isset ($this->mapped[$key]))
		{
			return $this->mapped[$key];
		}
		else
		{
			$this->mapped[$key] = new $default ();
		}
		return $this->mapped[$key];
	}

	/**
	 * @return \CatLab\Accounts\Mappers\DeligatedMapper
	 */
	public static function getDeligatedMapper () {
		return self::getInstance ()->getMapper ('deligated', '\CatLab\Accounts\Mappers\DeligatedMapper');
	}
}