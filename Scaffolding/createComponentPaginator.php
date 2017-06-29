<?php
namespace Shake\Scaffolding;

use Shake\VisualPaginator;


/**
 * Scaffolding\createComponentPaginator
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
trait createComponentPaginator
{

	protected function createComponentPaginator($name)
	{
		$vp = new VisualPaginator($this, $name);
		$vp->getPaginator()->itemsPerPage = 10;

		return $vp;
	}

}
