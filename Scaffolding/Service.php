<?php
namespace Shake\Scaffolding;

use Nette\Object,
	Nette\DI\Container;


/**
 * Scaffolding\Manager
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class Manager extends Object
{
	/** @var Nette\DI\Container */
	protected $context;

	/** @var string */
	private $repositoryName;



	/**
	 * @param Nette\DI\Container
	 */
	public function __construct(Container $context)
	{
		$this->context = $context;
	}



	/**
	 * If manager's method doesn't exist, call repository
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$repository = $this->context->getService($this->getRepositoryName());

		return call_user_func_array(array($repository, $name), $args);
	}



	/**
	 * Get main repository name for this manager
	 * @return string
	 */
	public function getRepositoryName()
	{
		if (!$this->repositoryName)
			$this->repositoryName = $this->detectRepositoryName();

		return $this->repositoryName;
	}



	/**
	 * Set main repository name for this manager
	 * @param string
	 * @return void
	 */
	public function setRepositoryName($repositoryName)
	{
		$this->repositoryName = $repositoryName;
	}



	/**
	 * @return bool
	 */
	public function beginTransaction()
	{
		return $this->getConnection()->beginTransaction();
	}



	/**
	 * @return bool
	 */
	public function commit()
	{
		return $this->getConnection()->commit();
	}



	/**
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->getConnection()->rollBack();
	}



	/**
	 * Detect repository name based on actual sevice name
	 * @return string
	 */
	private function detectRepositoryName()
	{
		$name = get_class($this);                     // FooBarManager
		$name = substr($name, 0, strlen($name) - 7);  // FooBar
		$name = lcfirst($name);                       // fooBar

		return $name . 'Repository';                  // fooBarRepository
	}



	public function &__get($name)
	{
		// Default behavior
		try {
			return parent::__get($name);

		// Automatic service getter from context
		} catch (\Nette\MemberAccessException $e) {
			// Repository
			if (strrpos($name, 'Repository') == (strlen($name) - 10)) {
				$repository = $this->context->getService($name);
				return $repository;
			}

			// Manager
			if (strrpos($name, 'Manager') == (strlen($name) - 7)) {
				$manager = $this->context->getService($name);
				return $manager;
			}

			throw $e;
		}
	}

}
