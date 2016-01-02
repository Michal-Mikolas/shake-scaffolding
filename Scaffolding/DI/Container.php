<?php
namespace Shake\Scaffolding\DI;

use Shake\Utils\Strings;
use Nette;


/**
 * DI\Container
 *
 * @package Shake
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 */
class Container extends Nette\DI\Container
{
	/** @var string */
	public $repositoryMapping = 'App\\Model\\*Repository';
	
	/** @var string */
	public $serviceMapping = 'App\\Model\\*Service';

	/** @var array */
	private $registry;


	/**
	 * @param string
	 * @return object
	 */
	public function &__get($name)
	{
		return $this->getService($name);
	}



	/**
	 * @param string
	 * @return object
	 */
	public function getService($name)
	{
		if (isset($this->registry[$name]))
			return $this->registry[$name];

		// Base Nette service loading
		try {
			return parent::getService($name);
		
		// Try automatic creation
		} catch (Nette\DI\MissingServiceException $e) {

			// Repository
			if (strrpos($name, 'Repository') === (strlen($name) - 10)) {
				$this->registry[$name] = $this->createRepository($name);
			}

			// Service
			if (strrpos($name, 'Service') === (strlen($name) - 7)) {
				$this->registry[$name] = $this->createAppService($name);
			}

			if ($this->registry[$name]) 
				return $this->registry[$name];
			else 
				throw $e;
		}
	}



	/**
	 * @param string
	 * @return object
	 */
	private function createRepository($serviceName)
	{
		$className = ucfirst($serviceName);
		$className = substr($className, 0, strlen($className)-10);
		$className = str_replace('*', $className, $this->repositoryMapping);

		$repository = NULL;
		if (class_exists($className)) {
			$repositoryDependencies = $this->findRepositoryDependencies();
			$repository = $this->createInstance($className, $repositoryDependencies);
		}

		return $repository;
	}



	/**
	 * Search Nette\Database\Context or Shake\Database\Orm\Context and return it in array
	 * @return array
	 * @todo Remove this after Nette\Database\Context implements some interface
	 */
	private function findRepositoryDependencies()
	{
		if ($databaseContext = $this->getByType('Shake\\Database\\Orm\\Context', FALSE)) {
			return array($databaseContext);
		
		} elseif ($databaseContext = $this->getByType('Nette\\Database\\Context', FALSE)) {
			return array($databaseContext);
		}

		return array();	
	}



	/**
	 * @param string
	 * @return object
	 */
	private function createAppService($serviceName)
	{
		$className = ucfirst($serviceName);
		$className = substr($className, 0, strlen($className)-7);
		$className = str_replace('*', $className, $this->serviceMapping);

		$service = NULL;
		if (class_exists($className)) {
			$service = $this->createInstance($className);
		}

		return $service;
	}

}