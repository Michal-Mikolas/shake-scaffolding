<?php
namespace Shake\Scaffolding;


/**
 * Scaffolding\handleDelete
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
trait handleDelete
{

	public function handleDelete($id)
	{
		$result = $this->context->getService($this->serviceName)->delete($id);

		if ($result) {
			$this->flashMessage('Entry succesfully deleted.');
		} else {
			$this->flashMessage('No data was deleted.', 'error');			
		}

		$this->redirect('this');
	}

}