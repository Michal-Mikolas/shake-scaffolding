<?php
namespace Shake\Scaffolding;


/**
 * Scaffolding\renderEdit
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
trait renderEdit
{

	public function renderEdit($id)
	{
		$entry = $this->context->{$this->serviceName}->get($id);
		
		$this->template->{$this->entityName} = $entry;
		$this['form']->setDefaults($entry);
	}

}