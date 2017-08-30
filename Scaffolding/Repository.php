<?php
namespace Shake\Scaffolding;

use Shake\Utils\Strings,
	Shake\Database\Orm;
use Nette\Object,
	Nette\Database\Table\IRowContainer,
	Nette\Database\Table\IRow,
	Nette\Database\Table\Selection,
	Nette\MemberAccessException,
	Nette\InvalidArgumentException,
	Nette\Application\BadRequestException;


/**
 * Scaffolding\Repository
 * Base repository with conventional functions.
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class Repository extends Object
{
	/** @var Nette\Database\Context|Shake\Database\Orm\Context */
	private $connection;

	/** @var string */
	private $tableName;



	/**
	 * @param Nette\Database\Context|Shake\Database\Orm\Context
	 */
	public function __construct($connection)
	{
		$this->connection = $connection;
	}



	/**
	 * @return string
	 */
	public function getTableName()
	{
		if (!$this->tableName)
			$this->tableName = $this->detectTableName();

		return $this->tableName;
	}



	/**
	 * @param string
	 * @return void
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}



	/**
	 * @param int
	 * @return IRow
	 * @throws Nette\Application\BadRequestException
	 */
	public function get($id)
	{
		$row = $this->find($id);

		if ($row === FALSE)
			throw new BadRequestException('Entry not found', 404);

		return $row;
	}



 	/**
	 * @param int
	 * @param int|array
	 * @return IRow|FALSE
	 */
	public function find($conditions)
	{
		$selection = $this->select();

		if (is_array($conditions)) {
			$conditions = $this->fixConditions($conditions);
			$this->applyConditions($selection, $conditions);
		} else {
			$selection->where($this->prefix('id'), $conditions);
		}

		return $selection->limit(1)->fetch();
 	}



	/**
	 * @param array|NULL
	 * @param array|NULL
	 * @param array|NULL
	 * @return IRowContainer
	 */
	public function search($conditions = NULL, $limit = NULL, $order = NULL)
	{
		$selection = $this->select();

		if ($conditions) {
			$conditions = $this->fixConditions($conditions);
			$this->applyConditions($selection, $conditions);
		}

		if ($limit) {
			$selection->limit($limit[0], $limit[1]);
		}

		if ($order) {
			$selection->order($order);
		}

		return $selection;
	}



	/**
	 * @param string  key column name
	 * @param string  value column name
	 * @param array|NULL
	 * @param array|NULL
	 * @return array
	 */
	public function fetchPairs($key, $value = NULL, $conditions = NULL, $order = NULL)
	{
		$selection = $this->search($conditions, NULL, $order);

		return $selection->fetchPairs($key, $value);
	}



	/**
	 * @param array
	 * @return IRow|FALSE
	 */
	public function create($values)
	{
		return $this->connection->table($this->getTableName())
			->insert($values);
	}



	/**
	 * @param int
	 * @param array
	 * @return int
	 */
	public function update($id, $values)
	{
		return $this->connection->table($this->getTableName())
			->get($id)
			->update($values);
	}



	/**
	 * @param int
	 * @return int
	 */
	public function delete($id)
	{
		return $this->connection->table($this->getTableName())
			->get($id)
			->delete();
	}



	/**
	 * @param array
	 * @return int
	 */
	public function count($data)
	{
		if ($data instanceof IRowContainer) {
			return $data->count('*');

		} else {
			return count($data);
		}
	}



	/**
	 * @param array
	 * @param int
	 * @param int
	 * @return array
	 */
	public function applyLimit($data, $limit, $offset)
	{
		// Selection
		if ($data instanceof IRowContainer) {
			return $data->limit($limit, $offset);

		// Iterator
		} elseif ($data instanceof Iterator) {
			$data = iterator_to_array($data);
			return array_slice($data, $offset, $limit);

		// Array
		} elseif ($data instanceof ArrayAccess) {
			return array_slice($data, $offset, $limit);

		// Bad argument?
		} else {
			if (is_object($data))
				throw new InvalidArgumentException("Can't apply limit to instance of " . get_class($data) . ".");
			else
				throw new InvalidArgumentException("Can't apply limit to " . gettype($data) . " type variable.");
		}
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * Alias for getConnection()
	 * @return Connection
	 */
	public function getConn()
	{
		return $this->getConnection();
	}



	/**
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		// findBy<column>
		if (Strings::startsWith($name, 'getBy')) {
			$column = substr($name, 5);
			return $this->getBy($column, $args[0]);

		// findBy<column>
		} elseif (Strings::startsWith($name, 'findBy')) {
			$column = substr($name, 6);
			return $this->findBy($column, $args[0]);

		// searchBy<column>
		} elseif (Strings::startsWith($name, 'searchBy')) {
			$column = substr($name, 8);
			return $this->searchBy($column, $args[0], @$args[1], @$args[2]);  // @ - last 2 args are not required

		// updateBy<column>
		} elseif (Strings::startsWith($name, 'updateBy')) {
			$column = substr($name, 8);
			return $this->updateBy($column, $args[0], $args[1]);

		// deleteBy<column>
		} elseif (Strings::startsWith($name, 'deleteBy')) {
			$column = substr($name, 8);
			return $this->deleteBy($column, $args[0]);
		}

		throw new MemberAccessException("Call to undefined method " . get_class($this) . "::$name().");
	}



	/**
	 * @param string
	 * @param mixed
	 * @return IRow|FALSE
	 * @throws Nette\Application\BadRequestException
	 */
	public function getBy($name, $value)
	{
		$row = $this->findBy($name, $value);

		if ($row === FALSE)
			throw new BadRequestException('Entry not found', 404);

		return $row;
	}



	/**
	 * @param string
	 * @param mixed
	 * @return IRow|FALSE
	 */
	public function findBy($name, $value)
	{
		$name = $this->toUnderscoreCase($name);
		$name = $this->prefix($name);

		return $this->select()
			->where($name, $value)
			->limit(1)
			->fetch();
	}



	/**
	 * @param string
	 * @param mixed
	 * @param array|NULL
	 * @return IRowContainer
	 */
	public function searchBy($name, $value, $limit = NULL, $order = NULL)
	{
		$name = $this->toUnderscoreCase($name);
		$name = $this->prefix($name);

		$selection = $this->select()
			->where($name, $value);

		if ($limit)
			$selection->limit($limit[0], $limit[1]);

		if ($order)
			$selection->order($order);

		return $selection;
	}



	/**
	 * @param string
	 * @param mixed
	 * @param array
	 * @return int
	 * @throws Nette\Application\BadRequestException
	 */
	public function updateBy($name, $value, $values)
	{
		return $this->getBy($name, $value)->update($values);
	}



	/**
	 * @param string
	 * @param mixed
	 * @return int|FALSE
	 * @throws Nette\Application\BadRequestException
	 */
	public function deleteBy($name, $value)
	{
		return $this->getBy($name, $value)->delete();
	}



	/**
	 * @return IRowContainer
	 */
	protected function select()
	{
		$tableName = $this->getTableName();

		return $this->connection->table($tableName)->select("$tableName.*");
	}



	/**
	 * @param Selection|Orm\Table
	 * @param array
	 * @return Selection
	 */
	protected function applyConditions($selection, array $conditions)
	{
		$orConds = [];
		foreach ($conditions as $condition => $value) {
			$condition = trim($condition);

			// Cache OR conditions for later
			if (Strings::endsWith($condition, ' OR')) {
				$condition = preg_replace('/ OR$/', '', $condition);
				$orConds[$condition] = $value;
				continue;
			}

			// Process OR conditions
			if ($orConds) {
				$orConds[$condition] = $value;
				$selection->whereOr($orConds);
				$orConds = [];

			// Process AND condition
			} else {
				$selection->where($condition, $value);
			}
		}

		return $selection;
	}



	/**
	 * @return string
	 */
	private function detectTableName()
	{
		$tableName = get_class($this);                                          // App\Model\FooBarRepository
		$tableName = substr($tableName, 0, strrpos($tableName, 'Repository'));  // App\Model\FooBar
		$tableName = substr($tableName, strrpos($tableName, '\\') + 1);         // FooBar
		$tableName = $this->toUnderscoreCase($tableName);                       // foo_bar

		return $tableName;
	}



	/**
	 * @param string
	 * @return string
	 */
	private function toUnderscoreCase($name)
	{
		$name = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $name));
		$name = preg_replace('/([a-z])(2_)/', '$1_$2', $name);  // support for joining tables

		return $name;
	}



	/**
	 * Prepend column name with table name
	 * @param string
	 * @return string
	 */
	private function prefix($columnName)
	{
		if (strpos($columnName, '.') || strpos($columnName, ':') || !ctype_lower($columnName{0}))
			return $columnName;

		return $this->getTableName() . ".$columnName";
	}



	/**
	 * Fix condition's column names for SELECT with JOINs
	 * @param array
	 * @return array
	 */
	private function fixConditions($conditions)
	{
		$fixedConditions = array();
		foreach ($conditions as $key => $value) {
			$prefixedKey = $this->prefix($key);
			$fixedConditions[$prefixedKey] = $conditions[$key];
		}

		return $fixedConditions;
	}

}
