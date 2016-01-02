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
		$this->template->{$this->listName} = $this->context->getService($this->serviceName)->search();

		$data = $this->context->getService($this->serviceName)->search();
		
		if (isset($this['paginator'])) {
			$this['paginator']->paginator->itemCount = $this->context->getService($this->serviceName)->count($data);
			$this->template->{$this->paginatedListName} = $this->paginate(  // TODO: check functionality
				$data,
				$this->context->getService($this->serviceName)
			);
		}
	}

}