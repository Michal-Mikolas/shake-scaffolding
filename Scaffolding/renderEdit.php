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
		$entry = $this->context->getService($this->getManagerName())->get($id);

		$this->template->{$this->getEntityName()} = $entry;
		$this['form']->setDefaults($entry);
	}

}
