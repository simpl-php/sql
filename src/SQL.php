<?php

namespace Simpl;

use Exception;
use PDO;

class SQL
{
	/**
	 * @var PDO
	 */
	public $pdo;
	public $dsn;
	public $username;
	public $password;
	public $throw_exception;
	public $exception;

	/**
	 * SQL constructor.
	 * @param mixed $config Host or DSN Array.
	 * @param null $dbname
	 * @param null $username
	 * @param null $password
	 * @param array $options
	 * @param bool $throw Should we throw exceptions? Useful for unit testing to disable throwing exceptions.
	 * @throws Exception
	 */
	public function __construct($config, $dbname = null, $username = null, $password = null, $options = [], $throw = true)
	{
		$this->throw_exception = $throw;

		$this->dsn = self::buildDsn($config, $dbname);

		// Get options from the config array, if available.
		if (empty($options) && isset($config['options'])){
			$options = $config['options'];
		}

		if (empty($options)){
			$options = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
			];
		}

		if (empty($username) && isset($config['username'])){
			$username = $config['username'];
		}

		if (empty($password) && isset($config['password'])){
			$password = $config['password'];
		}

		# Save username/password as class properties for testability.
		$this->username = $username;
		$this->password = $password;

		try {
			$this->pdo = new PDO($this->dsn, $username, $password, $options);
		} catch (\PDOException $e) {
			return $this->handleException($e);
		}
	}

	/**
	 * Used for SELECT commands.
	 * @param $sql
	 * @param array $params
	 * @return bool|false|\PDOStatement
	 */
	public function query($sql, $params = array())
	{
		if (empty($params)) {
			return $this->pdo->query($sql);
		} else {
			if (is_scalar($params)) {
				$params = array($params);
			}

			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);
			return $stmt;
		}
	}

	/**
	 * @param $sql
	 * @param $params
	 * @param int $mode
	 * @return array
	 */
	public function fetchAll($sql, $params = array(), $mode = PDO::FETCH_ASSOC)
	{
		if (empty($params)) {
			return $this->pdo->query($sql)->fetchAll($mode);
		} else {
			if (is_scalar($params)) {
				$params = array($params);
			}

			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);
			return $stmt->fetchAll($mode);
		}
	}

	/**
	 * @param $sql
	 * @param $params
	 * @return array
	 */
	public function fetchColumn($sql, $params = array())
	{
		return $this->fetchAll($sql, $params, PDO::FETCH_COLUMN);
	}

	/**
	 * Used for DELETE and UPDATE commands. Returns number of affected rows.
	 * @param $sql
	 * @param array $params
	 * @return int
	 */
	public function exec($sql, $params = array())
	{
		if (empty($params)) {
			return $this->pdo->exec($sql);
		} else {
			if (is_scalar($params)) {
				$params = array($params);
			}

			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);
			return $stmt->rowCount();
		}
	}

	/**
	 * Used for INSERT commands. Returns the last insert id.
	 * @param $sql
	 * @param array $params
	 * @return string
	 * @throws Exception
	 */
	public function insert($sql, $params = array())
	{
		try{
			$this->exec($sql, $params);
			return $this->pdo->lastInsertId();
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * Build an "update" sql query from an array.
	 * @param string $table Name of table
	 * @param array $parts Key=>Value pairs
	 * @param string $condition Update condition, ie: "where name='Josh'"
	 * @return string sql string.
	 *
	 * This function escapes parameters with PDO::quote(), so they don't need to be
	 * quoted or escaped prior to sending thru this function.
	 */
	public function makeUpdateOld($table, $parts, $condition)
	{
		$sql = "update $table set ";

		foreach ($parts as $field => $val) {
			$sql.="$field = " . $this->pdo->quote($val) . ", ";
		}

		$sql = preg_replace("/, $/", "", $sql);

		if ($condition) {
			$sql.=' where ' . $condition;
		}
		return $sql;
	}

	public function makeUpdate($table, $parts, $condition)
	{
		$updates = [];

		foreach($parts as $field => $value){
			$updates[] = $field . ' = ' . $this->prepareValuesArray($value);
		}

		$sql = sprintf('update %s set %s', $table, join(', ', $updates));

		if ($condition){
			$sql .= ' where ' . $condition;
		}
		return $sql;
	}

	/**
	 * Build an "insert into" sql query from an array.
	 * @param string $table Name of table
	 * @param array $parts Key=>Value pairs
	 * @return string sql string.
	 *
	 * This function escapes parameters with PDO::quote(), so they don't need to be
	 * quoted or escaped prior to sending thru this function.
	 */
	public function makeInsertOld($table, $parts)
	{
		$sql = "insert into $table (";
		$sql2 = '' ;

		foreach ($parts as $field => $val) {
			$sql.=$field . ', ';

			if ($val === null) {
				$sql2.= 'NULL' . ", ";
			} else {
				$sql2.= $this->pdo->quote($val) . ", ";
			}
		}
		$sql = preg_replace("/, $/", "", $sql);
		$sql2 = preg_replace("/, $/", "", $sql2);
		return $sql . ') values (' . $sql2 . ')';
	}

	public function makeInsert($table, $parts = [])
	{
		$keys_string = join(', ', array_keys($parts));
		$values = array_values($parts);
		$quoted_values = array_map([$this, 'prepareValuesArray'], $values);
		$values_string = join(', ', $quoted_values);

		return sprintf('insert into %s (%s) values (%s)', $table, $keys_string, $values_string);
	}
	/**
	 * Build a "replace into" sql query from an array.
	 * @param string $table Name of table
	 * @param array $parts Key=>Value pairs
	 * @return string sql string.
	 *
	 * This function escapes parameters with PDO::quote(), so they don't need to be
	 * quoted or escaped prior to sending thru this function.
	 */
	public function makeReplace($table, $parts)
	{
		$sql = "replace into $table (";
		$sql2 = '';

		foreach ($parts as $field => $val) {
			$sql.=$field . ', ';

			if ($val === null) {
				$sql2.= 'NULL' . ", ";
			} else {
				$sql2.= $this->pdo->quote($val) . ", ";
			}
		}
		$sql = preg_replace("/, $/", "", $sql);
		$sql2 = preg_replace("/, $/", "", $sql2);
		return $sql . ') values (' . $sql2 . ')';
	}

	public static function buildDsn($host, $dbname = null, $port = null, $prefix = 'mysql', $charset = 'utf8mb4')
	{
		if (is_array($host))
		{
			$dsn = self::buildDsnFromArray($host);
		} elseif ($host == 'sqlite') {
			$dsn = self::buildDsnFromArray(
				[
					'prefix' => $host,
					'path' => $dbname
				]
			);
		} else {
			$dsn = self::buildDsnFromArray(
				[
					'prefix' => $prefix,
					'host' => $host,
					'port' => $port,
					'dbname' => $dbname,
					'charset' => $charset
				]
			);
		}

		return $dsn;
	}

	/**
	 * @param $options
	 * @return string
	 * @throws Exception
	 */
	public static function buildDsnFromArray($options)
	{
		$prefix = isset($options['prefix']) ? $options['prefix'] : 'mysql';

		$parts = [];

		if (preg_match('/sqlite/', $prefix)){
			if (!isset($options['path'])){
				throw new Exception("sqlite path must be set in options.");
			}
			return $prefix . ':' . $options['path'];
		}

		$keys = ['unix_socket', 'host', 'port', 'dbname', 'charset'];

		foreach($keys as $key){
			if (isset($options[$key]) && !empty($options[$key])){
				$parts[$key] = sprintf('%s=%s', $key, $options[$key]);
			}
		}

		return $prefix . ':' . join(';', $parts);
	}

	public function getDsn()
	{
		return $this->dsn;
	}

	public function getPdo()
	{
		return $this->pdo;
	}

	public function quote($string)
	{
		return $this->pdo->quote($string);
	}

	public function prepareValuesArray($string){
		if ($string === null){
			return 'NULL';
		} else {
			return $this->quote($string);
		}
	}
	/**
	 * @param Exception $exception
	 * @return SQL
	 * @throws Exception
	 */
	protected function handleException(Exception $exception)
	{
		$this->exception = $exception;

		if ($this->throw_exception){
			throw $exception;
		} else{
			return $this;
		}
	}

	/**
	 * @return bool
	 */
	public function hasException()
	{
		return $this->exception !== null;
	}
}
