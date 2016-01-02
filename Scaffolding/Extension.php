<?php 
namespace Shake\Scaffolding;

use Nette,
	Nette\DI\CompilerExtension,
	Nette\Database\Connection,
	Nette\PhpGenerator;


/**
 * Scaffolding\Extension
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class Extension extends CompilerExtension
{
	/** @var array */
	private $defaults = array(
		'database' => '@database.default',
		'disableVirtualServices' => FALSE,
		'repositoryMapping' => 'App\\Model\\*Repository',
		'serviceMapping' => 'App\\Model\\*Service',
	);

	/** @var string */
	private $databaseClass;

	/** @var ServiceGenerator */
	private $serviceGenerator;


	/**
	 * @return void
	 */
	private function init()
	{
		// Load config
		$this->config = $this->getConfig($this->defaults);
		$this->config['database'] = ltrim($this->config['database'], '@');

		// Use Shake\Database if available
		$this->databaseClass = $this->containerBuilder->findByType('Shake\\Database\\Orm\\Context')
			? 'Shake\\Database\\Orm\\Context'
			: 'Nette\\Database\\Context';

		// Create ServiceGenerator
		$this->serviceGenerator = new ServiceGenerator(
			$this->containerBuilder->parameters['tempDir'] . '/Shake.Scaffolding', 
			$this->config['repositoryMapping'],
			$this->config['serviceMapping']
		);

		// Activate Shake\DI\Container for service autoloading
		$this->containerBuilder->parameters['container']['parent'] = 'Shake\\Scaffolding\\DI\\Container';
	}



	/********************* beforeCompile *********************/



	/**
	 * @return void
	 */
	public function beforeCompile()
	{
		$this->init();

		$this->generateVirtualServices();
	}


	/**
	 * @return void
	 */
	private function generateVirtualServices()
	{
		// Generate by database tables
		$tables = $this->getDatabaseTables();
		foreach ($tables as $table) {
			// Repository
			$info = $this->serviceGenerator->prepareServiceInfo($table, 'repository');

			if ($this->isAllowedToCreate($info['classPath'], $info['serviceName'])) {
				$this->serviceGenerator->generateRepository($table);
				$this->addRepository($info);
			}

			// Service
			$info = $this->serviceGenerator->prepareServiceInfo($table, 'service');

			if ($this->isAllowedToCreate($info['classPath'], $info['serviceName'])) {
				$this->serviceGenerator->generateService($table);
				$this->addService($info);
			}
		}
	}


	/**
	 * @return string[]
	 */
	private function getDatabaseTables()
	{
		$tables = array();
		foreach ($this->createDatabase()->query('SHOW TABLES') as $entry) {
			$tables[] = reset($entry);
		}

		return $tables;
	}


	/**
	 * @return Nette\Database\Context
	 */
	private function createDatabase()
	{
		// Get database credentials
		$dbDefinition = $this->containerBuilder->getDefinition($this->config['database']);
		$dbConfig = array(
			'dsn' => $dbDefinition->getFactory()->arguments[0],
			'user' => $dbDefinition->getFactory()->arguments[1],
			'password' => $dbDefinition->getFactory()->arguments[2],
		);

		// Connect to DB
		$connection = new Nette\Database\Connection(
			$dbConfig['dsn'], 
			$dbConfig['user'], 
			$dbConfig['password']
		);

		// Return
		return $connection;
	}


	/**
	 * Add repository to DI container
	 * @param string
	 * @return void
	 */
	private function addRepository($info)
	{
		$this->containerBuilder->addDefinition($info['serviceName'])
			->setClass(
				$info['classPath'], 
				array('@'.$this->databaseClass)
			)
			->setInject(TRUE);
	}


	/**
	 * Add service to DI container
	 * @param string
	 * @return void
	 */
	private function addService($info)
	{
		$this->containerBuilder->addDefinition($info['serviceName'])
			->setClass($info['classPath'])
			->setInject(TRUE);
	}


	/**
	 * @param string
	 * @param string
	 * @return bool
	 */
	public function isAllowedToCreate($class, $name)
	{
		// Check if service is already defined by user
		if ($this->containerBuilder->findByType($class))
			return FALSE;  // don't create this virtual service, user already defined it on his own

		if (class_exists($class))
			return FALSE;

		// Check if user didn't disable virtual service creation
		$disabled = $this->config['disableVirtualServices'];

		if ($disabled === TRUE)
			return FALSE;  // all virtual services were disabled

		if (is_array($disabled) && (in_array($name, $disabled) || in_array($class, $disabled)))
			return FALSE;  // this virtual service were disabled in config
		
		return TRUE;
	}



	/********************* afterCompile *********************/



	/**
	 * @param Nette\PhpGenerator\ClassType
	 * @return void
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$method = $class->methods['__construct'];

		/*
		 * DI\Container services autoloading
		 */
		$method->addBody("\n"
			. "\$this->repositoryMapping = '" 
				. addslashes($this->config['repositoryMapping']) 
			. "';\n"
			. "\$this->serviceMapping = '" 
				. addslashes($this->config['serviceMapping']) 
			. "';"
		);

		/*
		 * Service classes autoloading
		 */
		if ($this->config['disableVirtualServices'] !== TRUE) {
			// Generate class autoloader
			$autoloaderPath = $this->containerBuilder->parameters['tempDir'] . '/Shake.Scaffolding/autoloader.php';
			$this->serviceGenerator->generateAutoloader($autoloaderPath);

			// Register scaffolding class autoloader 
			$method->addBody("\n"
				. "require('$autoloaderPath');"
			);
		}
	}

}