<?php
declare(strict_types=1);

namespace Shake\Scaffolding\DI;

use Nette;


/**
 * Scaffolding\DI\Extension
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class Extension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->compiler->getConfig();

		$builder = $this->getContainerBuilder();

		$builder->addDefinition('application.presenterFactoryCallback')
		->setFactory(
			'Nette\Bridges\ApplicationDI\PresenterFactoryCallback',
			[
				'@container',
				Nette\Application\UI\Presenter::INVALID_LINK_WARNING,  // invalidLinkMode
				FALSE,                                                 // touchToRefresh
			]
		);

		$builder->removeDefinition('application.presenterFactory');
		$builder->addDefinition('application.presenterFactory')
			->setFactory(
				'Shake\Scaffolding\PresenterFactory',
				['@application.presenterFactoryCallback']
			)
			->addSetup(
				'setMapping',
				[$config['application']['mapping']]
			);
	}

}
