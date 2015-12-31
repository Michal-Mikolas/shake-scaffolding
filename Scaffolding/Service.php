<?php
namespace Shake\Scaffolding;

use Nette\Object,
	Nette\DI\Container;


/**
 * Scaffolding\Service
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class Service extends Object
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
	 * If service method doesn't exist, call repository
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$repository = $this->context->getService( $this->getRepositoryName() );

		return call_user_func_array(array($repository, $name), $args);
	}



	/**
	 * Get main repository name for this service
	 * @return string
	 */
	public function getRepositoryName()
	{
		if (!$this->repositoryName)
			$this->repositoryName = $this->detectRepositoryName();

		return $this->repositoryName;
	}



	/**
	 * Set main repository name for this service
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
		$name = get_class($this);                     // App\Model\FooBarService
		$name = substr($name, 0, strlen($name) - 7);  // App\Model\FooBar
		$name = substr($name, strrpos($name, '\\'));  // \FooBar
		$name = trim($name, '\\');                    // FooBar
		$name = lcfirst($name);                       // fooBar

		return $name . 'Repository';                  // fooBarRepository
	}



	/**
	 * @param  string  property name
	 * @return mixed   property value
	 */
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
			
			// Service
			if (strrpos($name, 'Service') == (strlen($name) - 7)) {
				$service = $this->context->getService($name);
				return $service;
			}

			throw $e;
		}
	}

}