<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Simpl\SQL;

abstract class SQLTest extends TestCase
{
	/**
	 * @var SQL
	 */
	protected $db;

	/**
	 * Get a new connection
	 * @return SQL
	 */
	protected function connect()
	{
		$db = new \Simpl\SQL('sqlite', ':memory:');
		$this->createTableFoo($db);
		return $db;
	}

	/**
	 * Return an existing connection or create new one. Useful when you want to reuse the same connection in a test.
	 * @return SQL
	 */
	protected function getConnection()
	{
		if (empty($this->db)) {
			$this->db = $this->connect();
		}

		return $this->db;
	}

	/**
	 * Create the table most of our tests rely on.
	 * @param $db SQL
	 */
	public function createTableFoo($db)
	{
		$db->pdo->exec('create
					 	table foo(
							id INTEGER PRIMARY KEY AUTOINCREMENT,
							mytext varchar,
							myint int,
							myfloat float,
							mydate datetime)');
	}
}
