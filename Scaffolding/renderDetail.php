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
		$this->template->{$this->getEntityName()}
		 = $this->context->getService($this->getServiceName())->get($id);
	}

}
