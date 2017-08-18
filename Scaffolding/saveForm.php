<?php
namespace Shake\Scaffolding;

use Nette;


/**
 * Scaffolding\saveForm
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
trait saveForm
{

	public function saveForm(Nette\Application\UI\Form $form)
	{
		$values = $form->values;

		// Edit
		if ($id = $this->getParam('id')) {
			$this->context->getService($this->getManagerName())->update($id, $values);
			$this->flashMessage('Entry successfully updated.');

		// Create
		} else {
			$this->context->getService($this->getManagerName())->create($values);
			$this->flashMessage('Entry successfully created.');
		}

		$this->redirect('this');
	}

}
