<?php
namespace Shake\Scaffolding;

use Nette;


/**
 * Scaffolding\PresenterFactory
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class PresenterFactory extends Nette\Application\PresenterFactory
{

	public function getPresenterClass(&$name)
	{
		// Default Nette presenter loading
		try {
			return parent::getPresenterClass($name);

		// Create virtual presenter
		} catch (Nette\Application\InvalidPresenterException $e) {
			return 'Shake\Scaffolding\VirtualPresenter';
		}
	}

}
