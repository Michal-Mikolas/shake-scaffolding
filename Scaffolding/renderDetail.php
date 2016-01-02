<?php
namespace Shake\Scaffolding;


/**
 * Scaffolding\renderDetail
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
trait renderDetail
{

	public function renderDetail($id)
	{
		$this->template->{$this->entityName} = $this->context->getService($this->serviceName)->get($id);
	}

}