<?php
namespace Shake\Scaffolding;


/**
 * Scaffolding\renderDefault
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
trait renderDefault
{

	public function renderDefault()
	{
		$this->template->{$this->getListName()}
		 = $this->context->getService($this->getServiceName())->search();

		$data = $this->context->getService($this->getServiceName())->search();

		if (isset($this['paginator'])) {
			$this['paginator']->getPaginator()->itemCount
			 = $this->context->getService($this->getServiceName())->count($data);
			$this->template->{$this->getPaginatedListName()} = $this->paginate(  // wtf?
				$data,
				$this->context->getService($this->getServiceName())
			);
		}
	}

}
