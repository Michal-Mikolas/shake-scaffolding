<?php 
namespace Shake\Scaffolding;

use Shake\Utils\Strings;
use Nette,
	Nette\Object,
	Nette\PhpGenerator,
	Nette\Utils\FileSystem;


/**
 * Scaffolding\ServiceGenerator
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 * @internal
 */
class ServiceGenerator extends Object
{
	/** @var string */
	private $dir;

	/** @var string */
	private $repositoryMapping;

	/** @var string */
	private $serviceMapping;

	/** @var string[]  list of generated classes */
	private $classes = array();


	/**
	 * @param string
	 * @param string
	 * @param string
	 */
	public function __construct($dir, $repositoryMapping, $serviceMapping)
	{
		$this->dir = $dir;
		$this->repositoryMapping = $repositoryMapping;
		$this->serviceMapping = $serviceMapping;

		FileSystem::createDir($this->dir);
	}


	/**
	 * @param string  table name
	 * @param string  "repository"|"service"
	 * @return array  [serviceName, classPath, className, classNamespace, fileName, filePath]
	 */
	public function prepareServiceInfo($table, $type)
	{
		// Prepare
		if ($type == 'repository') {
			$sufix = 'Repository';
			$mapping = $this->repositoryMapping;

		} else {
			$sufix = 'Service';
			$mapping = $this->serviceMapping;
		}

		$name = Strings::toCamelCase($table);

		// Get info
		$info = array();
		$info['serviceName'] = $name . $sufix;
		$info['classPath'] = str_replace('*', ucfirst($name), $mapping);

		$parts = explode('\\', $info['classPath']);
		$info['className'] = array_pop($parts);
		$info['classNamespace'] = implode('\\', $parts);
		
		$info['fileName'] = ucfirst($info['serviceName']) . '.php';
		$info['filePath'] = $this->dir . '/'. $info['fileName'];
		
		// Return
		return $info;
	}


	/**
	 * @param string
	 * @return void
	 */
	public function generateRepository($table)
	{
		// Resolve service name & class
		$info = $this->prepareServiceInfo($table, 'repository');

		// Generate virtual class
		$namespaceGen = new PhpGenerator\PhpNamespace($info['classNamespace']);
		$classGen = new PhpGenerator\ClassType($info['className'], $namespaceGen);
		$classGen->setExtends('\\Shake\\Scaffolding\\Repository');
		$classGen->addDocument($info['classPath'])->addDocument("")
			->addDocument("Auto-generated virtual repository for table `$table`.")
			->addDocument('@author  Shake framework <https://bitbucket.org/nanuqcz/shake-shake>');
			
		$this->saveClass($info['filePath'], $classGen, $namespaceGen);

		// Load class for ContainerBuilder
		include $info['filePath'];

		// Save class for future autoloader
		$this->classes[$info['classPath']] = $info['filePath'];
	}


	/**
	 * @param string
	 * @return void
	 */
	public function generateService($table)
	{
		// Resolve service name & class
		$info = $this->prepareServiceInfo($table, 'service');

		// Generate virtual class
		$namespaceGen = new PhpGenerator\PhpNamespace($info['classNamespace']);
		$classGen = new PhpGenerator\ClassType($info['className'], $namespaceGen);
		$classGen->setExtends('\\Shake\\Scaffolding\\Service');
		$classGen->addDocument($info['classPath'])->addDocument("")
			->addDocument("Auto-generated virtual service for table `$table`.")
			->addDocument('@author  Shake framework <https://bitbucket.org/nanuqcz/shake-shake>');
			
		$this->saveClass($info['filePath'], $classGen, $namespaceGen);

		// Load class for ContainerBuilder
		include $info['filePath'];

		// Save class for future autoloader
		$this->classes[$info['classPath']] = $info['filePath'];
	}


	/**
	 * @param string
	 * @param PhpGenerator\ClassType
	 * @param PhpGenerator\PhpNamespace
	 * @return void
	 */
	private function saveClass($filePath, $classGen, $namespaceGen)
	{
		// Save to file
		file_put_contents(
			$filePath, 
			"<?php \n" . (string)$namespaceGen . (string)$classGen
		);
	}


	/**
	 * @param string
	 * @return void
	 */
	public function generateAutoloader($filepath)
	{
		$script = '<?php ' . "\n"
			. 'function shake_scaffolding_autoload($class) {' . "\n"
			. '	$classes = array(' . "\n";
		
		foreach ($this->classes as $class => $path) {
			$script .= "		'" . str_replace('\\', '\\\\', $class) . "' => '$path',\n";
		}
		
		$script .= '	);' . "\n\n"
			. '	if (isset($classes[$class])) {' . "\n"
			. '		require($classes[$class]);' . "\n"
			. '	}' . "\n"
			. '}' . "\n\n"
			. 'spl_autoload_register(\'shake_scaffolding_autoload\');';

		// Save to file
		file_put_contents($filepath, $script);
	}

}