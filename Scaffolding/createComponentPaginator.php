<?php
namespace Shake\Scaffolding;

use Shake\Components\VisualPaginator;


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
		$vp->paginator->itemsPerPage = 10;

		return $vp;
	}

}