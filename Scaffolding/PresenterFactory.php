<?php
namespace Shake\Scaffolding;

use Shake\Application\UI\Presenter;
use Nette;


/**
 * Scaffolding\PresenterFactory
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class PresenterFactory extends Nette\Application\PresenterFactory
{
	/** @var Nette\DI\Container */
	private $context;


	/**
	 * @param Nette\DI\Container
	 */
	public function __construct(Nette\DI\Container $context)
	{
		parent::__construct();

		$this->context = $context;
	}


	/**
	 * Creates new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$presenterClass = $this->getPresenterClass($name);
		
		$presenter = new $presenterClass();
		$this->context->callInjects($presenter);

		return $presenter;
	}


	/**
	 * Generates and checks presenter class name.
	 * @param  string  presenter name
	 * @return string  class name
	 */
	public function getPresenterClass(& $name)
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