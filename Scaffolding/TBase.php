<?php
namespace Shake\Scaffolding;

use Shake,
	Shake\Utils\Strings;


/**
 * Scaffolding\TBase
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
trait TBase
{

	public function getEntityName()
	{
		$name = $this->name;                                    // Admin:ShopCategory
		$name = trim(substr($name, strrpos($name, ':')), ':');  // ShopCategory
		$name = Strings::toCamelCase($name);                    // shopCategory

		return $name;
	}



	public function getListName()
	{
		return Strings::plural( $this->getEntityName() );
	}



	public function getPaginatedListName()
	{
		return $this->getListName() . 'Paginated';
	}



	public function getServiceName()
	{
		return $this->getEntityName() . 'Service';
	}

}