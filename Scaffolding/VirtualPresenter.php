<?php
namespace Shake\Scaffolding;


/**
 * Scaffolding\VirtualPresenter
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class VirtualPresenter extends ScaffoldingPresenter
{
	use renderDefault;
	use renderDetail;
	use renderEdit;
	use renderAdd;
	use handleDelete;
	use createComponentForm;
	use saveForm;
	use createComponentPaginator;



	public function formatTemplateFiles()
	{
		$name = $this->getName();		
		$modules = explode(':', $name);
		$presenter = array_pop($modules);

		$dir = $this->context->parameters['appDir'];
		foreach ($modules as $module) {
			$module = ucfirst($module);
			$dir .= "/{$module}Module";
		}

		return array(
			"$dir/templates/$presenter/$this->view.latte",
			"$dir/templates/$presenter.$this->view.latte",
			"$dir/templates/$presenter/$this->view.phtml",
			"$dir/templates/$presenter.$this->view.phtml",
		);
	}



	public function formatLayoutTemplateFiles()
	{
		$name = $this->getName();
		$modules = explode(':', $name);
		$presenter = array_pop($modules);
		$layout = $this->layout ? $this->layout : 'layout';

		$dir = $this->context->parameters['appDir'];
		foreach ($modules as $module) {
			$module = ucfirst($module);
			$dir .= "/{$module}Module";
		}
		
		$list = array(
			"$dir/templates/$presenter/@$layout.latte",
			"$dir/templates/$presenter.@$layout.latte",
			"$dir/templates/$presenter/@$layout.phtml",
			"$dir/templates/$presenter.@$layout.phtml",
		);
		do {
			$list[] = "$dir/templates/@$layout.latte";
			$list[] = "$dir/templates/@$layout.phtml";
			$dir = dirname($dir);
		} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
		
		return $list;
	}

}